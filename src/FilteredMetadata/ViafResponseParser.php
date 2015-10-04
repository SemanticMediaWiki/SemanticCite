<?php

namespace SCI\FilteredMetadata;

use Onoi\Remi\Viaf\ViafFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\ResourceIdentifierFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ViafResponseParser implements ResponseParser {

	/**
	 * @var ViafFilteredHttpResponseParser
	 */
	private $viafFilteredHttpResponseParser;

	/**
	 * @since 1.0
	 *
	 * @param ViafFilteredHttpResponseParser $viafFilteredHttpResponseParser
	 */
	public function __construct( ViafFilteredHttpResponseParser $viafFilteredHttpResponseParser ) {
		$this->viafFilteredHttpResponseParser = $viafFilteredHttpResponseParser;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usesCache() {
		return $this->viafFilteredHttpResponseParser->usesCache();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getMessages() {
		return $this->viafFilteredHttpResponseParser->getMessages();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getFilteredRecord() {
		return $this->viafFilteredHttpResponseParser->getFilteredRecord();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $viafID ) {
		return $this->viafFilteredHttpResponseParser->getRawResponse( $viafID );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doFilterResponseFor( $viafID ) {

		$resourceIdentifierFactory = new ResourceIdentifierFactory();

		$viafValue = $resourceIdentifierFactory->newResourceIdentifierStringValueForType( 'viaf' );
		$viafValue->setUserValue( $viafID );

		if ( !$viafValue->isValid() ) {
			return $this->viafFilteredHttpResponseParser->addMessage( $viafValue->getErrors() );
		}

		$viafID = $viafValue->getWikiValue();

		$this->viafFilteredHttpResponseParser->doFilterResponseFor( $viafID );

		$this->viafFilteredHttpResponseParser->getFilteredRecord()->setTitleForPageCreation( 'VIAF:' . $viafID );
		$this->viafFilteredHttpResponseParser->getFilteredRecord()->setSciteTransclusionHead(
			'VIAF' . $viafID
		);

		$this->viafFilteredHttpResponseParser->getFilteredRecord()->addSearchMatchSet( 'viaf', $viafID );
	}

}
