<?php

namespace SCI\FilteredMetadata\Oclc;

use SCI\FilteredMetadata\FilteredHttpResponseParser;
use SCI\FilteredMetadata\HttpRequest;

/**
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class OclcFilteredHttpResponseParser extends FilteredHttpResponseParser {

	/**
	 * @see http://dataliberate.com/2013/06/content-negotiation-for-worldcat/
	 */
	const OCLC_REST = "http://www.worldcat.org/oclc/";

	/**
	 * @since 0.1
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponseById( $oclcID ) {
		return $this->requestResponseFor( $oclcID );
	}

	/**
	 * @since 0.1
	 *
	 * {@inheritDoc}
	 */
	public function doFilterResponseById( $oclcID ) {
		$text = $this->requestResponseFor( $oclcID );

		if ( $this->httpRequest->getLastError() !== '' ) {
			return $this->addMessage( [ 'onoi-remi-request-error', $this->httpRequest->getLastError(), $oclcID ] );
		}

		$jsonld = json_decode( $text, true );

		if ( $jsonld === null || $jsonld === '' ) {
			return $this->addMessage( [ 'onoi-remi-response-empty', $oclcID ] );
		}

		$this->doProcessJsonLd( $oclcID, $jsonld );

		$this->filteredRecord->set( 'retrieved-from', 'http://www.worldcat.org/' );
	}

	private function doProcessJsonLd( $oclcID, $jsonld ) {
		$simpleOclcJsonLdGraphProcessor = new SimpleOclcJsonLdGraphProcessor(
			$this->filteredRecord
		);

		$simpleOclcJsonLdGraphProcessor->doProcess( $oclcID, $jsonld );
	}

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	public function requestResponseFor( $id ) {
		$this->httpRequest->setOption( HttpRequest::FOLLOW_LOCATION, true );

		$this->httpRequest->setOption( HttpRequest::URL, self::OCLC_REST . $id );

		$this->httpRequest->setOption( HttpRequest::HEADERS, [
			'Accept: application/ld+json',
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		] );

		return $this->httpRequest->execute();
	}

}
