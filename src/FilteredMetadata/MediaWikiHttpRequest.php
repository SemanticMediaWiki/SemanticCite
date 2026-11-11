<?php

namespace SCI\FilteredMetadata;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use MediaWiki\Http\HttpRequestFactory;
use MWHttpRequest;
use Wikimedia\AtEase\AtEase;
use Wikimedia\IPUtils;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * HTTP client backed by MediaWiki's `HttpRequestFactory` with an optional
 * response cache layer.
 *
 * This replaces the former `onoi/http-request` `CachedCurlRequest`: it issues
 * requests to the bibliographic metadata providers and caches successful
 * responses, using only MediaWiki built-ins.
 *
 * @license GPL-2.0-or-later
 * @since 7.0
 */
class MediaWikiHttpRequest implements HttpRequest {

	/**
	 * Fixed cache key segment
	 */
	private const CACHE_PREFIX = 'sci:http:';

	private const MAX_REDIRECTS = 5;

	/**
	 * @var HttpRequestFactory
	 */
	private $httpRequestFactory;

	/**
	 * @var BagOStuff
	 */
	private $cache;

	/**
	 * Per-request options, cleared after each {@see execute}.
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Response cache lifetime in seconds; persists across requests. Defaults to
	 * 60s (matching the former CachedCurlRequest); callers normally override it.
	 *
	 * @var int
	 */
	private $cacheTtl = 60;

	/**
	 * Response cache key prefix; persists across requests.
	 *
	 * @var string
	 */
	private $cachePrefix = '';

	/**
	 * @var string
	 */
	private $lastError = '';

	/**
	 * @var bool
	 */
	private $isFromCache = false;

	/**
	 * @var callable(string):string[]
	 */
	private $resolveHost;

	/**
	 * @since 7.0
	 *
	 * @param HttpRequestFactory $httpRequestFactory
	 * @param BagOStuff $cache
	 * @param callable(string):string[] $resolveHost Resolves a hostname to its IP addresses
	 */
	public function __construct( HttpRequestFactory $httpRequestFactory, BagOStuff $cache, callable $resolveHost ) {
		$this->httpRequestFactory = $httpRequestFactory;
		$this->cache = $cache;
		$this->resolveHost = $resolveHost;
	}

	/**
	 * @since 7.0
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setOption( $name, $value ) {
		if ( $name === self::RESPONSE_CACHE_TTL ) {
			$this->cacheTtl = (int)$value;
			return;
		}

		if ( $name === self::RESPONSE_CACHE_PREFIX ) {
			$this->cachePrefix = (string)$value;
			return;
		}

		$this->options[$name] = $value;
	}

	/**
	 * @since 7.0
	 *
	 * @return string
	 */
	public function getLastError() {
		return $this->lastError;
	}

	/**
	 * @since 7.0
	 *
	 * @return bool
	 */
	public function isCached() {
		return $this->isFromCache;
	}

	/**
	 * @since 7.0
	 *
	 * @return string
	 */
	public function execute() {
		$this->lastError = '';
		$this->isFromCache = false;

		$key = $this->makeCacheKey();
		$cached = $this->cache->get( $key );

		if ( $cached !== false ) {
			$this->isFromCache = true;
			$this->options = [];
			return $cached;
		}

		$response = $this->doRequest();

		// Do not cache a failed response.
		if ( $this->lastError === '' ) {
			$this->cache->set( $key, $response, $this->cacheTtl );
		}

		$this->options = [];

		return $response;
	}

