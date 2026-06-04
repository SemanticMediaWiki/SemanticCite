<?php

namespace SCI\FilteredMetadata\CrossRef;

use SCI\FilteredMetadata\FilteredRecord;

/**
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class CrossRefCiteprocJsonProcessor {

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
		$this->doProcessCiteproc( $json );
	}

	private function doProcessCiteproc( $citeproc ) {
		foreach ( $citeproc as $key => $value ) {

			switch ( $key ) {
				case 'type':
					$this->filteredRecord->set( 'type', $value );
					break;
				case 'subject':
					$this->filteredRecord->set( 'subject', $value );
					break;
				case 'editor':
				case 'author':
					$this->collectAuthors( $key, $value );
					break;
				case 'title':
					$this->filteredRecord->set( 'title', $value );
					break;
				case 'issue':
					$this->filteredRecord->set( 'issue', $value );
					break;
				case 'volume':
					$this->filteredRecord->set( 'volume', $value );
					break;
				case 'page':
					$this->filteredRecord->set( 'pages', $value );
					break;
				case 'publisher':
					$this->filteredRecord->set( 'publisher', $value );
					break;
				case 'container-title':
					$this->filteredRecord->set( 'journal', $value );
					break;
				case 'DOI':
					$this->filteredRecord->set( 'doi', $value );
					break;
				case 'ISSN':
					$this->filteredRecord->set( 'issn', $value );
					break;
				case 'deposited': // Dataset
				case 'issued':
					if ( isset( $value['raw'] ) ) {
						$this->filteredRecord->set( 'year', $value['raw'] );
					} else {
						$date = end( $value['date-parts'] );
						$this->filteredRecord->set( 'year', $date[0] );
					}

					break;
			}
		}

		// Part of the auto generated key
		$this->filteredRecord->append(
			'reference',
			$this->filteredRecord->get( 'year' ) .
			mb_substr( strtolower( $this->filteredRecord->get( 'title' ) ), 0, 2 )
		);
	}

	private function collectAuthors( $name, array $values ) {
		$authors = [];

		foreach ( $values as $key => $value ) {

			// Literal set by dataset
			$familyName = isset( $value['literal'] ) ? $value['literal'] : $value['family'];

			// Part of the auto generated key
			if ( $key == 0 ) {
				$this->filteredRecord->set( 'reference', strtolower( $familyName ) );
			}

			$authors[] = ( isset( $value['given'] ) ? $value['given'] . ' ' : '' ) . $familyName;
		}

		$this->filteredRecord->set( $name, $authors );
	}

}
