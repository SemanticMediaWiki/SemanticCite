<?php

namespace SCI\FilteredMetadata;

use Onoi\HttpRequest\HttpRequest;
use Onoi\Remi\FilteredHttpResponseParserFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HttpResponseParserFactory {

	/**
	 * @var HttpRequest
	 */
	private $httpRequest;

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
	 * @param string $type
	 *
	 * @return ResponseParser
	 */
	public function newResponseParserForType( $type ) {

		$bibliographicFilteredRecord = new BibliographicFilteredRecord();

		$filteredHttpResponseParserFactory = new FilteredHttpResponseParserFactory(
			$this->httpRequest
		);

		switch ( strtolower( $type ) ) {
			case 'doi':
				$responseParser = new CrossRefResponseParser(
					$filteredHttpResponseParserFactory->newCrossRefFilteredHttpResponseParser(
						$bibliographicFilteredRecord
					)
				);
				break;
			case 'viaf':
				$responseParser = new ViafResponseParser(
					$filteredHttpResponseParserFactory->newViafFilteredHttpResponseParser(
						$bibliographicFilteredRecord
					)
				);
				break;
			case 'pubmed':
				$responseParser = new NcbiPubMedResponseParser(
					$filteredHttpResponseParserFactory->newNcbiPubMedFilteredHttpResponseParser(
						$bibliographicFilteredRecord
					)
				);
				break;
			case 'pmc':
				$responseParser = new NcbiPubMedResponseParser(
					$filteredHttpResponseParserFactory->newNcbiPubMedCentralFilteredHttpResponseParser(
						$bibliographicFilteredRecord
					)
				);
				break;
			case 'oclc':
				$responseParser = new OclcResponseParser(
					$filteredHttpResponseParserFactory->newOclcFilteredHttpResponseParser(
						$bibliographicFilteredRecord
					)
				);
				break;
			case 'ol':
				$responseParser = new OLResponseParser(
					$filteredHttpResponseParserFactory->newOLFilteredHttpResponseParser(
						$bibliographicFilteredRecord
					)
				);
				break;
			default:
				$responseParser = $filteredHttpResponseParserFactory->newNullResponseParser(
					$bibliographicFilteredRecord
				);
				break;
		}

		return $responseParser;
	}

}
