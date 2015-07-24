<?php

namespace SCI\Metadata\Provider\Ncbi;

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
class NcbiPubMedResponseContentParser extends ResponseContentParser {

	/**
	 * @var NcbiRequestContentFetcher
	 */
	private $ncbiRequestContentFetcher;

	/**
	 * @since 1.0
	 *
	 * @param NcbiRequestContentFetcher $ncbiRequestContentFetcher
	 * @param FilteredMetadataRecord $filteredMetadataRecord
	 */
	public function __construct( NcbiRequestContentFetcher $ncbiRequestContentFetcher, FilteredMetadataRecord $filteredMetadataRecord ) {
		$this->ncbiRequestContentFetcher = $ncbiRequestContentFetcher;
		$this->filteredMetadataRecord = $filteredMetadataRecord;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usedCache() {
		return $this->ncbiRequestContentFetcher->isCached();
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $id ) {
		return $this->ncbiRequestContentFetcher->fetchSummaryFor( $this->convertUserValueToId( $id ) ) .
			$this->ncbiRequestContentFetcher->fetchAbstractFor($this->convertUserValueToId( $id ) );
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doParseFor( $id ) {

		$id = $this->convertUserValueToId( $id );

		if ( !$this->success ) {
			return null;
		}

		// We expect this type of either being PMC or PMID as
		// invoked by NcbiResponseContentParser
		$prefix = $this->ncbiRequestContentFetcher->getType() === 'pmc' ? 'PMC' : 'PMID';

		$this->doProcessSummary( $id );

		if ( !$this->success ) {
			return null;
		}

		$this->doProcessAbstract( $id );

		$this->filteredMetadataRecord->setCitationResourceTitle(
			$prefix . ':' . $id
		);

		$this->filteredMetadataRecord->setSciteTransclusionHead(
			$prefix . $id
		);

		$this->filteredMetadataRecord->addSearchMatchSet(
			'doi',
			$this->filteredMetadataRecord->get( 'doi' )
		);

		$this->filteredMetadataRecord->addSearchMatchSet(
			$this->ncbiRequestContentFetcher->getType(),
			$prefix . $id
		);
	}

	private function doProcessSummary( $id ) {

		$db = $this->ncbiRequestContentFetcher->getType();

		$result = json_decode(
			$this->ncbiRequestContentFetcher->fetchSummaryFor( $id ),
			true
		);

		if ( !isset( $result['result'][$id] ) ) {
			$this->success = false;
			$this->addMessage( array( 'sci-metadata-response-match-error', $id, 'PubMed API' ) );
			return null;
		}

		$record = $result['result'][$id];

		if ( isset( $record['error'] ) || $this->ncbiRequestContentFetcher->getError() !== '' ) {
			$this->success = false;
			$this->addMessage( array( 'sci-metadata-response-api-error', 'PubMed API', $id, $record['error'] ) );
			return null;
		}

		if ( isset( $record['pubtype'] ) ) {
			foreach ( $record['pubtype'] as $type ) {
				$this->filteredMetadataRecord->append( 'type', $type );
			}
		} else{
			$this->filteredMetadataRecord->set( 'type', 'article' ); // default
		}

		foreach ( $record['articleids'] as $articleids ) {

			if ( $articleids['idtype'] === 'doi' ) {
				$this->filteredMetadataRecord->set( 'doi', $articleids['value'] );
			}

			if ( $articleids['idtype'] === 'pmid' ) {
				$this->filteredMetadataRecord->set( 'pmid', $articleids['value'] );
			}

			if ( $articleids['idtype'] === 'pubmed' ) {
				$this->filteredMetadataRecord->set( 'pmid', $articleids['value'] );
			}

			if ( $articleids['idtype'] === 'pmcid' && $db === 'pmc' ) {
				$this->filteredMetadataRecord->set( 'pmcid', $articleids['value'] );
			}

			if ( $articleids['idtype'] === 'pmc' && $db === 'pubmed' ) {
				$this->filteredMetadataRecord->set( 'pmcid', $articleids['value'] );
			}
		}

		foreach ( $record['authors'] as $author ) {
			$this->filteredMetadataRecord->append( 'author', $author['name'] );
		}

		$this->filteredMetadataRecord->set( 'title', $record['title'] );
		$this->filteredMetadataRecord->set( 'journal', $record['fulljournalname'] );
		$this->filteredMetadataRecord->set( 'pubdate', $record['pubdate'] );
		$this->filteredMetadataRecord->set( 'volume', $record['volume'] );
		$this->filteredMetadataRecord->set( 'issue', $record['issue'] );
		$this->filteredMetadataRecord->set( 'pages', $record['pages'] );

		$this->success = true;
	}

	private function doProcessAbstract( $id ) {

		$dom = new DOMDocument();
		$dom->loadXml( $this->ncbiRequestContentFetcher->fetchAbstractFor( $id ) );

		foreach ( $dom->getElementsByTagName( 'PubDate' ) as $item ) {
			foreach ( $item->getElementsByTagName( 'Year' ) as $i ) {
				$this->filteredMetadataRecord->set( 'year', $i->nodeValue );
			}
		}

		foreach ( $dom->getElementsByTagName( 'Abstract' ) as $item ) {
			$this->filteredMetadataRecord->set( 'abstract', preg_replace( '#\s{2,}#', ' ', trim( $item->nodeValue ) ) );
		}

		foreach ( $dom->getElementsByTagName( 'MeshHeading' ) as $item ) {
			$this->filteredMetadataRecord->append( 'subject', preg_replace( '#\s{2,}#', ' ', trim( $item->nodeValue ) ) );
		}

		// PMC related DOM parsing
		foreach ( $dom->getElementsByTagName( 'pub-date' ) as $item ) {
			foreach ( $item->getElementsByTagName( 'year' ) as $i ) {
				$this->filteredMetadataRecord->set( 'year', $i->nodeValue );
			}
		}

		foreach ( $dom->getElementsByTagName( 'abstract' ) as $item ) {
			$this->filteredMetadataRecord->set( 'abstract', trim( $item->nodeValue ) );
		}

	//	if ( isset( $xml->PubmedArticle->MedlineCitation->Article->Journal->JournalIssue->PubDate->Year ) ) {
	//		$year = $xml->PubmedArticle->MedlineCitation->Article->Journal->JournalIssue->PubDate->Year;
	//	} elseif ( isset( $xml->article->front->{'article-meta'}->{'pub-date'}->year ) ) {
	//		$year = $xml->article->front->{'article-meta'}->{'pub-date'}->year; //PMC
	//	}

	//	$this->filteredMetadataRecord->set( 'year', $year );

	//	if ( isset( $xml->PubmedArticle->MedlineCitation->Article->Abstract->AbstractText ) ) {
	//		$abstract = end( $xml->PubmedArticle->MedlineCitation->Article->Abstract->AbstractText );
	//	} elseif ( isset( $xml->article->front->{'article-meta'}->abstract->p ) ) {
	//		$abstract =  $xml->article->front->{'article-meta'}->abstract->p; // PMC
	//	}

	///	$this->filteredMetadataRecord->set( 'abstract', $abstract );

	//	if ( isset( $xml->PubmedArticle->MedlineCitation->MeshHeadingList->MeshHeading ) ) {
	//		foreach ( $xml->PubmedArticle->MedlineCitation->MeshHeadingList->MeshHeading as $value ) {
	//			$this->filteredMetadataRecord->append( 'subject', $value->DescriptorName );
	//		}
	//	}
	}

	private function convertUserValueToId( $id ) {

		$uidValueFactory = new UidValueFactory();

		$pubmedValue = $uidValueFactory->newUidValueForType(
			$this->ncbiRequestContentFetcher->getType()
		);

		$pubmedValue->setUserValue( $id );

		if ( !$pubmedValue->isValid() ) {
			$this->success = false;
			$this->addMessage( $pubmedValue->getErrors() );
			return null;
		}

		// NCBI requires to work without any prefix
		return str_replace( 'PMC', '', $pubmedValue->getWikiValue() );
	}

}
