<?php

namespace SCI\Metadata\Provider\Viaf;

use SCI\Metadata\ResponseContentParser;
use SCI\Metadata\FilteredMetadataRecord;
use SCI\DataValues\UidValueFactory;
use DOMDocument;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ViafResponseContentParser extends ResponseContentParser {

	/**
	 * @var ViafRequestContentFetcher
	 */
	private $viafRequestContentFetcher;

	/**
	 * @since 1.0
	 *
	 * @param ViafRequestContentFetcher $viafRequestContentFetcher
	 * @param FilteredMetadataRecord $filteredMetadataRecord
	 */
	public function __construct( ViafRequestContentFetcher $viafRequestContentFetcher, FilteredMetadataRecord $filteredMetadataRecord ) {
		$this->viafRequestContentFetcher = $viafRequestContentFetcher;
		$this->filteredMetadataRecord = $filteredMetadataRecord;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usedCache() {
		return $this->viafRequestContentFetcher->isCached();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $id ) {
		return $this->viafRequestContentFetcher->fetchXmlFor( $id );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doParseFor( $viaf ) {

		$uidValueFactory = new UidValueFactory();

		$viafValue = $uidValueFactory->newUidValueForType( 'viaf' );
		$viafValue->setUserValue( $viaf );

		if ( !$viafValue->isValid() ) {
			$this->success = false;
			$this->addMessage( $viafValue->getErrors() );
			return null;
		}

		$viaf = $viafValue->getWikiValue();

		$text = $this->viafRequestContentFetcher->fetchXmlFor( $viaf );

		if ( !$text || $text === '' ) {
			$this->success = false;
			$this->addMessage( "Could not find or match {$viaf} against the VIAF API." );
			return null;
		}

		$this->doProcessDom( $viaf, $text );
	}

	private function doProcessDom( $viaf, $text ) {

		$viafID = '';

		$dom = new DOMDocument();
		$dom->loadXml( $text );

		foreach ( $dom->getElementsByTagName( 'viafID' ) as $item ) {
			$viafID = $item->nodeValue;
		}

		if ( $viafID != $viaf ) {
			$this->success = false;
			$this->addMessage( "Could not match {$viaf} as VIAF ID to the response record." );
			return null;
		}

		$this->filteredMetadataRecord->setCitationResourceTitle(
			'VIAF:' . $viaf
		);

		$this->filteredMetadataRecord->setSciteTransclusionHead(
			'VIAF' . $viaf
		);

		$this->filteredMetadataRecord->addSearchMatchSet( 'viaf', $viaf );

		$this->filteredMetadataRecord->set( 'viaf', $viaf );

		foreach ( $dom->getElementsByTagName( 'nameType' ) as $item ) {
			$this->filteredMetadataRecord->set( 'type', $item->nodeValue );
		}

		// Not sure what we want to search/iterate for therefore stop after the
		// first data/name element
		foreach ( $dom->getElementsByTagName( 'data' ) as $item ) {

			foreach ( $item->getElementsByTagName( 'text' ) as $i ) {
				$this->filteredMetadataRecord->set( 'name', str_replace( '.', '', $i->nodeValue ) );
				break;
			}

			break;
		}
	}

}
