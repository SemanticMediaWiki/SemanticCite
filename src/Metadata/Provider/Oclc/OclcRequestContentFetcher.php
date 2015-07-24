<?php

namespace SCI\Metadata\Provider\Oclc;

use SCI\Metadata\HttpRequestContentFetcher;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OclcRequestContentFetcher extends HttpRequestContentFetcher {

	/**
	 * @see http://dataliberate.com/2013/06/content-negotiation-for-worldcat/
	 */
	const OCLC_REST = "http://www.worldcat.org/oclc/";

	public function fetchJsonLdFor( $id ) {

		$this->httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );

		$this->httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
		$this->httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$this->httpRequest->setOption( CURLOPT_URL, self::OCLC_REST . $id );

		$this->httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/ld+json',
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		) );

		return $this->httpRequest->execute();
	}

}
