<?php

namespace SCI\DataValues;

use SCI\CitationReferencePositionJournal;
use SMW\DataTypeRegistry;
use SMWStringValue as StringValue;
use SMWDIBlob as DIBlob;
use Html;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CitationReferenceValue extends StringValue {

	/**
	 * @var integer
	 */
	private $captionFormat;

	/**
	 * @var CitationReferencePositionJournal
	 */
	private $citationReferencePositionJournal;

	/**
	 * @var string
	 */
	private $reference;

	/**
	 * @param string $typeid
	 */
	public function __construct( $typeid = '' ) {
		parent::__construct( '_sci_ref' );

		// Currently there is no good way to inject the setting
		$this->captionFormat = $GLOBALS['scigCitationReferenceCaptionFormat'];
	}

	/**
	 * @since 1.0
	 *
	 * @param  integer $captionFormat
	 */
	public function setCaptionFormat( $captionFormat ) {
		$this->captionFormat = $captionFormat;
	}

	/**
	 * @see StringValue::parseUserValue
	 */
	protected function parseUserValue( $value ) {

		$this->citationReferencePositionJournal = $this->getExtraneousFunctionFor( 'CitationReferencePositionJournal' );

		$value = trim( $value );

		if ( $value === '' ) {
			$this->addError( wfMessage( 'sci-datavalue-empty-reference' )->inContentLanguage()->text() );
			$this->m_dataitem = new DIBlob( 'ERROR' );
			return;
		}

		if ( $this->m_contextPage === null ) {
			parent::parseUserValue( $value );
			return;
		}

		$this->reference = $value;
		$this->m_caption = $this->reference;

		// This is where the magic happens, compute the position of
		// a reference relative to previous CiteRef annotations
		$this->citationReferencePositionJournal->addJournalEntryFor(
			$this->m_contextPage,
			$this->reference
		);

		parent::parseUserValue( $this->reference );
	}

	/**
	 * @see StringValue::parseUserValue
	 */
	public function getShortWikiText( $linked = null ) {

		// We want the last entry here to get the major/minor
		// number that was internally recorded
		$lastReferenceEntry = $this->citationReferencePositionJournal->findLastReferencePositionEntryFor(
			$this->m_contextPage,
			$this->reference
		);

		if ( $lastReferenceEntry === null || $this->m_caption === false ) {
			return '';
		}

		$referenceHash = md5( $this->reference );

		if ( $this->captionFormat === SCI_CITEREF_NUM ) {
			list( $major, $minor ) = explode( '-', $lastReferenceEntry );
			$caption = $major;
			$captionClass = 'number';
		} else {
			$captionClass = 'key';
			$caption = $this->m_caption;
		}

		// Build element with back and forth link anchor
		return Html::rawElement(
			'span',
			array(
				'id'    => 'scite-ref-'. $referenceHash . '-' . $lastReferenceEntry,
				'class' => 'scite-citeref-' . $captionClass,
				'data-reference' => $this->reference
			),
			'[[' .'#scite-' . $referenceHash . '|' . $caption . ']]'
		);
	}

}
