<?php

namespace SCI\Metadata;

use Onoi\Remi\CrossRef\CrossRefFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\UidValueFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CrossRefResponseParser implements ResponseParser {

	/**
	 * @var CrossRefFilteredHttpResponseParser
	 */
	private $crossRefFilteredHttpResponseParser;

	/**
	 * @since 1.0
	 *
	 * @param CrossRefFilteredHttpResponseParser $crossRefFilteredHttpResponseParser
	 */
	public function __construct( CrossRefFilteredHttpResponseParser $crossRefFilteredHttpResponseParser ) {
		$this->crossRefFilteredHttpResponseParser = $crossRefFilteredHttpResponseParser;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usedCache() {
		return $this->crossRefFilteredHttpResponseParser->usedCache();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getMessages() {
		return $this->crossRefFilteredHttpResponseParser->getMessages();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRecord() {
		return $this->crossRefFilteredHttpResponseParser->getRecord();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $doi ) {
		return $this->crossRefFilteredHttpResponseParser->getRawResponse( $doi );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doParseFor( $doi ) {

		$uidValueFactory = new UidValueFactory();

		$doiValue = $uidValueFactory->newUidValueForType( 'doi' );
		$doiValue->setUserValue( $doi );

		if ( !$doiValue->isValid() ) {
			return $this->crossRefFilteredHttpResponseParser->addMessage( $doiValue->getErrors() );
		}

		$doi = $doiValue->getWikiValue();

		$this->crossRefFilteredHttpResponseParser->doParseFor( $doi );

		$this->crossRefFilteredHttpResponseParser->getRecord()->setTitleForPageCreation( 'DOI:' . md5( $doi ) );

		$this->crossRefFilteredHttpResponseParser->getRecord()->addSearchMatchSet(
			'doi',
			$doi
		);

		$this->crossRefFilteredHttpResponseParser->getRecord()->addSearchMatchSet(
			'reference',
			$this->crossRefFilteredHttpResponseParser->getRecord()->get( 'reference' )
		);
	}

}
