<?php

namespace SCI\DataValues;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class UidValuePatternParser {

	/**
	 * @var string
	 */
	private $typeid;

	/**
	 * @param string $typeid
	 */
	public function __construct( $typeid ) {
		$this->typeid = $typeid;
	}

	/**
	 * @since 1.0
	 *
	 * @return true
	 */
	public function parse( &$value ) {

		// Remove a possible url-prefix

		if ( $this->typeid ===  '_sci_doi' ) {
			// Remove anything before 10 as in http://dx.doi.org/10.1000/123
			$value = substr( $value, strcspn( $value, '10' ) );
		} elseif ( strrpos( $value, '://' ) !== false ) {
			// Remove anything before the last / as in http://foo/bar123
			$value = substr( $value, strrpos( $value, '/' ) + 1 );
		}

		// Remove other possible prefixes

		// http://www.doi.org/doi_handbook/2_Numbering.html#2.4
		// All DOI names are converted to upper case upon registration
		// but since it system is case insensitive, we store it as lower
		// case as it "looks" better
		if ( $this->typeid ===  '_sci_doi' ) {
			$value = strtolower( $value );
		} else {
			$value = str_replace( array( 'VIAF', 'OCLC', 'PMID' ), '', strtoupper( $value ) );
		}

		return $this->canMatchValueToPattern( $value );
	}

	/**
	 * @return string
	 */
	public function getCanonicalName() {

		switch ( $this->typeid ) {
			case '_sci_viaf':
				return 'VIAF';
			case '_sci_oclc':
				return 'OLCL';
			case '_sci_pmid':
				return 'PMID';
			case '_sci_pmcid':
				return 'PMCID';
			case '_sci_olid':
				return 'OLID';
			case '_sci_doi':
				return 'DOI';
		}

		return null;
	}

	private function canMatchValueToPattern( &$value ) {

		switch ( $this->typeid ) {
			case '_sci_viaf':
			case '_sci_oclc':
			case '_sci_pmid':
	 			return preg_match( "/^[0-9]*$/", $value );
			case '_sci_pmcid':
	 			return preg_match( "/PMC[\d]+/", $value );
			case '_sci_olid':
	 			return preg_match( "/OL[A-Z0-9]+/", $value );
			case '_sci_doi':
				// http://stackoverflow.com/questions/27910/finding-a-doi-in-a-document-or-page#
	 			return preg_match( "/\b(10[.][0-9]{4,}(?:[.][0-9]+)*\/(?:(?![\"&\'])\S)+)\b/", $value );
		}

		return false;
	}

}
