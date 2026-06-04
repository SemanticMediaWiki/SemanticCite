<?php

namespace SCI\FilteredMetadata\Oclc;

use SCI\FilteredMetadata\FilteredRecord;

/**
 * This is a very simple graph parser for metadata we want to harvest from a
 * REST response.
 *
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class SimpleOclcJsonLdGraphProcessor {

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
	 * @param string $oclcID
	 * @param array $jsonld
	 */
	public function doProcess( $oclcID, array $jsonld ) {
		foreach ( $jsonld as $graph ) {
			$this->doProcessGraphElementsFor( $oclcID, $graph );
		}
	}

	/**
	 * @param string $oclcID
	 * @param array $graph
	 */
	private function doProcessGraphElementsFor( $oclcID, array $graph ) {
		foreach ( $graph as $element ) {
			$this->doProcessOtherElements( $element );

			// Looking for the tree component that hosts the metadata
			if ( ( isset( $element['oclcnum'] ) && $element['oclcnum'] == $oclcID ) ||
				( isset( $element['library:oclcnum'] ) && $element['library:oclcnum'] == $oclcID ) ) {
				$this->doProcessTheAboutElement( $element );
			}
		}
	}

	private function doProcessOtherElements( $element ) {
		if ( isset( $element['isbn'] ) ) {
			$this->collectIsbn( $element['isbn'] );
		}

		// Looking only for an entity of schema:Person
		if ( isset( $element['@type'] ) && $element['@type'] === 'schema:Person' && strpos( $element['@id'], 'viaf' ) !== false ) {
			$this->filteredRecord->append(
				'viaf',
				substr( $element['@id'], strrpos( $element['@id'], '/' ) + 1 ) // get the ID not the URL
			);
			$this->filteredRecord->append( 'author', $element['name'] );
		}

		// Looking only for an entity of schema:Intangible
		if ( isset( $element['@type'] ) && $element['@type'] === 'schema:Intangible' && isset( $element['name']['@value'] ) ) {
			$this->filteredRecord->append( 'subject', $element['name']['@value'] );
		}
	}

	private function doProcessTheAboutElement( array $values ) {
		foreach ( $values as $key => $value ) {

			switch ( $key ) {
				case '@id':
					$this->filteredRecord->set( 'url', $value );
					break;
				case '@type':
					$this->filteredRecord->set( 'type', is_string( $value ) ? [ $value ] : $value );
					break;
				case 'datePublished':
					$this->filteredRecord->set( 'pubdate', $value );
					break;
				case 'description':
					$this->collectAbstract( $value );
					break;
				case 'bookEdition':
					$this->filteredRecord->set( 'edition', $value );
					break;
				case 'name':
					$this->filteredRecord->set( 'title', is_array( $value ) ? $value['@value'] : $value );
					break;
				case 'genre':
					$this->collectGenre( $value );
					break;
			}
		}
	}

	private function collectIsbn( array $values ) {
		foreach ( $values as $value ) {
			$this->filteredRecord->append( 'isbn', $value );
		}
	}

	private function collectGenre( $values ) {
		foreach ( $values as $key => $value ) {

			if ( is_array( $value ) ) {
				$this->collectGenre( $value );
				continue;
			}

			if ( $key === '@value' ) {
				$this->filteredRecord->append( 'genre', $value );
			}
		}
	}

	private function collectAbstract( $values ) {
		foreach ( $values as $key => $value ) {

			if ( is_array( $value ) ) {
				$this->collectAbstract( $value );
				continue;
			}

			// This is pure guess work since the description list is
			// an unordered list with no indication of what type of
			// content is stored, use `--` is indicator that is not
			// the abstract
			if ( strpos( $value, '--' ) !== false ) {
				continue;
			}

			if ( $key === '@value' ) {
				$this->filteredRecord->append( 'abstract', $value );
			}
		}
	}

}
