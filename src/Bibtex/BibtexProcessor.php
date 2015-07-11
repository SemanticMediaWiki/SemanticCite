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

		$bibtex = $parserParameterProcessor->getParameterValuesFor( 'bibtex' );

		$parameters = $this->bibtexParser->parse( end( $bibtex ) );

		foreach ( $parameters as $key => $value ) {

			// The explicit reference precedes the one found in bibtex
			if ( $key === 'reference' && $parserParameterProcessor->getFirstParameter() !== '' ) {
				continue;
			}

			$parserParameterProcessor->addParameter(
				$key,
				$value
			);
		}
	}

}
