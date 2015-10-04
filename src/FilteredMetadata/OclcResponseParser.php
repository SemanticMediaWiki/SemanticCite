<?php

namespace SCI\FilteredMetadata;

use Onoi\Remi\Oclc\OclcFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\ResourceIdentifierFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OclcResponseParser implements ResponseParser {

	/**
	 * @var OclcFilteredHttpResponseParser
	 */
	private $oclcFilteredHttpResponseParser;

	/**
	 * @since 1.0
	 *
	 * @param OclcFilteredHttpResponseParser $oclcFilteredHttpResponseParser
	 */
	public function __construct( OclcFilteredHttpResponseParser $oclcFilteredHttpResponseParser ) {
		$this->oclcFilteredHttpResponseParser = $oclcFilteredHttpResponseParser;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usesCache() {
		return $this->oclcFilteredHttpResponseParser->usesCache();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getMessages() {
		return $this->oclcFilteredHttpResponseParser->getMessages();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getFilteredRecord() {
		return $this->oclcFilteredHttpResponseParser->getFilteredRecord();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $oclcID ) {
		return $this->oclcFilteredHttpResponseParser->getRawResponse( $oclcID );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doFilterResponseFor( $oclcID ) {

		$resourceIdentifierFactory = new ResourceIdentifierFactory();

		$oclcValue = $resourceIdentifierFactory->newResourceIdentifierStringValueForType( 'oclc' );
		$oclcValue->setUserValue( $oclcID );

		if ( !$oclcValue->isValid() ) {
			return $this->oclcFilteredHttpResponseParser->addMessage( $oclcValue->getErrors() );
		}

		$oclcID = $oclcValue->getWikiValue();

		$this->oclcFilteredHttpResponseParser->doFilterResponseFor( $oclcID );

		$this->oclcFilteredHttpResponseParser->getFilteredRecord()->setTitleForPageCreation( 'OCLC:' . $oclcID );
		$this->oclcFilteredHttpResponseParser->getFilteredRecord()->setSciteTransclusionHead(
			'OCLC' . $oclcID
		);

		$this->oclcFilteredHttpResponseParser->getFilteredRecord()->addSearchMatchSet( 'oclc', $oclcID );
		$this->oclcFilteredHttpResponseParser->getFilteredRecord()->set( 'oclc', $oclcID );
	}

}
