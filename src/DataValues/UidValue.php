<?php

namespace SCI\DataValues;

use SMWStringValue as StringValue;
use SMWDIBlob as DIBlob;
use Html;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class UidValue extends StringValue {

	/**
	 * @var UidValuePatternParser
	 */
	private $uidValuePatternParser;

	/**
	 * @param string $typeid
	 */
	public function __construct( $typeid = '' ) {
		parent::__construct( $typeid );
		$this->uidValuePatternParser = new UidValuePatternParser( $typeid );
	}

	/**
	 * @see StringValue::parseUserValue
	 */
	protected function parseUserValue( $value ) {

		$inputValue = $value;
	//	$this->m_caption = $value;

		if ( !$this->uidValuePatternParser->parse( $value ) ) {
			$this->addError( wfMessage( 'sci-datavalue-invalid-id-value', $inputValue, $this->uidValuePatternParser->getCanonicalName() )->inContentLanguage()->escaped() );
			$this->m_dataitem = new DIBlob( 'ERROR' );
			return;
		}

		parent::parseUserValue( $value );
	}

	/**
	 * @see StringValue::getShortWikiText
	 */
	public function getShortWikiText( $linker = null ) {

		if ( !$this->isValid() ) {
			return '';
		}

		if ( !$this->m_caption ) {
			$this->m_caption = $this->m_dataitem->getString();
		}

		if ( $preferredCaption = $this->getPreferredCaption() ) {
			return $preferredCaption;
		}

		if ( $linker === null ) {
			return $this->m_caption;
		}

		return Html::rawElement(
			'span',
			array(),
			'[' . $this->getTargetLink( urlencode( $this->m_caption ) ) . ' ' . $this->m_caption .']'
		);
	}

	public function getShortHTMLText( $linker = null ) {

		if ( !$this->isValid() ) {
			return '';
		}

		if ( !$this->m_caption ) {
			$this->m_caption = $this->m_dataitem->getString();
		}

		if ( $preferredCaption = $this->getPreferredCaption() ) {
			return $preferredCaption;
		}

		if ( $linker === null ) {
			return $this->m_caption;
		}

		return Html::rawElement(
			'a',
			array(
				'href'   => $this->getTargetLink( $this->m_caption ),
				'target' => '_blank'
			),
			$this->m_caption
		);
	}

	public function getLongWikiText( $linked = null ) {
		return $this->getShortWikiText( $linked );
	}

	public function getLongHTMLText( $linker = null ) {
		return $this->getShortHTMLText( $linker );
	}

	private function getTargetLink( $target ) {

		$uri = '';

		switch ( $this->m_typeid ) {
			case '_sci_viaf':
	 			// http://www.oclc.org/research/activities/viaf.html
	 			// http://id.loc.gov/vocabulary/identifiers/viaf.html
				$uri = "https://viaf.org/viaf/";
				break;
			case '_sci_oclc':
				// http://www.oclc.org/support/documentation/glossary/oclc.en.html#OCLCControlNumber
				$uri = "https://www.worldcat.org/oclc/";
				break;
			case '_sci_pmcid':
				// https://www.nlm.nih.gov/pubs/techbull/nd09/nd09_pmc_urls.html
				$uri = "https://www.ncbi.nlm.nih.gov/pmc/";
				break;
			case '_sci_pmid':
				$uri = "https://www.ncbi.nlm.nih.gov/pubmed/";
				break;
			case '_sci_olid':
				$uri = "https://openlibrary.org/books/";
				break;
			case '_sci_doi':
				// https://en.wikipedia.org/wiki/Digital_object_identifier
				$uri = "https://doi.org/";
				break;
		}

		return $uri . $target;
	}

	private function getPreferredCaption() {

		if ( $this->m_outformat == '-' ) {
			return $this->m_caption;
		}

		return false;
	}

}
