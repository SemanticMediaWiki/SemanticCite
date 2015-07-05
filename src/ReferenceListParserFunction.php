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

		$header = '';

		$attributes = array(
			'id' => 'scite-custom-referencelist'
		);

		foreach ( $parserParameterProcessor->toArray() as $key => $values ) {

			if ( $key === 'references' ) {
				$attributes['data-references'] = json_encode( $values );
				continue;
			}

			foreach ( $values as $value ) {

				if ( $key === 'header' ) {
					$header = $value;
				}

				$attributes['data-'. $key] = $value;
			}
		}

		$header = Html::element(
			'h2',
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

}
