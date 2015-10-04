<?php

namespace SCI\FilteredMetadata;

use Onoi\Remi\Ncbi\NcbiPubMedFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\ResourceIdentifierFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class NcbiPubMedResponseParser implements ResponseParser {

	/**
	 * @var NcbiPubMedFilteredHttpResponseParser
	 */
	private $ncbiPubMedFilteredHttpResponseParser;

	/**
	 * @since 1.0
	 *
	 * @param NcbiPubMedFilteredHttpResponseParser $ncbiPubMedFilteredHttpResponseParser
	 */
	public function __construct( NcbiPubMedFilteredHttpResponseParser $ncbiPubMedFilteredHttpResponseParser ) {
		$this->ncbiPubMedFilteredHttpResponseParser = $ncbiPubMedFilteredHttpResponseParser;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usesCache() {
		return $this->ncbiPubMedFilteredHttpResponseParser->usesCache();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getMessages() {
		return $this->ncbiPubMedFilteredHttpResponseParser->getMessages();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getFilteredRecord() {
		return $this->ncbiPubMedFilteredHttpResponseParser->getFilteredRecord();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $id ) {
		return $this->ncbiPubMedFilteredHttpResponseParser->getRawResponse( $id );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doFilterResponseFor( $id ) {

		$type = $this->ncbiPubMedFilteredHttpResponseParser->getFilteredRecord()->get(
			'ncbi-dbtype'
		);

		$resourceIdentifierFactory = new ResourceIdentifierFactory();

		$pubmedValue = $resourceIdentifierFactory->newResourceIdentifierStringValueForType( $type );
		$pubmedValue->setUserValue( $id );

		if ( !$pubmedValue->isValid() ) {
			return $this->ncbiPubMedFilteredHttpResponseParser->addMessage( $pubmedValue->getErrors() );
		}

		// NCBI requires to work without any prefix
		$id = str_replace( 'PMC', '', $pubmedValue->getWikiValue() );

		// We expect this type of either being PMC or PMID as
		// invoked by NcbiResponseContentParser
		$prefix = $type === 'pmc' ? 'PMC' : 'PMID';

		$this->ncbiPubMedFilteredHttpResponseParser->doFilterResponseFor( $id );

		$this->ncbiPubMedFilteredHttpResponseParser->getFilteredRecord()->setTitleForPageCreation( $prefix . ':' . $id );
		$this->ncbiPubMedFilteredHttpResponseParser->getFilteredRecord()->setSciteTransclusionHead(
			$prefix . $id
		);

		$this->ncbiPubMedFilteredHttpResponseParser->getFilteredRecord()->addSearchMatchSet(
			'doi',
			$this->ncbiPubMedFilteredHttpResponseParser->getFilteredRecord()->get( 'doi' )
		);

		$this->ncbiPubMedFilteredHttpResponseParser->getFilteredRecord()->addSearchMatchSet(
			$type,
			$prefix . $id
		);

		$this->ncbiPubMedFilteredHttpResponseParser->getFilteredRecord()->delete( 'ncbi-dbtype' );
	}

}
