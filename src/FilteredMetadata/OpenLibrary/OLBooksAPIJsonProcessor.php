<?php

namespace SCI\FilteredMetadata\OpenLibrary;

use SCI\FilteredMetadata\FilteredRecord;

/**
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class OLBooksAPIJsonProcessor {

	/**
	 * @var FilteredRecord
	 */
	private $filteredRecord;

	/**
	 * @since 0.1
	 *
	 * @param FilteredRecord $filteredRecord
	 */
	public function __construct( FilteredRecord $filteredRecord ) {
		$this->filteredRecord = $filteredRecord;
	}

	/**
	 * @since 0.1
	 *
	 * @param array $json
	 */
	public function doProcess( array $json ) {
		foreach ( $json as $key => $record ) {
			$this->doProcessElements( $record );
		}
	}

	private function doProcessElements( $record ) {
		foreach ( $record as $key => $value ) {

			switch ( $key ) {
				case 'title':
					$this->filteredRecord->set( 'title', $value );
					break;
				case 'subtitle':
					$this->filteredRecord->set( 'subtitle', $value );
					break;
				case 'url':
					$this->filteredRecord->set( 'url', $value );
					break;
				case 'publishers':
					$this->filteredRecord->set( 'publisher', end( $value ) );
					break;
				case 'publish_date':
					$this->filteredRecord->set( 'pubdate', $value );
					break;
				case 'number_of_pages':
					$this->filteredRecord->set( 'pages', $value );
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
					$this->filteredRecord->set( 'cover', end( $value ) );
					break;
			}
		}
	}

	private function collectClassifications( $value ) {
		foreach ( $value as $id => $v ) {
			$this->filteredRecord->append( str_replace( '_', ' ', $id ), end( $v ) );
		}
	}

	private function collectSubjects( $value ) {
		foreach ( $value as $id => $v ) {
			$this->filteredRecord->append( 'subject', $v['name'] );
		}
	}

	private function collectAuthors( $value ) {
		foreach ( $value as $id => $v ) {
			$this->filteredRecord->append( 'author', $v['name'] );
		}
	}

	private function collectIdentifiers( $value ) {
		foreach ( $value as $id => $v ) {

			if ( $id === 'openlibrary' ) {
				$id = 'olid';
			}

			if ( $id === 'isbn_13' || $id === 'isbn_10' ) {
				$id = 'isbn';
			}

			// No commercial provider
			if ( !in_array( $id, [ 'isbn', 'olid', 'oclc', 'lccn' ] ) ) {
				continue;
			}

			$this->filteredRecord->append( $id, end( $v ) );
		}
	}

}
