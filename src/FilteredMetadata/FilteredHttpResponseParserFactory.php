<?php

namespace SCI\FilteredMetadata;

use SCI\FilteredMetadata\CrossRef\CrossRefFilteredHttpResponseParser;
use SCI\FilteredMetadata\Ncbi\NcbiPubMedFilteredHttpResponseParser;
use SCI\FilteredMetadata\Oclc\OclcFilteredHttpResponseParser;
use SCI\FilteredMetadata\OpenLibrary\OLFilteredHttpResponseParser;
use SCI\FilteredMetadata\Viaf\ViafFilteredHttpResponseParser;

/**
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class FilteredHttpResponseParserFactory {

	/**
	 * @var HttpRequest
	 */
	private $httpRequest;

	/**
	 * @since 0.1
	 *
	 * @param HttpRequest $httpRequest
	 */
	public function __construct( HttpRequest $httpRequest ) {
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @since 0.1
	 *
	 * @param FilteredRecord $filteredRecord
	 *
	 * @return ResponseParser
	 */
	public function newCrossRefFilteredHttpResponseParser( FilteredRecord $filteredRecord ) {
		return new CrossRefFilteredHttpResponseParser(
			$this->httpRequest,
			$filteredRecord
		);
	}

	/**
	 * @since 0.1
	 *
	 * @param FilteredRecord $filteredRecord
	 *
	 * @return ResponseParser
	 */
	public function newViafFilteredHttpResponseParser( FilteredRecord $filteredRecord ) {
		return new ViafFilteredHttpResponseParser(
			$this->httpRequest,
			$filteredRecord
		);
	}

	/**
	 * @since 0.1
	 *
	 * @param FilteredRecord $filteredRecord
	 *
	 * @return ResponseParser
	 */
	public function newOclcFilteredHttpResponseParser( FilteredRecord $filteredRecord ) {
		return new OclcFilteredHttpResponseParser(
			$this->httpRequest,
			$filteredRecord
		);
	}

	/**
	 * @since 0.1
	 *
	 * @param FilteredRecord $filteredRecord
	 *
	 * @return ResponseParser
	 */
	public function newOLFilteredHttpResponseParser( FilteredRecord $filteredRecord ) {
		return new OLFilteredHttpResponseParser(
			$this->httpRequest,
			$filteredRecord
		);
	}

	/**
	 * @since 0.1
	 *
	 * @param FilteredRecord $filteredRecord
	 *
	 * @return ResponseParser
	 */
	public function newNcbiPubMedFilteredHttpResponseParser( FilteredRecord $filteredRecord ) {
		$filteredRecord->set( 'ncbi-dbtype', 'pubmed' );

		return new NcbiPubMedFilteredHttpResponseParser(
			$this->httpRequest,
			$filteredRecord
		);
	}

	/**
	 * @since 0.1
	 *
	 * @param FilteredRecord $filteredRecord
	 *
	 * @return ResponseParser
	 */
	public function newNcbiPubMedCentralFilteredHttpResponseParser( FilteredRecord $filteredRecord ) {
		$filteredRecord->set( 'ncbi-dbtype', 'pmc' );

		return new NcbiPubMedFilteredHttpResponseParser(
			$this->httpRequest,
			$filteredRecord
		);
	}

	/**
	 * @since 0.1
	 *
	 * @param FilteredRecord $filteredRecord
	 *
	 * @return ResponseParser
	 */
	public function newNullResponseParser( FilteredRecord $filteredRecord ) {
		return new NullResponseParser( $filteredRecord );
	}

}
