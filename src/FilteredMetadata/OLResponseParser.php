<?php

namespace SCI\FilteredMetadata;

use Onoi\Remi\OpenLibrary\OLFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\ResourceIdentifierFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OLResponseParser implements ResponseParser {

	/**
	 * @var OLFilteredHttpResponseParser
	 */
	private $olFilteredHttpResponseParser;

	/**
	 * @since 1.0
	 *
	 * @param OLFilteredHttpResponseParser $olFilteredHttpResponseParser
	 */
	public function __construct( OLFilteredHttpResponseParser $olFilteredHttpResponseParser ) {
		$this->olFilteredHttpResponseParser = $olFilteredHttpResponseParser;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usesCache() {
		return $this->olFilteredHttpResponseParser->usesCache();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getMessages() {
		return $this->olFilteredHttpResponseParser->getMessages();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getFilteredRecord() {
		return $this->olFilteredHttpResponseParser->getFilteredRecord();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $id ) {
		return $this->olFilteredHttpResponseParser->getRawResponse( $id );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doFilterResponseFor( $olID ) {

		$this->olFilteredHttpResponseParser->doFilterResponseFor( $olID );

		// Fetch the OLID has one could search for an ISBN as well
		if ( $this->olFilteredHttpResponseParser->getFilteredRecord()->has( 'olid' ) ) {
			$olID = $this->olFilteredHttpResponseParser->getFilteredRecord()->get( 'olid' );
		}

		if ( is_array( $olID ) ) {
			$olID = end( $olID );
		}

		$this->olFilteredHttpResponseParser->getFilteredRecord()->setTitleForPageCreation(
			'OL:' . str_replace( 'OL', '', $olID )
		);

		$this->olFilteredHttpResponseParser->getFilteredRecord()->setSciteTransclusionHead(
			$olID
		);

		$this->olFilteredHttpResponseParser->getFilteredRecord()->addSearchMatchSet( 'olid', $olID );

		if ( $this->olFilteredHttpResponseParser->getFilteredRecord()->has( 'lccn' ) ) {
			$this->olFilteredHttpResponseParser->getFilteredRecord()->addSearchMatchSet(
				'lccn',
				$this->olFilteredHttpResponseParser->getFilteredRecord()->get( 'lccn' )
			);
		}
	}

}
