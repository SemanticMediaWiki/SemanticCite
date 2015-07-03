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
	 * |listType="ol"
	 * |browseLinks=true
	 * }}
	 *
	 * @since 1.0
	 *
	 * @param ParserParameterProcessor $parserParameterProcessor
	 */
	public function doProcess( ParserParameterProcessor $parserParameterProcessor ) {

		$attributes = array(
			'id' => 'scite-custom-referencelist'
		);

		foreach ( $parserParameterProcessor->toArray() as $key => $values ) {
			foreach ( $values as $value ) {
				$attributes['data-'. $key] = $value;
			}
		}

		$html = Html::rawElement(
			'div',
			$attributes
		);

		return $html;
	}

}
