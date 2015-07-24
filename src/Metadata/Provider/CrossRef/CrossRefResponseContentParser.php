<?php

namespace SCI\Metadata\Provider\CrossRef;

use SCI\Metadata\ResponseContentParser;
use SCI\Metadata\FilteredMetadataRecord;
use SCI\DataValues\UidValueFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CrossRefResponseContentParser extends ResponseContentParser {

	/**
	 * @var CrossRefRequestContentFetcher
	 */
	private $crossRefRequestContentFetcher;

	/**
	 * @since 1.0
	 *
	 * @param CrossRefRequestContentFetcher $crossRefRequestContentFetcher
	 * @param FilteredMetadataRecord $filteredMetadataRecord
	 */
	public function __construct( CrossRefRequestContentFetcher $crossRefRequestContentFetcher, FilteredMetadataRecord $filteredMetadataRecord ) {
		$this->crossRefRequestContentFetcher = $crossRefRequestContentFetcher;
		$this->filteredMetadataRecord = $filteredMetadataRecord;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usedCache() {
		return $this->crossRefRequestContentFetcher->isCached();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $doi ) {
		return $this->crossRefRequestContentFetcher->fetchCiteprocJsonFor( $doi );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doParseFor( $doi ) {

		$html = '';
		$uidValueFactory = new UidValueFactory();

		$doiValue = $uidValueFactory->newUidValueForType( 'doi' );
		$doiValue->setUserValue( $doi );

		if ( !$doiValue->isValid() ) {
			$this->success = false;
			$this->addMessage( $doiValue->getErrors() );
			return $html;
		}

		$doi = $doiValue->getWikiValue();

		$json = json_decode(
			$this->crossRefRequestContentFetcher->fetchCiteprocJsonFor( $doi ),
			true
		);

		if ( $json === null || $json === array() ) {
			$this->addMessage( array( 'sci-metadata-response-empty', 'CrossRef API', $doi ) );
			$this->success = false;
			return '';
		}

		$this->doProcessCiteproc( $json );

		$this->filteredMetadataRecord->setCitationResourceTitle( 'DOI:' . md5( $doi ) );
		$this->filteredMetadataRecord->addSearchMatchSet( 'doi', $doi );
		$this->filteredMetadataRecord->addSearchMatchSet(
			'reference',
			$this->filteredMetadataRecord->get( 'reference' )
		);
	}

	private function doProcessCiteproc( $citeproc ) {

		foreach ( $citeproc as $key => $value ) {

			switch ( $key ) {
				case 'type':
					$this->filteredMetadataRecord->set( 'type', $value );
					break;
				case 'subject':
					$this->filteredMetadataRecord->set( 'subject', $value );
					break;
				case 'editor':
				case 'author':
					$this->collectAuthors( $key, $value );
					break;
				case 'title':
					$this->filteredMetadataRecord->set( 'title', $value );
					break;
				case 'issue':
					$this->filteredMetadataRecord->set( 'issue', $value );
					break;
				case 'volume':
					$this->filteredMetadataRecord->set( 'volume', $value );
					break;
				case 'page':
					$this->filteredMetadataRecord->set( 'pages', $value );
					break;
				case 'publisher':
					$this->filteredMetadataRecord->set( 'publisher', $value );
					break;
				case 'container-title':
					$this->filteredMetadataRecord->set( 'journal', $value );
					break;
				case 'DOI':
					$this->filteredMetadataRecord->set( 'doi', $value );
					break;
				case 'deposited': // Dataset
				case 'issued':
					$date = end( $value['date-parts'] );
					$this->filteredMetadataRecord->set( 'year', $date[0] );
					break;
			}
		}

		// Part of the auto generated citation key
		$this->filteredMetadataRecord->append(
			'reference',
			$this->filteredMetadataRecord->get( 'year' ) .
			mb_substr( strtolower( $this->filteredMetadataRecord->get( 'title' ) ) , 0, 2 )
		);

		$this->success = true;
	}

	private function collectAuthors( $name, array $values ) {

		$authors = array();

		foreach ( $values as $key => $value ) {

			// Part of the auto generated citation key
			if ( $key == 0 ) {
				$this->filteredMetadataRecord->set( 'reference', strtolower( $value['family'] ) );
			}

			$authors[] = $value['given'] . ' ' . $value['family'];
		}

		$this->filteredMetadataRecord->set( $name, $authors );
	}

}
