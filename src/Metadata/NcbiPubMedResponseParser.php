<?php

namespace SCI\Metadata;

use Onoi\Remi\Ncbi\NcbiPubMedFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\UidValueFactory;

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
	public function usedCache() {
		return $this->ncbiPubMedFilteredHttpResponseParser->usedCache();
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
	public function getRecord() {
		return $this->ncbiPubMedFilteredHttpResponseParser->getRecord();
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
	public function doParseFor( $id ) {

		$type = $this->ncbiPubMedFilteredHttpResponseParser->getRecord()->get(
			'ncbi-dbtype'
		);

		$uidValueFactory = new UidValueFactory();

		$pubmedValue = $uidValueFactory->newUidValueForType( $type );
		$pubmedValue->setUserValue( $id );

		if ( !$pubmedValue->isValid() ) {
			return $this->ncbiPubMedFilteredHttpResponseParser->addMessage( $pubmedValue->getErrors() );
		}

		// NCBI requires to work without any prefix
		$id = str_replace( 'PMC', '', $pubmedValue->getWikiValue() );

		// We expect this type of either being PMC or PMID as
		// invoked by NcbiResponseContentParser
		$prefix = $type === 'pmc' ? 'PMC' : 'PMID';

		$this->ncbiPubMedFilteredHttpResponseParser->doParseFor( $id );

		$this->ncbiPubMedFilteredHttpResponseParser->getRecord()->setTitleForPageCreation( $prefix . ':' . $id );
		$this->ncbiPubMedFilteredHttpResponseParser->getRecord()->setSciteTransclusionHead(
			$prefix . $id
		);

		$this->ncbiPubMedFilteredHttpResponseParser->getRecord()->addSearchMatchSet(
			'doi',
			$this->ncbiPubMedFilteredHttpResponseParser->getRecord()->get( 'doi' )
		);

		$this->ncbiPubMedFilteredHttpResponseParser->getRecord()->addSearchMatchSet(
			$type,
			$prefix . $id
		);

		$this->ncbiPubMedFilteredHttpResponseParser->getRecord()->delete( 'ncbi-dbtype' );
	}

}
