<?php

namespace SCI\Metadata;

use Onoi\Remi\Oclc\OclcFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\UidValueFactory;

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
	public function usedCache() {
		return $this->oclcFilteredHttpResponseParser->usedCache();
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
	public function getRecord() {
		return $this->oclcFilteredHttpResponseParser->getRecord();
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
	public function doParseFor( $oclcID ) {

		$uidValueFactory = new UidValueFactory();

		$viafValue = $uidValueFactory->newUidValueForType( 'oclc' );
		$viafValue->setUserValue( $oclcID );

		if ( !$viafValue->isValid() ) {
			return $this->oclcFilteredHttpResponseParser->addMessage( $viafValue->getErrors() );
		}

		$oclcID = $viafValue->getWikiValue();

		$this->oclcFilteredHttpResponseParser->doParseFor( $oclcID );

		$this->oclcFilteredHttpResponseParser->getRecord()->setTitleForPageCreation( 'OCLC:' . $oclcID );
		$this->oclcFilteredHttpResponseParser->getRecord()->setSciteTransclusionHead(
			'OCLC' . $oclcID
		);

		$this->oclcFilteredHttpResponseParser->getRecord()->addSearchMatchSet( 'oclc', $oclcID );
		$this->oclcFilteredHttpResponseParser->getRecord()->set( 'oclc', $oclcID );
	}

}
