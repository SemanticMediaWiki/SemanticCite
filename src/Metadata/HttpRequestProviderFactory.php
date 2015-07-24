<?php

namespace SCI\Metadata;

use Onoi\HttpRequest\HttpRequest;
use SCI\Metadata\Provider\Ncbi\NcbiResponseContentParser;
use SCI\Metadata\Provider\Ncbi\NcbiRequestContentFetcher;
use SCI\Metadata\Provider\CrossRef\CrossRefResponseContentParser;
use SCI\Metadata\Provider\CrossRef\CrossRefRequestContentFetcher;
use SCI\Metadata\Provider\Oclc\OclcResponseContentParser;
use SCI\Metadata\Provider\Oclc\OclcRequestContentFetcher;
use SCI\Metadata\Provider\Viaf\ViafResponseContentParser;
use SCI\Metadata\Provider\Viaf\ViafRequestContentFetcher;
use SCI\Metadata\Provider\OpenLibrary\OLResponseContentParser;
use SCI\Metadata\Provider\OpenLibrary\OLRequestContentFetcher;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HttpRequestProviderFactory {

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
	 * @return HttpResponseContentParser
	 */
	public function newResponseContentParserForType( $type ) {

		$filteredMetadataRecord = new FilteredMetadataRecord();

		switch ( strtolower( $type ) ) {
			case 'pubmed':
			case 'pmc':
				$responseContentParser = new NcbiResponseContentParser(
					new NcbiRequestContentFetcher( $this->httpRequest, $type ),
					$filteredMetadataRecord
				);
				break;
			case 'doi':
				$responseContentParser = new CrossRefResponseContentParser(
					new CrossRefRequestContentFetcher( $this->httpRequest ),
					$filteredMetadataRecord
				);
				break;
			case 'oclc':
				$responseContentParser = new OclcResponseContentParser(
					new OclcRequestContentFetcher( $this->httpRequest ),
					$filteredMetadataRecord
				);
				break;
			case 'viaf':
				$responseContentParser = new ViafResponseContentParser(
					new ViafRequestContentFetcher( $this->httpRequest ),
					$filteredMetadataRecord
				);
				break;
			case 'isbn':
			case 'ol':
				$responseContentParser = new OLResponseContentParser(
					new OLRequestContentFetcher( $this->httpRequest ),
					$filteredMetadataRecord
				);
				break;
			default:
				$responseContentParser = new NullResponseContentParser();
				break;
		}

		return $responseContentParser;
	}

}
