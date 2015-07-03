<?php

namespace SCI;

use Parser;
use Html;
use SMW\MediaWiki\Renderer\HtmlColumnListRenderer;
use SMW\DataValueFactory;
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
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var integer
	 */
	private $numberOfReferenceListColumns = 1;

	/**
	 * @var boolean
	 */
	private $browseLinkToCitationResourceState = true;

	/**
	 * @var string
	 */
	private $referenceListType = 'ol';

	/**
	 * @since  1.0
	 *
	 * @param CitationResourceMatchFinder $citationResourceMatchFinder
	 * @param CitationReferencePositionJournal $citationReferencePositionJournal
	 * @param HtmlColumnListRenderer $htmlColumnListRenderer
	 * @param Parser $parser
	 */
	public function __construct( CitationResourceMatchFinder $citationResourceMatchFinder, CitationReferencePositionJournal $citationReferencePositionJournal, HtmlColumnListRenderer $htmlColumnListRenderer, $parser ) {
		$this->citationResourceMatchFinder = $citationResourceMatchFinder;
		$this->citationReferencePositionJournal = $citationReferencePositionJournal;
		$this->htmlColumnListRenderer = $htmlColumnListRenderer;
		$this->parser = $parser;
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
	 * @param string $referenceListType
	 */
	public function setReferenceListType( $referenceListType ) {
		$this->referenceListType = $referenceListType;
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
	 * @param DIWikiPage $subject
	 *
	 * @return string
	 */
	public function renderReferenceListFor( DIWikiPage $subject ) {

		$journal = $this->citationReferencePositionJournal->getJournalBySubject(
			$subject
		);

		if ( $journal !== null ) {
			return $this->createHtmlFromList( $journal );
		}

		return '';
	}

	/**
	 * The journal is expected to contain:
	 *
	 * 'total'      => a number
	 * 'reference-list' => array of hashes for references used
	 * 'reference-pos'  => individual reference links (1-a, 1-b) assigned to a hash
	 */
	private function createHtmlFromList( array $referenceList ) {

		$listOfFormattedReferences = array();

		foreach ( $referenceList['reference-pos'] as $referenceAsHash => $linkList ) {

			// Get the "human" readable citation key/reference from the hashmap
			// intead of trying to access the DB/Store
			$reference = $referenceList['reference-list'][$referenceAsHash];

			list( $subjects, $citationText ) = $this->findCitationTextFor(
				$reference
			);

			$flatHtmlReferenceLinks = $this->createFlatHtmlListForReferenceLinks(
				$linkList,
				$referenceAsHash
			);

			$browseLinks = $this->createBrowseLinkFor( $subjects, $reference );

			$listOfFormattedReferences[] =
				Html::rawElement(
					'span',
					array(
						'id'    => 'scite-'. $referenceAsHash,
						'class' => 'scite-referencelinks'
					),
					$flatHtmlReferenceLinks
					) . '&nbsp;'  .
				Html::rawElement(
					'span',
					array(
						'id'    => 'scite-'. $referenceAsHash,
						'class' => 'scite-citation'
					),
					$browseLinks . '&nbsp;' . Html::rawElement(
						'span',
						array( 'class' => 'scite-citation-text' ),
						$citationText
					)
				);
		}

		$this->htmlColumnListRenderer->setColumnListClass( 'scite-referencelist' );
		$this->htmlColumnListRenderer->setNumberOfColumns( $this->numberOfReferenceListColumns );
		$this->htmlColumnListRenderer->setListType( $this->referenceListType );
		$this->htmlColumnListRenderer->addContentsByNoIndex( $listOfFormattedReferences );

		return Html::rawElement(
				'div',
				array(
					'class' => 'scite-content'
				),
				Html::element(
				'h2',
				array(),
				wfMessage( 'sci-referencelist-header' )->inContentLanguage()->text()
			) . "\n" . $this->htmlColumnListRenderer->getHtml() . "\n"
		);
	}

	private function findCitationTextFor( $reference ) {

		$text = '';
		$subjects = array();

		$queryResult = $this->citationResourceMatchFinder->findMatchForCitationReference(
			$reference
		);

		if ( !$queryResult instanceof \SMWQueryResult ) {
			return array( $subjects, $text );
		}

		while ( $resultArray = $queryResult->getNext() ) {
			foreach ( $resultArray as $result ) {

				// Collect all matches for the same reference because it can happen
				// that the same reference key is used for different citation
				// resources therefore we only return one (the last) valid citation
				// text but nevertheless return all subjects to make easier to find them
				$subjects[] = $result->getResultSubject();

				while ( ( $dataValue = $result->getNextDataValue() ) !== false ) {
					$text = $this->getFormattedText( $dataValue );
				}
			}
		}

		return array( $subjects, $text );
	}

	/**
	 * Check for ParserOptions to avoid a "Call to a member function getMaxIncludeSize()
	 * Parser.php on line 3266" encountered on the 1.24 diff view
	 */
	private function getFormattedText( $dataValue ) {

		if ( $this->parser->getOptions() !== null ) {
			return $this->parser->recursiveTagParse( $dataValue->getShortWikiText() );
		}

		return $dataValue->getShortWikiText();
	}

	private function createFlatHtmlListForReferenceLinks( array $linkList, $referenceHash ) {

		$referenceLinks = array();
		$class = 'scite-backlinks';

		foreach ( $linkList as $value ) {

			// Split a value of 1-a, 1-b, 2-a into its parts
			list( $major, $minor ) = explode( '-', $value );

			// We only have one link therefore just show a simple link
			// similar to what is done on en.wp
			if ( count( $linkList ) == 1 ) {
				$minor = '^';
				$class = 'scite-backlink';
			}

			$referenceLinks[] = Html::rawElement(
				'a',
				array(
					'href'  => "#scite-ref-{$referenceHash}-" . $value,
					'class' => $class
				),
				$this->referenceListType === 'ul' ? str_replace( '-', '.', $value ) : $minor
			);
		}

		return implode( ' ', $referenceLinks );
	}

	private function createBrowseLinkFor( array $subjects, $reference ) {

		if ( $subjects === array() || !$this->browseLinkToCitationResourceState ) {
			return $reference;
		}

		$references = array();

		foreach ( $subjects as $subject ) {

			$dataValue = DataValueFactory::getInstance()->newDataItemValue(
				$subject,
				null
			);

			$browselink = \SMWInfolink::newBrowsingLink(
				'&#8593;', //$reference,
				$dataValue->getWikiValue(),
				'scite-citation-resourcelink'
			);

			$references[] = $browselink->getHTML();
		}

		// Normally we should have only one subject to host a citation resource
		// for the reference in question but it might be that double assingments
		// did occur and therefore show them all
		return implode( ' | ', $references );
	}

}
