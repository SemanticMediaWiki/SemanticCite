<?php

namespace SCI\FilteredMetadata;

use MediaWiki\Http\HttpRequestFactory;
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
	 * @since 7.0
	 *
	 * @param HttpRequestFactory $httpRequestFactory
	 * @param BagOStuff $cache
	 */
	public function __construct( HttpRequestFactory $httpRequestFactory, BagOStuff $cache ) {
		$this->httpRequestFactory = $httpRequestFactory;
		$this->cache = $cache;
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
		$requestOptions = [];

		if ( !empty( $this->options[self::FOLLOW_LOCATION] ) ) {
			$requestOptions['followRedirects'] = true;
		}

		if ( !empty( $this->options[self::POST] ) ) {
			$requestOptions['method'] = 'POST';
			$requestOptions['postData'] = (string)( $this->options[self::POST_FIELDS] ?? '' );
		}

		try {
			$request = $this->httpRequestFactory->create( $url, $requestOptions, __METHOD__ );

			foreach ( (array)( $this->options[self::HEADERS] ?? [] ) as $header ) {
				if ( strpos( (string)$header, ':' ) === false ) {
					continue;
				}

				[ $name, $value ] = explode( ':', $header, 2 );
				$request->setHeader( trim( $name ), trim( $value ) );
			}

			$status = $request->execute();
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
