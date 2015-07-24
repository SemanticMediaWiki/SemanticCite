<?php

namespace SCI\Bibtex;

use SMW\ParserParameterProcessor;

/**
 * @license GNU GPL v2+
 * @since 1.0
 */
class BibtexProcessor {

	/**
	 * @var BibtexParser
	 */
	private $bibtexParser;

	/**
	 * @since 1.0
	 *
	 * @param BibtexParser $bibtexParser
	 */
	public function __construct( BibtexParser $bibtexParser ) {
		$this->bibtexParser = $bibtexParser;
	}

	/**
	 * @since 1.0
	 *
	 * @param ParserParameterProcessor $parserParameterProcessor
	 */
	public function doProcess( ParserParameterProcessor $parserParameterProcessor ) {

		$bibtex = $this->doPreprocess(
			$parserParameterProcessor->getParameterValuesFor( 'bibtex' )
		);

		$parameters = $this->bibtexParser->parse( $bibtex );

		foreach ( $parameters as $key => $value ) {

			// The explicit parameters precedes the one found in bibtex
			if ( $key === 'reference' && ( $parserParameterProcessor->hasParameter( 'reference' ) || $parserParameterProcessor->getFirstParameter() !== '' ) ) {
				continue;
			}

			if ( $key === 'type' && $parserParameterProcessor->hasParameter( 'type' ) ) {
				continue;
			}

			$parserParameterProcessor->addParameter(
				$key,
				$value
			);
		}
	}

	private function doPreprocess( array $bibtex ) {

		$bibtex = end( $bibtex );

		// Avoid things like {{Stable theories}}" which are not supported in MW
		// since the parser replaces it with [[:Template:Stable theories]]
		$this->replace( '{{', "{", $bibtex );
		$this->replace( '}}', "}", $bibtex );

		$this->replace( '{\textquotesingle}', "'", $bibtex );
		$this->replace( '$\upgamma$', "γ", $bibtex );

		return $bibtex;
	}

	private function replace( $search, $with, &$on ) {
		$on = strpos( $on, $search ) !== false ? str_replace( $search, $with, $on ) : $on;
	}

}
