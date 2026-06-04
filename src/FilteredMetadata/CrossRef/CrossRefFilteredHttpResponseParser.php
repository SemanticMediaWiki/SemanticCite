<?php

namespace SCI\FilteredMetadata\CrossRef;

use SCI\FilteredMetadata\FilteredHttpResponseParser;
use SCI\FilteredMetadata\HttpRequest;

/**
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class CrossRefFilteredHttpResponseParser extends FilteredHttpResponseParser {

	/**
	 * @see http://crosscite.org/cn/
	 */
	const CROSSREF_CONTENT_API = "https://dx.doi.org/";

	/**
	 * @since 0.1
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponseById( $doi ) {
		return $this->requestResponseFor( $doi );
	}

	/**
	 * @since 0.1
	 *
	 * {@inheritDoc}
	 */
	public function doFilterResponseById( $doi ) {
		$json = json_decode(
			$this->requestResponseFor( $doi ),
			true
		);

		if ( $this->httpRequest->getLastError() !== '' ) {
			return $this->addMessage( [ 'onoi-remi-request-error', $this->httpRequest->getLastError(), $doi ] );
		}

		if ( $json === null || $json === [] ) {
			return $this->addMessage( [ 'onoi-remi-response-empty', $doi ] );
		}

		$this->doProcessCiteproc( $json );

		$this->filteredRecord->set( 'retrieved-from', self::CROSSREF_CONTENT_API );
	}

	private function doProcessCiteproc( $json ) {
		$crossRefCiteprocJsonProcessor = new CrossRefCiteprocJsonProcessor(
			$this->getFilteredRecord()
		);

		$crossRefCiteprocJsonProcessor->doProcess( $json );
	}

	/**
	 * @param string $doi
	 *
	 * @return string
	 */
	private function requestResponseFor( $doi ) {
		$this->httpRequest->setOption( HttpRequest::FOLLOW_LOCATION, true );

		$this->httpRequest->setOption( HttpRequest::URL, self::CROSSREF_CONTENT_API . $doi );

		$this->httpRequest->setOption( HttpRequest::HEADERS, [
			'Accept: application/vnd.citationstyles.csl+json',
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		] );

		return $this->httpRequest->execute();
	}

}
