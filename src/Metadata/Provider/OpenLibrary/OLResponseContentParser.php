<?php

namespace SCI\Metadata\Provider\OpenLibrary;

use SCI\Metadata\ResponseContentParser;
use SCI\Metadata\FilteredMetadataRecord;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OLResponseContentParser extends ResponseContentParser {

	/**
	 * @var OLRequestContentFetcher
	 */
	private $olRequestContentFetcher;

	/**
	 * @since 1.0
	 *
	 * @param OLRequestContentFetcher $olRequestContentFetcher
	 * @param FilteredMetadataRecord $filteredMetadataRecord
	 */
	public function __construct( OLRequestContentFetcher $olRequestContentFetcher, FilteredMetadataRecord $filteredMetadataRecord ) {
		$this->olRequestContentFetcher = $olRequestContentFetcher;
		$this->filteredMetadataRecord = $filteredMetadataRecord;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usedCache() {
		return $this->olRequestContentFetcher->isCached();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $id ) {
		return $this->olRequestContentFetcher->fetchDataJsonFor( $id );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doParseFor( $id ) {

		$json = json_decode(
			$this->olRequestContentFetcher->fetchDataJsonFor( $id ),
			true
		);

		if ( $json === null || $json === array() ) {
			$this->addMessage( "No positive response was received for ID: {$id} from the OpenLibrary API." );
			$this->success = false;
			return '';
		}

		$this->doProcessJson( $json );
	}

	private function doProcessJson( $json ) {

		foreach ( $json as $key => $record ) {
			foreach ( $record as $key => $value ) {

				switch ( $key ) {
					case 'title':
						$this->filteredMetadataRecord->set( 'title', $value );
						break;
					case 'url':
						$this->filteredMetadataRecord->set( 'url', $value );
						break;
					case 'publishers':
						$this->filteredMetadataRecord->set( 'publisher', end( $value ) );
						break;
					case 'publish_date':
						$this->filteredMetadataRecord->set( 'pubdate', $value );
						break;
					case 'number_of_pages':
						$this->filteredMetadataRecord->set( 'pages', $value );
						break;
					case 'classifications':
						$this->collectClassifications( $value );
						break;
					case 'identifiers':
						$this->collectIdentifiers( $value );
						break;
					case 'subject_places':
					case 'subjects':
						$this->collectSubjects( $value );
						break;
					case 'authors':
						$this->collectAuthors( $value );
						break;
					case 'cover':
						$this->filteredMetadataRecord->set( 'cover', end( $value ) );
						break;
				}
			}
		}

		$this->filteredMetadataRecord->set( 'type', 'book' );
	}

	private function collectClassifications( $value ) {
		foreach ( $value as $id => $v ) {
			$this->filteredMetadataRecord->append( str_replace( '_', ' ', $id ), end( $v ) );
		}
	}

	private function collectSubjects( $value ) {
		foreach ( $value as $id => $v ) {
			$this->filteredMetadataRecord->append( 'subject', $v['name'] );
		}
	}

	private function collectAuthors( $value ) {
		foreach ( $value as $id => $v ) {
			$this->filteredMetadataRecord->append( 'author', $v['name'] );
		}
	}

	private function collectIdentifiers( $value ) {

		foreach ( $value as $id => $v ) {

			if ( $id === 'openlibrary' ) {
				$id = 'olid';
				$val = str_replace( 'OL', '', end( $v ) );
				$this->filteredMetadataRecord->setCitationResourceTitle( 'OL:' . $val );
				$this->filteredMetadataRecord->setSciteTransclusionHead( 'OL' . $val );
				$this->filteredMetadataRecord->addSearchMatchSet( 'olid', end( $v ) );
			}

			if ( $id === 'isbn_13' ||  $id === 'isbn_10' ) {
				$id = 'isbn';
			}

			if ( $id === 'oclc' ) {
				$this->filteredMetadataRecord->addSearchMatchSet( 'oclc', end( $v  ) );
			}

			// No commercial provider
			if ( !in_array( $id , array( 'isbn', 'olid', 'oclc', 'lccn' ) ) ) {
				continue;
			}

			$this->filteredMetadataRecord->append( $id, end( $v ) );
		}
	}

}