	/**
	 * @return string
	 */
	private function doRequest() {
		$url = (string)( $this->options[self::URL] ?? '' );
		$followRedirects = !empty( $this->options[self::FOLLOW_LOCATION] );

		try {
			$request = $this->newRequest( $url );
			$status = $request->execute();

			// Not left to core's followRedirects option, which prepends the body of
			// every redirect response to the content of the final one.
			for ( $redirects = 0; $followRedirects && $this->isFollowableRedirect( $request ); $redirects++ ) {
				if ( $redirects === self::MAX_REDIRECTS ) {
					$this->lastError = 'too-many-redirects';
					return '';
				}

				$target = UriResolver::resolve(
					new Uri( $url ),
					new Uri( $request->getResponseHeader( 'location' ) )
				);
				$url = (string)$target;

				// A provider that redirects to a private, loopback or link-local
				// address would otherwise turn these lookups into an SSRF vector.
				// The host is taken from the same resolved URI the request is
				// built from, so the guard and the request read the same host.
				if ( !$this->hostIsPublic( $target->getHost() ) ) {
					$this->lastError = 'blocked-private-redirect';
					return '';
				}

				$request = $this->newRequest( $url );
				$status = $request->execute();
			}
		} catch ( \Throwable $e ) {
			$this->lastError = $e->getMessage();
			return '';
		}

		if ( !$status->isOK() ) {
			$httpCode = $request->getStatus();
			$this->lastError = $httpCode ? 'HTTP ' . $httpCode : 'request-failed';

			// Mirror the former cURL CURLOPT_FAILONERROR behaviour: a failed
			// request yields no body (the parsers short-circuit on getLastError).
			return '';
		}

		return (string)$request->getContent();
	}

	private function newRequest( string $url ): MWHttpRequest {
		$requestOptions = [];

		if ( !empty( $this->options[self::POST] ) ) {
			$requestOptions['method'] = 'POST';
			$requestOptions['postData'] = (string)( $this->options[self::POST_FIELDS] ?? '' );
		}

		$request = $this->httpRequestFactory->create( $url, $requestOptions, __METHOD__ );

		foreach ( (array)( $this->options[self::HEADERS] ?? [] ) as $header ) {
			if ( strpos( (string)$header, ':' ) === false ) {
				continue;
			}

			[ $name, $value ] = explode( ':', $header, 2 );
			$request->setHeader( trim( $name ), trim( $value ) );
		}

		return $request;
	}

	private function isFollowableRedirect( MWHttpRequest $request ): bool {
		$status = $request->getStatus();

		return $status >= 300 && $status < 400 && $request->getResponseHeader( 'location' ) !== null;
	}

	private function hostIsPublic( string $host ): bool {
		if ( $host === '' ) {
			return false;
		}

		$host = trim( $host, '[]' );
		$addresses = IPUtils::isIPAddress( $host ) ? [ $host ] : ( $this->resolveHost )( $host );

		// Fail closed: a host that resolves to nothing cannot be reached anyway.
		if ( $addresses === [] ) {
			return false;
		}

		foreach ( $addresses as $address ) {
			// Canonicalize first so an IPv4-mapped IPv6 address such as
			// ::ffff:127.0.0.1 is judged by the IPv4 it dials, not by its
			// public-looking IPv6 form.
			$address = IPUtils::canonicalize( $address );

			if ( $address === null || !IPUtils::isPublic( $address ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @since 7.0
	 *
	 * @return string[]
	 */
	public static function resolveHostAddresses( string $host ): array {
		$addresses = AtEase::quietCall( 'gethostbynamel', $host ) ?: [];

		foreach ( AtEase::quietCall( 'dns_get_record', $host, DNS_AAAA ) ?: [] as $record ) {
			if ( isset( $record['ipv6'] ) ) {
				$addresses[] = $record['ipv6'];
			}
		}

		return $addresses;
	}

	/**
	 * Builds a stable cache key from the request-defining options.
	 *
	 * @return string
	 */
	private function makeCacheKey() {
		$parts = [
			'url' => $this->options[self::URL] ?? '',
			'headers' => $this->options[self::HEADERS] ?? [],
			'follow' => (bool)( $this->options[self::FOLLOW_LOCATION] ?? false ),
			'post' => (bool)( $this->options[self::POST] ?? false ),
			'post-fields' => $this->options[self::POST_FIELDS] ?? '',
		];

		return $this->cachePrefix . self::CACHE_PREFIX . md5( (string)json_encode( $parts ) );
	}

}
