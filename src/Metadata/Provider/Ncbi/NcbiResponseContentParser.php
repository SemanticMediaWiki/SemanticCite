<?php

namespace SCI\Metadata\Provider\Ncbi;

use SCI\Metadata\ResponseContentParser;
use SCI\Metadata\NullResponseContentParser;
use SCI\Metadata\FilteredMetadataRecord;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class NcbiResponseContentParser extends ResponseContentParser {

	/**
	 * @var NcbiRequestContentFetcher
	 */
	private $ncbiRequestContentFetcher;

	/**
	 * @since 1.0
	 *
	 * @param NcbiRequestContentFetcher $ncbiRequestContentFetcher
	 * @param FilteredMetadataRecord $filteredMetadataRecord
	 */
	public function __construct( NcbiRequestContentFetcher $ncbiRequestContentFetcher, FilteredMetadataRecord $filteredMetadataRecord ) {
		$this->ncbiRequestContentFetcher = $ncbiRequestContentFetcher;
		$this->filteredMetadataRecord = $filteredMetadataRecord;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usedCache() {
		return $this->ncbiRequestContentFetcher->isCached();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $id ) {
		return $this->newResponseContentParser()->getRawResponse( $id );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doParseFor( $id ) {

		$responseContentParser = $this->newResponseContentParser();
		$responseContentParser->doParseFor( $id );

		$this->success = $responseContentParser->isSuccess();
		$this->messages = $responseContentParser->getMessages();
	}

	private function newResponseContentParser() {

		// http://www.ncbi.nlm.nih.gov/books/NBK25500/
		// http://www.ncbi.nlm.nih.gov/books/NBK25497/table/chapter2.T._entrez_unique_identifiers_ui/?report=objectonly

		switch ( $this->ncbiRequestContentFetcher->getType() ) {
			case 'pmc':
			case 'pubmed':
				$responseContentParser = new NcbiPubMedResponseContentParser(
					$this->ncbiRequestContentFetcher,
					$this->filteredMetadataRecord
				);
				break;
			default:
				$responseContentParser = new NullResponseContentParser();
		}

		return $responseContentParser;
	}

}
