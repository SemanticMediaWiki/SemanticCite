<?php

namespace SCI\Metadata\Provider\OpenLibrary;

use SCI\Metadata\HttpRequestContentFetcher;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OLRequestContentFetcher extends HttpRequestContentFetcher {

	/**
	 * @see https://openlibrary.org/dev/docs/api/books
	 */
	const OL_REST = "https://openlibrary.org/api/";

	public function fetchDataJsonFor( $id ) {

		$this->httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );

		$this->httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
		$this->httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$this->httpRequest->setOption( CURLOPT_SSL_VERIFYPEER, false );

		$this->httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Content-type: application/json; charset=utf-8'
		) );

		$this->httpRequest->setOption(
			CURLOPT_URL,
			self::OL_REST . "books?bibkeys=" . 'OLID:' . $id . ',ISBN:' . $id . '&format=json&jscmd=data' );

		return $this->httpRequest->execute();
	}

}
