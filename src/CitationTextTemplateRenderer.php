<?php

namespace SCI;

use SMW\MediaWiki\Renderer\WikitextTemplateRenderer;
use Parser;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CitationTextTemplateRenderer {

	/**
	 * @var WikitextTemplateRenderer
	 */
	private $wikitextTemplateRenderer;

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var string
	 */
	private $templateName = '';

	/**
	 * @since 1.0
	 *
	 * @param WikitextTemplateRenderer $wikitextTemplateRenderer
	 * @param Parser $parser
	 */
	public function __construct( WikitextTemplateRenderer $wikitextTemplateRenderer, Parser $parser ) {
		$this->wikitextTemplateRenderer = $wikitextTemplateRenderer;
		$this->parser = $parser;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $templateName
	 */
	public function packFieldsForTemplate( $templateName ) {
		$this->templateName = $templateName;
	}

	/**
	 * @since 1.0
	 *
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function renderFor( array $parameters ) {

		$wikiText = $this->doFormat( $parameters );

		if ( $wikiText === '' ) {
			return '';
		}

		return $this->parser->recursivePreprocess( $wikiText );
	}

	private function doFormat( array $parameters ) {

		if ( $this->templateName === '' ) {
			return '';
		}

		foreach ( $parameters as $key => $values ) {

			$key = strtolower( trim( $key ) );
			$pieces = array();

			foreach ( $values as $value ) {
				$pieces[] = trim( $value );
			}

			$this->wikitextTemplateRenderer->addField( $key, implode( ', ', $pieces ) );
		}

		$this->wikitextTemplateRenderer->packFieldsForTemplate( $this->templateName );

		return $this->wikitextTemplateRenderer->render();
	}

}
