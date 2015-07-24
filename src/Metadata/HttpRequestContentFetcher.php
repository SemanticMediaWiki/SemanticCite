<?php

namespace SCI\Metadata;

use Onoi\HttpRequest\HttpRequest;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HttpRequestContentFetcher {

	/**
	 * @var HttpRequest
	 */
	protected $httpRequest;

	/**
	 * @since 1.0
	 *
	 * @param HttpRequest $httpRequest
	 */
	public function __construct( HttpRequest $httpRequest ) {
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getError() {
		return $this->httpRequest->getLastError();
	}

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function isCached() {
		return $this->httpRequest->isCached();
	}

}
