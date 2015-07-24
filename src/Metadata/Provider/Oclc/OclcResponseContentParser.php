<?php

namespace SCI\Metadata\Provider\Oclc;

use SCI\Metadata\ResponseContentParser;
use SCI\Metadata\FilteredMetadataRecord;
use SCI\DataValues\UidValueFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OclcResponseContentParser extends ResponseContentParser {

	/**
	 * @var OclcRequestContentFetcher
	 */
	private $oclcRequestContentFetcher;

	/**
	 * @since 1.0
	 *
	 * @param OclcRequestContentFetcher $oclcRequestContentFetcher
	 * @param FilteredMetadataRecord $filteredMetadataRecord
	 */
	public function __construct( OclcRequestContentFetcher $oclcRequestContentFetcher, FilteredMetadataRecord $filteredMetadataRecord ) {
		$this->oclcRequestContentFetcher = $oclcRequestContentFetcher;
		$this->filteredMetadataRecord = $filteredMetadataRecord;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usedCache() {
		return $this->oclcRequestContentFetcher->isCached();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $id ) {
		return $this->oclcRequestContentFetcher->fetchJsonLdFor( $id );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doParseFor( $oclc ) {

		$uidValueFactory = new UidValueFactory();

		$oclcValue = $uidValueFactory->newUidValueForType( 'oclc' );
		$oclcValue->setUserValue( $oclc );

		if ( !$oclcValue->isValid() ) {
			$this->success = false;
			$this->addMessage( $oclcValue->getErrors() );
			return null;
		}

		$oclc = $oclcValue->getWikiValue();

		$this->filteredMetadataRecord->addSearchMatchSet( 'oclc', $oclc );
		$this->filteredMetadataRecord->setCitationResourceTitle( 'OCLC:' . $oclc );
		$this->filteredMetadataRecord->setSciteTransclusionHead( 'OCLC' . $oclc );

		$jsonld = json_decode(
			$this->oclcRequestContentFetcher->fetchJsonLdFor( $oclc ),
			true
		);

		if ( $jsonld === null || $jsonld === '' ) {
			$this->addMessage( "No valid response for {$oclc} was fetched from the OCLC provider." );
			$this->success = false;
			return null;
		}

		$this->filteredMetadataRecord->set( 'oclc', $oclc );

		// We do a poor man's parse on metadata we want to harvest from the response
		// and do not looking for an almighty parser to complete JsonLD graph tree
		$this->doProcessJsonLd( $oclc, $jsonld );
	}

	private function doProcessJsonLd( $oclc, $jsonld ) {
		foreach ( $jsonld as $graph ) {
			foreach ( $graph as $key => $values ) {

				if ( isset( $values['isbn'] ) ) {
					$this->collectIsbn( $values['isbn'] );
				}

				if ( isset( $values['@type'] ) && $values['@type'] === 'schema:Person' &&  strpos( $values['@id'] , 'viaf' ) !== false ) {
					$this->filteredMetadataRecord->append(
						'viaf',
						substr( $values['@id'], strrpos( $values['@id'], '/' ) + 1 ) // get the ID not the URL
					);
					$this->filteredMetadataRecord->append( 'author', $values['name'] );
				}

				if ( isset( $values['@type'] ) && $values['@type'] === 'schema:Intangible' && isset( $values['name']['@value'] ) ) {
					$this->filteredMetadataRecord->append( 'subject', $values['name']['@value'] );
				}

				// Looking for the tree component that hosts the metadata
				if ( !isset( $values['oclcnum'] ) && !isset( $values['library:oclcnum'] ) ) {
					continue;
				}

				if ( isset( $values['oclcnum'] ) && $values['oclcnum'] == $oclc  ) {
					$this->doProcessAboutTree( $graph[$key] );
					$this->success = true;
				} elseif ( isset( $values['library:oclcnum'] ) && $values['library:oclcnum'] == $oclc ) {
					$this->doProcessAboutTree( $graph[$key] );
					$this->success = true;
				} else{
					$this->addMessage( "The `oclcnum` {$oclc} ID does not match the process tree." );
					$this->success = false;
				}
			}
		}
	}

	private function collectIsbn( $values ) {
		foreach ( $values as $value ) {
			$this->filteredMetadataRecord->append( 'isbn', $value );
		}
	}

	private function doProcessAboutTree( $values ) {
		foreach ( $values as $key => $value ) {

			switch ( $key ) {
				case '@id':
					$this->filteredMetadataRecord->set( 'url', $value );
					break;
				case '@type':
					$this->filteredMetadataRecord->set( 'type', is_string( $value ) ? array( $value) : $value );
					break;
				case 'datePublished':
					$this->filteredMetadataRecord->set( 'pubdate', $value );
					break;
				case 'description':
					$this->collectAbstract( $value );
					break;
				case 'bookEdition':
					$this->filteredMetadataRecord->set( 'edition', $value );
					break;
				case 'name':
					$this->filteredMetadataRecord->set( 'title', is_array( $value ) ? $value['@value'] : $value );
					break;
				case 'genre':
					$this->collectGenre( $value );
					break;
			}
		}
	}

	private function collectGenre( $values ) {

		foreach ( $values as $key => $value ) {

			if ( is_array( $value ) ) {
				$this->collectGenre( $value );
				continue;
			}

			if ( $key === '@value' ) {
				$this->filteredMetadataRecord->append( 'genre', $value );
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
				$this->filteredMetadataRecord->append( 'abstract', $value );
			}
		}
	}

}
