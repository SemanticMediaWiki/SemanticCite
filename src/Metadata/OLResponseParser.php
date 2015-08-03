<?php

namespace SCI\Metadata;

use Onoi\Remi\OpenLibrary\OLFilteredHttpResponseParser;
use Onoi\Remi\ResponseParser;
use SCI\DataValues\UidValueFactory;

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
	public function usedCache() {
		return $this->olFilteredHttpResponseParser->usedCache();
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
	public function getRecord() {
		return $this->olFilteredHttpResponseParser->getRecord();
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
	public function doParseFor( $olID ) {

		$this->olFilteredHttpResponseParser->doParseFor( $olID );

		// Fetch the OLID has one could search for an ISBN as well
		if ( $this->olFilteredHttpResponseParser->getRecord()->has( 'olid' ) ) {
			$olID = $this->olFilteredHttpResponseParser->getRecord()->get( 'olid' );
		}

		if ( is_array( $olID ) ) {
			$olID = end( $olID );
		}

		$this->olFilteredHttpResponseParser->getRecord()->setTitleForPageCreation(
			'OL:' . str_replace( 'OL', '', $olID )
		);

		$this->olFilteredHttpResponseParser->getRecord()->setSciteTransclusionHead(
			$olID
		);

		$this->olFilteredHttpResponseParser->getRecord()->addSearchMatchSet( 'olid', $olID );

		if ( $this->olFilteredHttpResponseParser->getRecord()->has( 'lccn' ) ) {
			$this->olFilteredHttpResponseParser->getRecord()->addSearchMatchSet(
				'lccn',
				$this->olFilteredHttpResponseParser->getRecord()->get( 'lccn' )
			);
		}
	}

}
