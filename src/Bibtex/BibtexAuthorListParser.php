<?php

namespace SCI\Bibtex;

/**
 * @note most of the parsing code has been copied from PARSECREATORS therefore
 * thanks goes to the authors of http://bibliophile.sourceforge.net
 *
 * Comments to the source code can be found at
 * http://sourceforge.net/projects/bibliophile/files/bibtexParse/ released under
 * under the GPL license.
 *
 * @license GNU GPL v2+
 * @since 1.0
 */
class BibtexAuthorListParser {

	/**
	 * @var array
	 */
	private $prefix = array();

	/**
	 * Create writer arrays from bibtex input
	 *
	 * 'author field can be (delimiters between authors are 'and' or '&'):
	 * 1. <first-tokens> <von-tokens> <last-tokens>
	 * 2. <von-tokens> <last-tokens>, <first-tokens>
	 * 3. <von-tokens> <last-tokens>, <jr-tokens>, <first-tokens>
	 *
	 * @since 1.0
	 *
	 * @param string $input
	 *
	 * @return array
	 */
	public function parse( $input ) {

		$authorList = array();

		// split on ' and '
		$authorArray = preg_split("/\s(and|&)\s/i", trim( $input ) );

		foreach( $authorArray as $value ) {
			$appellation = '';
			$prefix = '';

			$surname = '';
			$initials = '';

			$this->prefix = array();

			$author = explode( ",", preg_replace("/\s{2,}/", ' ', trim( $value ) ) );
			$size = count( $author );

			// No commas therefore something like Mark Grimshaw, Mark Nicholas Grimshaw, M N Grimshaw, Mark N. Grimshaw
			if( $size == 1 ) {
				// Is complete surname enclosed in {...}, unless the string starts with a backslash (\) because then it is
				// probably a special latex-sign..
				// 2006.02.11 DR: in the last case, any NESTED curly braces should also be taken into account! so second
				// clause rules out things such as author="a{\"{o}}"
				//
				if( preg_match("/(.*){([^\\\].*)}/", $value, $matches) &&
					!(preg_match("/(.*){\\\.{.*}.*}/", $value, $matches2 ) ) ) {
					$author = explode(" ", $matches[1]);
					$surname = $matches[2];
				} else {
					$author = explode(" ", $value);
					// last of array is surname (no prefix if entered correctly)
					$surname = array_pop($author);
				}
			} elseif( $size == 2 ) { // Something like Grimshaw, Mark or Grimshaw, Mark Nicholas  or Grimshaw, M N or Grimshaw, Mark N.
				// first of array is surname (perhaps with prefix)
				list( $surname, $prefix ) = $this->grabSurname( array_shift( $author ) );
			} else { // If $size is 3, we're looking at something like Bush, Jr. III, George W
				// middle of array is 'Jr.', 'IV' etc.
				$appellation = implode(' ', array_splice( $author, 1, 1 ) );
				// first of array is surname (perhaps with prefix)
				list( $surname, $prefix ) = $this->grabSurname( array_shift( $author ) );
			}

			$remainder = implode( " ", $author );

			list( $firstname, $initials ) = $this->grabFirstnameInitials( $remainder );

			if( $this->prefix !== array() ) {
				$prefix = implode(' ', $this->prefix );
			}

			$surname = $surname . ' ' . trim( $appellation );

			$authorList[] = $this->concatenate( $firstname, $initials, $surname, $prefix );
		}

		return $authorList;
	}

	private function concatenate( $firstname, $initials, $surname, $prefix ) {

		$author = array(
			trim( $firstname ),
			trim( $initials ),
			trim( $prefix ),
			trim( $surname )
		);

		return implode( ' ', array_filter( $author ) );
	}

	/**
	 * @note firstname and initials which may be of form "A.B.C." or "A. B. C. " or " A B C " etc.
	 */
	private function grabFirstnameInitials( $remainder ) {

		$array = explode( " ", $remainder );

		$firstname = '';
		$initials = '';

		$initialsArray = array();
		$firstnameArray = array();

		foreach( $array as $value ) {
			$firstChar = substr($value, 0, 1);

			if( ( ord( $firstChar ) >= 97 ) && ( ord( $firstChar ) <= 122) ) {
				$this->prefix[] = $value;
			} elseif( preg_match("/[a-zA-Z]{2,}/", trim( $value ) ) ) {
				$firstnameArray[] = trim($value);
			} else {
				$initialsArray[] = str_replace(".", " ", trim( $value ) );
			}
		}

		foreach( $initialsArray as $initial) {
			$initials .= ' ' . trim ( $initial );
		}

		$firstname = implode(" ", $firstnameArray);

		return array( $firstname, $initials );
	}

	/**
	 * @note surname may have title such as 'den', 'von', 'de la' etc. -
	 * characterised by first character lowercased.  Any uppercased part means
	 * lowercased parts following are part of the surname (e.g. Van den Bussche)
	 */
	private function grabSurname( $input ) {
		$surnameArray = explode(" ", $input );

		$noPrefix = false;
		$surname = array();
		$prefix = array();

		foreach( $surnameArray as $value ) {
			$firstChar = substr($value, 0, 1);

			if( !$noPrefix && ( ord( $firstChar ) >= 97 ) && ( ord( $firstChar ) <= 122 ) ) {
				$prefix[] = $value;
			} else {
				$surname[] = $value;
				$noPrefix = TRUE;
			}
		}

		$surname = implode(" ", $surname);

		if( $prefix !== array() ) {
			return array( $surname, implode(" ", $prefix ) );
		}

		return array( $surname, false );
	}
}
