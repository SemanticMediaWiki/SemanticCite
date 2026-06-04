<?php

namespace SCI\FilteredMetadata\OpenLibrary;

use SCI\FilteredMetadata\FilteredHttpResponseParser;
use SCI\FilteredMetadata\HttpRequest;

/**
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class OLFilteredHttpResponseParser extends FilteredHttpResponseParser {

	/**
	 * @see https://openlibrary.org/dev/docs/api/books
	 */
	const OL_REST = "https://openlibrary.org/api/";

	/**
	 * @since 0.1
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponseById( $olID ) {
		return $this->requestResponseFor( $olID );
	}

	/**
	 * @since 0.1
	 *
	 * {@inheritDoc}
	 */
	public function doFilterResponseById( $olID ) {
		$text = $this->requestResponseFor( $olID );

		if ( $this->httpRequest->getLastError() !== '' ) {
			return $this->addMessage( [ 'onoi-remi-request-error', $this->httpRequest->getLastError(), $olID ] );
		}

		$json = json_decode(
			$text,
			true
		);

		if ( $json === null || $json === '' || $json === [] ) {
			return $this->addMessage( [ 'onoi-remi-response-empty', $olID ] );
		}

		$this->doProcessJson( $json );

		$this->filteredRecord->set( 'retrieved-from', 'https://openlibrary.org/' );
	}

	private function doProcessJson( $json ) {
		$olBooksAPIJsonProcessor = new OLBooksAPIJsonProcessor(
			$this->filteredRecord
		);

		$olBooksAPIJsonProcessor->doProcess( $json );
	}

	/**
	 * @see https://openlibrary.org/dev/docs/api/books#data
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	private function requestResponseFor( $id ) {
		$this->httpRequest->setOption( HttpRequest::FOLLOW_LOCATION, true );

		$this->httpRequest->setOption( HttpRequest::HEADERS, [
			'Accept: application/json',
			'Content-type: application/json; charset=utf-8'
		] );

		$this->httpRequest->setOption(
			HttpRequest::URL,
			self::OL_REST . "books?bibkeys=" . 'OLID:' . $id . ',ISBN:' . $id . '&format=json&jscmd=data' );

		return $this->httpRequest->execute();
	}

}
