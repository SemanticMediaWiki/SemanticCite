<?php

namespace SCI;

use SMW\ParserParameterProcessor;
use Html;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ReferenceListParserFunction {

	/**
	 * {{#referencelist:
	 * |columns=2
	 * |listtype="ol"
	 * |browselinks=true
	 * }}
	 *
	 * @since 1.0
	 *
	 * @param ParserParameterProcessor $parserParameterProcessor
	 */
	public function doProcess( ParserParameterProcessor $parserParameterProcessor ) {

		list( $header, $headerElement, $attributes ) = $this->getElementsForHtml(
			$parserParameterProcessor->toArray()
		);

		// Only the Parser can add a section/toc entry therefore the default reference
		// list is "too" late for the parser to process/add a toc section therefore only
		// the #referencelist can create a placeholder so that by the time the reference
		// list is generated the header is recognized.

		// The parser will set the headerTocId and is later fetched by the
		// CachedReferenceListOutputRenderer when replacing the placeholder. This
		// also takes care of any encoded title with non-Latin characters
		$header = Html::element(
			$headerElement,
			array(),
			$header
		);

		$html = Html::rawElement(
			'div',
			$attributes,
			$header
		);

		return $html . "<!-- end marker -->\n";
	}

	private function getElementsForHtml( $parameters ) {

		$header = wfMessage( 'sci-referencelist-header' )->text();

		// The span placeholder will hide the header from the TOC by default
		$headerElement = 'span';

		$attributes = array(
			'id' => 'scite-custom-referencelist'
		);

		foreach ( $parameters as $key => $values ) {

			if ( $key === 'references' ) {
				$attributes['data-references'] = json_encode( $values );
				continue;
			}

			foreach ( $values as $value ) {

				if ( $key === 'header' ) {
					$header = $value;
				}

				if ( $key === 'toc' && filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
					$headerElement = 'h2';
				}

				$attributes['data-'. $key] = $value;
			}
		}

		return array( $header, $headerElement, $attributes );
	}

}
