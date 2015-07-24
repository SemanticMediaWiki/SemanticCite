<?php

namespace SCI\Metadata\Provider\Viaf;

use SCI\Metadata\HttpRequestContentFetcher;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ViafRequestContentFetcher extends HttpRequestContentFetcher {

	/**
	 * @see ...
	 */
	const VIAF_REST = "http://viaf.org/viaf/";

	public function fetchXmlFor( $id ) {

		$this->httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );

		$this->httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
		$this->httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$this->httpRequest->setOption( CURLOPT_URL, self::VIAF_REST . $id );

		$this->httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/xml',
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		) );

		return $this->httpRequest->execute();
	}

}
