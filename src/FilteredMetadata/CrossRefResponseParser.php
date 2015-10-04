<?php

namespace SCI\FilteredMetadata;

use Onoi\Remi\CrossRef\CrossRefFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\ResourceIdentifierFactory;

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
	public function usesCache() {
		return $this->crossRefFilteredHttpResponseParser->usesCache();
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
	public function getFilteredRecord() {
		return $this->crossRefFilteredHttpResponseParser->getFilteredRecord();
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
	public function doFilterResponseFor( $doi ) {

		$resourceIdentifierFactory = new ResourceIdentifierFactory();

		$doiValue = $resourceIdentifierFactory->newResourceIdentifierStringValueForType( 'doi' );
		$doiValue->setUserValue( $doi );

		if ( !$doiValue->isValid() ) {
			return $this->crossRefFilteredHttpResponseParser->addMessage( $doiValue->getErrors() );
		}

		$doi = $doiValue->getWikiValue();

		$this->crossRefFilteredHttpResponseParser->doFilterResponseFor( $doi );

		$this->crossRefFilteredHttpResponseParser->getFilteredRecord()->setTitleForPageCreation( 'DOI:' . md5( $doi ) );

		$this->crossRefFilteredHttpResponseParser->getFilteredRecord()->addSearchMatchSet(
			'doi',
			$doi
		);

		$this->crossRefFilteredHttpResponseParser->getFilteredRecord()->addSearchMatchSet(
			'reference',
			$this->crossRefFilteredHttpResponseParser->getFilteredRecord()->get( 'reference' )
		);
	}

}
