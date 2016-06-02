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
class ResourceIdentifierStringValue extends StringValue {

	/**
	 * @var ResourceIdentifierStringValueParser
	 */
	private $resourceIdentifierStringValueParser;

	/**
	 * @param string $typeid
	 */
	public function __construct( $typeid = '' ) {
		parent::__construct( $typeid );
		$this->resourceIdentifierStringValueParser = new ResourceIdentifierStringValueParser( $typeid );
	}

	/**
	 * @see StringValue::parseUserValue
	 */
	protected function parseUserValue( $value ) {

		$inputValue = $value;
	//	$this->m_caption = $value;

		if ( !$this->resourceIdentifierStringValueParser->parse( $value ) ) {
			$this->addError( wfMessage( 'sci-datavalue-invalid-id-value', $inputValue, $this->resourceIdentifierStringValueParser->getCanonicalName() )->inContentLanguage()->escaped() );
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

	/**
	 * @see StringValue::getShortHTMLText
	 */
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

	/**
	 * @see StringValue::getLongWikiText
	 */
	public function getLongWikiText( $linked = null ) {
		return $this->getShortWikiText( $linked );
	}

	/**
	 * @see StringValue::getLongHTMLText
	 */
	public function getLongHTMLText( $linker = null ) {
		return $this->getShortHTMLText( $linker );
	}

	/**
	 * @see DataValue::getPreferredCaption
	 */
	public function getPreferredCaption() {

		if ( $this->m_outformat == '-' ) {
			return $this->m_caption;
		}

		return false;
	}

	private function getTargetLink( $target ) {
		return $this->resourceIdentifierStringValueParser->getResourceTargetUri() . $target;
	}

}
