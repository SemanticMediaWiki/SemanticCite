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
		$filteredRecord = $this->olFilteredHttpResponseParser->getFilteredRecord();

		// Fetch the OLID has one could search for an ISBN as well
		if ( $filteredRecord->has( 'olid' ) ) {
			$olID = $filteredRecord->get( 'olid' );
		}

		if ( is_array( $olID ) ) {
			$olID = end( $olID );
		}

		$filteredRecord->setTitleForPageCreation(
			'OL:' . str_replace( 'OL', '', $olID )
		);

		$filteredRecord->setSciteTransclusionHead(
			$olID
		);

		$filteredRecord->addSearchMatchSet( 'olid', $olID );

		if ( $filteredRecord->has( 'lccn' ) ) {
			$filteredRecord->addSearchMatchSet(
				'lccn',
				$filteredRecord->get( 'lccn' )
			);
		}

		$dateTimeUtc = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
		$filteredRecord->set( 'retrieved-on', $dateTimeUtc->format( 'Y-m-d' ) );
	}

}
