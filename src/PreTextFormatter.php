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
class PreTextFormatter {

	/**
	 * @since 1.4
	 *
	 * @return string
	 */
	public function getFormattedSciteFuncFrom( array $parameters ) {
		return "<pre>{{#scite:\n" . implode( "\n", $this->format( $parameters ) ) ."\n}}</pre>";
	}

	/**
	 * @since 1.4
	 *
	 * @return array
	 */
	public function format( array $parameters ) {

		$formatted = array();

		foreach ( $parameters as $key => $value ) {

			if ( $value === '' || $value{0} === '@' ) {
				continue;
			}

			if ( $value{0} === '+' ) {
				$formatted[$key-1] = $formatted[$key-1] . '|' . $value;
				continue;
			}

			$formatted[$key] = ' |' . $value;
		}

		return $formatted;
	}

}
