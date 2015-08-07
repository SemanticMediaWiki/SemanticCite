<?php

namespace SCI\Bibtex;

/**
 * @note most of the parsing code has been copied from PARSEENTRIES therefore
 * thanks goes to the authors of http://bibliophile.sourceforge.net
 *
 * Comments to the source code can be found at
 * http://sourceforge.net/projects/bibliophile/files/bibtexParse/ and is
 * released under the GPL license.
 *
 * @note There might be a better parser out there but I didn't want to spend to
 * much time reviewing code therefore PARSEENTRIES does the job well.
 *
 * Any fancy macro stuff or other complicated string parsing isn't supported
 * given that the bibtex format misses a proper specification. PARSEENTRIES
 * surely allows to cover more edge cases but for what we want to achieve (to ease
 * copy and paste of existing bibtex records) the current implementation is
 * sufficient.
 *
 * BibtexParserTest provides the test interface to verify edge cases.
 *
 * @license GNU GPL v2+
 * @since 1.0
 */
class BibtexParser {

	/**
	 * @var array
	 */
	private $undefinedStrings = array();

	/**
	 * @var array
	 */
	private $strings = array();

	/**
	 * @since  1.0
	 *
	 * @return array
	 */
	public function parse( $bibtex ) {

		if ( ( $matches = $this->findBibtexFormatMatches( $bibtex ) ) === array() ) {
			return array();
		}

		$head = array(
			'type'      => strtolower( trim( $matches[1] ) ),
			'reference' => $matches[2]
		);

		return $head + $this->parseFields( $matches[3] );
	}

	private function findBibtexFormatMatches( $bibtex ) {

		$matches = preg_split("/@(.*)[{(](.*),/U", $bibtex, 2, PREG_SPLIT_DELIM_CAPTURE );

		// Silently retreat from processing
		if ( !isset( $matches[2] ) ) {
			return array();
		}

		if( preg_match("/=/", $matches[2] ) ) {
			$matches = preg_split("/@(.*)\s*[{(](.*)/U", $bibtex, 2, PREG_SPLIT_DELIM_CAPTURE );
		}

		return $matches;
	}

	private function parseFields( $content ) {
		$elements = array();
		$values = array();

		$length = strlen( $content );

		if( $content[$length - 1] == "}" ||  $content[$length - 1] == ")" ||  $content[$length - 1] == ",") {
			$content = substr( $content,  0, $length - 1 );
		}

		$split = preg_split("/=/",  $content, 2 );
		$string = $split[1];

		while( $string ) {
			list( $entry, $string ) = $this->splitField( $string );
			$values[] = $entry;
		}

		foreach( $values as $value ) {
			$pos = strpos( $content, $value);
			$content = substr_replace( $content, '', $pos, strlen( $value ) );
		}

		$rev = strrev( trim( $content ) );

		if( $rev{0} != ',') {
			 $content .= ',';
		}

		$keys = preg_split("/=,/",  $content );
		array_pop($keys);

		foreach( $keys as $key ) {
			$value = trim( array_shift( $values ) );
			$rev = strrev( $value );

			// remove any dangling ',' left on final field of entry
			if($rev{0} == ',') {
				$value = rtrim($value, ",");
			}

			if(!$value) {
				continue;
			}

			$key = strtolower(trim($key));
			$value = trim($value);
			$elements[$key] = $this->removeDelimiters( $value );
		}

		return $elements;
	}

	private function splitField( $seg ) {

		$array = preg_split("/,\s*([-_.:,a-zA-Z0-9]+)\s*={1}\s*/U", $seg, PREG_SPLIT_DELIM_CAPTURE );

	//	if(!array_key_exists( 1, $array ) ) {
	//		return array( $array[0], FALSE);
	//	}

		return isset( $array[1] ) ? array( $array[0], $array[1] ) : array( $array[0], false );
	}

	private function removeDelimiters( $string ) {

		if( $string  && ( $string{0} == "\"") ) {
			$string = substr($string, 1);
			$string = substr($string, 0, -1);
		} else if ( $string && ($string{0} == "{") ) {
			if( strlen( $string ) > 0 && $string[strlen($string)-1] == "}" ) {
				$string = substr($string, 1);
				$string = substr($string, 0, -1);
			}

	//	} else if(!is_numeric($string) && !array_key_exists($string, $this->strings)
	//		 && (array_search($string, $this->undefinedStrings) === FALSE ) ) {
	//		$this->undefinedStrings[] = $string; // Undefined string that is not a year etc.
	//		return '';
		}

		return $string;
	}
}
