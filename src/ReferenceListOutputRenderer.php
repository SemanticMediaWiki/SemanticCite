<?php

namespace SCI;

use Parser;
use Html;
use SMW\MediaWiki\Renderer\HtmlColumnListRenderer;
use SMW\DIWikiPage;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ReferenceListOutputRenderer {

	/**
	 * @var CitationResourceMatchFinder
	 */
	private $citationResourceMatchFinder;

	/**
	 * @var CitationReferencePositionJournal
	 */
	private $citationReferencePositionJournal;

	/**
	 * @var HtmlColumnListRenderer
	 */
	private $htmlColumnListRenderer;

	/**
	 * @var integer
	 */
	private $numberOfReferenceListColumns = 0;

	/**
	 * @var boolean
	 */
	private $browseLinkToCitationResourceState = true;

	/**
	 * @var string
	 */
	private $referenceListType = 'ol';

	/**
	 * @var string
	 */
	private $referenceListHeader = '';

	/**
	 * @var string
	 */
	private $referenceListHeaderTocId = '';

	/**
	 * @var integer
	 */
	private $citationReferenceCaptionFormat = SCI_CITEREF_NUM;

	/**
	 * @since  1.0
	 *
	 * @param CitationResourceMatchFinder $citationResourceMatchFinder
	 * @param CitationReferencePositionJournal $citationReferencePositionJournal
	 * @param HtmlColumnListRenderer $htmlColumnListRenderer
	 */
	public function __construct( CitationResourceMatchFinder $citationResourceMatchFinder, CitationReferencePositionJournal $citationReferencePositionJournal, HtmlColumnListRenderer $htmlColumnListRenderer ) {
		$this->citationResourceMatchFinder = $citationResourceMatchFinder;
		$this->citationReferencePositionJournal = $citationReferencePositionJournal;
		$this->htmlColumnListRenderer = $htmlColumnListRenderer;
	}

	/**
	 * @since 1.0
	 *
	 * @param integer $citationReferenceCaptionFormat
	 */
	public function setCitationReferenceCaptionFormat( $citationReferenceCaptionFormat ) {
		$this->citationReferenceCaptionFormat = (int)$citationReferenceCaptionFormat;
	}

	/**
	 * @since 1.0
	 *
	 * @param integer $numberOfReferenceListColumns
	 */
	public function setNumberOfReferenceListColumns( $numberOfReferenceListColumns ) {
		$this->numberOfReferenceListColumns = (int)$numberOfReferenceListColumns;
	}

	/**
	 * @since 1.0
	 *
	 * @param integer
	 */
	public function getNumberOfReferenceListColumns() {
		return $this->numberOfReferenceListColumns;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $referenceListType
	 */
	public function setReferenceListType( $referenceListType ) {
		$this->referenceListType = $referenceListType;
	}

	/**
	 * @since 1.0
	 *
	 * @param string
	 */
	public function getReferenceListType() {
		return $this->referenceListType;
	}

	/**
	 * @since 1.0
	 *
	 * @param boolean $browseLinkToCitationResourceState
	 */
	public function setBrowseLinkToCitationResourceState( $browseLinkToCitationResourceState ) {
		$this->browseLinkToCitationResourceState = (bool)$browseLinkToCitationResourceState;
	}

	/**
	 * @since 1.0
	 *
	 * @param boolean
	 */
	public function getBrowseLinkToCitationResourceState() {
		return $this->browseLinkToCitationResourceState;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $referenceListHeader
	 */
	public function setReferenceListHeader( $referenceListHeader ) {
		$this->referenceListHeader = (string)$referenceListHeader;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $referenceListHeaderTocId
	 */
	public function setReferenceListHeaderTocId( $referenceListHeaderTocId ) {
		$this->referenceListHeaderTocId = (string)$referenceListHeaderTocId;
	}

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage $subject
	 * @param array|null $referenceList
	 *
	 * @return string
	 */
	public function doRenderReferenceListFor( DIWikiPage $subject, array $referenceList = null ) {

		if ( $referenceList !== null ) {
			$journal = $this->citationReferencePositionJournal->buildJournalForUnboundReferenceList(
				$referenceList
			);
		} else {
			$journal = $this->citationReferencePositionJournal->getJournalBySubject(
				$subject
			);
		}

		if ( $journal !== null ) {
			return $this->createHtmlFromJournal( $journal );
		}

		return '';
	}

	/**
	 * The journal is expected to contain:
	 *
	 * 'total' => a number
	 * 'reference-list' => array of hashes for references used
	 * 'reference-pos'  => individual reference links (1-a, 1-b) assigned to a hash
	 */
	private function createHtmlFromJournal( array $journal ) {

		$listOfFormattedReferences = array();
		$length = 0;

		foreach ( $journal['reference-pos'] as $referenceAsHash => $linkList ) {

			$citationText = '';
			// Get the "human" readable citation key/reference from the hashmap
			// intead of trying to access the DB/Store
			$reference = $journal['reference-list'][$referenceAsHash];

			list( $subjects, $citationText ) = $this->findCitationTextFor(
				$reference
			);

			$length += mb_strlen( $citationText );

			$flatHtmlReferenceLinks = $this->createFlatHtmlListForReferenceLinks(
				$linkList,
				$referenceAsHash
			);

			$browseLinks = $this->createBrowseLinkFor(
				$subjects,
				$reference,
				$citationText
			);

			$listOfFormattedReferences[] =
				Html::rawElement(
					'span',
					array(
						'id'    => 'scite-'. $referenceAsHash,
						'class' => 'scite-referencelinks'
					),
					$flatHtmlReferenceLinks
					) . ( $flatHtmlReferenceLinks !== '' ? '&nbsp;' : '' )  .
				Html::rawElement(
					'span',
					array(
						'id'    => 'scite-'. $referenceAsHash,
						'class' => 'scite-citation'
					),
					( $browseLinks !== '' ? $browseLinks . '&nbsp;' : '' ) . Html::rawElement(
						'span',
						array( 'class' => 'scite-citation-text' ),
						$citationText
					)
				);
		}

		return $this->doFinalizeHtmlForListOfReferences( $listOfFormattedReferences, $length );
	}

	private function doFinalizeHtmlForListOfReferences( $listOfFormattedReferences, $length ) {

		$this->htmlColumnListRenderer->setColumnListClass( 'scite-referencelist' );
		$this->htmlColumnListRenderer->setListType( $this->referenceListType );
		$this->htmlColumnListRenderer->addContentsByNoIndex( $listOfFormattedReferences );

		if ( $this->numberOfReferenceListColumns == 0 ) {
			$this->htmlColumnListRenderer->setColumnClass( 'scite-referencelist-columns-responsive'. ( $length > 300 ? '' : '-single' ) );
		} else {
			$this->htmlColumnListRenderer->setNumberOfColumns( $this->numberOfReferenceListColumns );
			$this->htmlColumnListRenderer->setColumnClass( 'smw-column' );
		}

		if ( $this->referenceListHeader === '' ) {
			$this->referenceListHeader = wfMessage( 'sci-referencelist-header' )->text();
		}

		if ( $this->referenceListHeaderTocId === '' ) {
			$this->referenceListHeaderTocId = $this->referenceListHeader;
		}

		return Html::rawElement(
				'div',
				array(
					'class' => 'scite-content'
				),
				Html::element(
				'h2',
				array(
					'id' => $this->referenceListHeaderTocId
				),
				$this->referenceListHeader
			) . "\n" . $this->htmlColumnListRenderer->getHtml() . "\n"
		);
	}

	private function findCitationTextFor( $reference ) {

		list( $subjects, $text ) = $this->citationResourceMatchFinder->findCitationTextFor(
			$reference
		);

		// Using Message:parse as shortcut to ensure the text is appropriately
		// parsed and escaped which saves the trouble to deal with Parser stubobject
		return array(
			$subjects,
			wfMessage( 'sci-referencelist-text', $text )->parse()
		);
	}

	private function createFlatHtmlListForReferenceLinks( array $linkList, $referenceHash ) {

		$referenceLinks = array();
		$class = 'scite-backlinks';

		foreach ( $linkList as $value ) {

			$isOneLinkElement = count( $linkList ) == 1;

			// Split a value of 1-a, 1-b, 2-a into its parts
			list( $major, $minor ) = explode( '-', $value );

			// Show a simple link similar to what is done on en.wp
			// for a one-link-reference
			if ( $isOneLinkElement ) {
				$minor = '^';
				$class = 'scite-backlink';
			}

			// Only display the "full" number for the combination of UL/SCI_CITEREF_NUM
			if ( $this->referenceListType === 'ul' && $this->citationReferenceCaptionFormat === SCI_CITEREF_NUM ) {
				$minor = $isOneLinkElement ? $major : str_replace( '-', '.', $value );
			}

			$referenceLinks[] = Html::rawElement(
				'a',
				array(
					'href'  => "#scite-ref-{$referenceHash}-" . $value,
					'class' => $class,
					'data-citeref-format' => $this->citationReferenceCaptionFormat === SCI_CITEREF_NUM ? 'number' : 'key'
				),
				$minor
			);
		}

		return implode( ' ', $referenceLinks );
	}

	private function createBrowseLinkFor( array $subjects, $reference, $citationText ) {

		// If no text is available at least show the reference
		if ( $citationText === '' ) {
			return Html::rawElement(
				'span',
				array(
					'class' => 'scite-citation-key'
				),
				$reference
			);
		}

		// No browse links means nothing will be displayed
		if ( !$this->browseLinkToCitationResourceState ) {
			return '';
		}

		$citationResourceLinks = $this->citationResourceMatchFinder->findCitationResourceLinks(
			$subjects,
			'scite-citation-resourcelink'
		);

		// Normally we should have only one subject to host a citation resource
		// for the reference in question but it might be that double assingments
		// did occur and therefore show them all
		return implode( ' | ', $citationResourceLinks );
	}

}
