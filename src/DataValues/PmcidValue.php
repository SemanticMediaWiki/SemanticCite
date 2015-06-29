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
class PmcidValue extends StringValue {

	/**
	 * @see https://www.nlm.nih.gov/pubs/techbull/nd09/nd09_pmc_urls.html
	 */
	const URL_RESOLVER = "https://www.ncbi.nlm.nih.gov/pmc/";

	/**
	 * @param string $typeid
	 */
	public function __construct( $typeid = '' ) {
		parent::__construct( '_sci_pmcid' );
	}

	/**
	 * @see StringValue::parseUserValue
	 */
	protected function parseUserValue( $value ) {

		if ( !$this->canMatchPMCPattern( $value ) ) {
			$this->addError( wfMessage( 'sci-datavalue-no-valid-pmcid-format', $value )->inContentLanguage()->text() );
			$this->m_dataitem = new DIBlob( 'ERROR' );
			return;
		}

		$this->m_caption = $value;

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

		if ( $linker === null ) {
			return $this->m_caption;
		}

		$url = self::URL_RESOLVER . urlencode( $this->m_caption );

		return Html::rawElement(
			'span',
			array(),
			'[' . $url . ' ' . $this->m_caption .']'
		);
	}

	public function getShortHTMLText( $linker = null ) {

		if ( !$this->isValid() ) {
			return '';
		}

		if ( !$this->m_caption ) {
			$this->m_caption = $this->m_dataitem->getString();
		}

		if ( $linker === null ) {
			return $this->m_caption;
		}

		return Html::rawElement(
			'a',
			array(
				'href'   => self::URL_RESOLVER . $this->m_caption,
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

	private function canMatchPMCPattern( $value ) {
		return preg_match( "/\PMC[\d]+/", $value );
	}

}
