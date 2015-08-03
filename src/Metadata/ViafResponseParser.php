<?php

namespace SCI\Metadata;

use Onoi\Remi\Viaf\ViafFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\UidValueFactory;

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
	public function usedCache() {
		return $this->viafFilteredHttpResponseParser->usedCache();
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
	public function getRecord() {
		return $this->viafFilteredHttpResponseParser->getRecord();
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
	public function doParseFor( $viafID ) {

		$uidValueFactory = new UidValueFactory();

		$viafValue = $uidValueFactory->newUidValueForType( 'viaf' );
		$viafValue->setUserValue( $viafID );

		if ( !$viafValue->isValid() ) {
			return $this->viafFilteredHttpResponseParser->addMessage( $viafValue->getErrors() );
		}

		$viafID = $viafValue->getWikiValue();

		$this->viafFilteredHttpResponseParser->doParseFor( $viafID );

		$this->viafFilteredHttpResponseParser->getRecord()->setTitleForPageCreation( 'VIAF:' . $viafID );
		$this->viafFilteredHttpResponseParser->getRecord()->setSciteTransclusionHead(
			'VIAF' . $viafID
		);

		$this->viafFilteredHttpResponseParser->getRecord()->addSearchMatchSet( 'viaf', $viafID );
	}

}
