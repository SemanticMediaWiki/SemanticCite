<?php

namespace SCI\FilteredMetadata;

/**
 * Minimal HTTP client abstraction used by the bibliographic metadata response
 * parsers.
 *
 * This replaces the former `onoi/http-request` dependency. Options are set via
 * {@see HttpRequest::setOption} using the constants declared below;
 * implementations issue the request using MediaWiki built-ins.
 *
 * @license GPL-2.0-or-later
 * @since 7.0
 */
interface HttpRequest {

	/**
	 * Target request URL (string).
	 */
	public const URL = 'url';

	/**
	 * Request headers as a list of "Header-Name: value" strings (string[]).
	 */
	public const HEADERS = 'headers';

	/**
	 * Whether to follow HTTP redirects (bool).
	 */
	public const FOLLOW_LOCATION = 'follow-location';

	/**
	 * Whether to issue the request as a POST (bool).
	 */
	public const POST = 'post';

	/**
	 * Raw POST body (string).
	 */
	public const POST_FIELDS = 'post-fields';

	/**
	 * Response cache lifetime in seconds (int).
	 */
	public const RESPONSE_CACHE_TTL = 'response-cache-ttl';

	/**
	 * Response cache key prefix (string).
	 */
	public const RESPONSE_CACHE_PREFIX = 'response-cache-prefix';

	/**
	 * @since 7.0
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setOption( $name, $value );

	/**
	 * @since 7.0
	 *
	 * @return string Empty string when the last request succeeded.
	 */
	public function getLastError();

	/**
	 * @since 7.0
	 *
	 * @return bool Whether the last response was served from the cache.
	 */
	public function isCached();

	/**
	 * @since 7.0
	 *
	 * @return string The response body.
	 */
	public function execute();

}
