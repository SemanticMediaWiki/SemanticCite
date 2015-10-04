<?php

namespace SCI\Tests\DataValues;

use SCI\DataValues\ResourceIdentifierStringValueParser;

/**
 * @covers \SCI\DataValues\ResourceIdentifierStringValueParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ResourceIdentifierStringValueParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider typeProvider
	 */
	public function testCanConstruct( $type ) {

		$this->assertInstanceOf(
			'\SCI\DataValues\ResourceIdentifierStringValueParser',
			new ResourceIdentifierStringValueParser( $type )
		);
	}

	/**
	 * @dataProvider typeToValueProvider
	 */
	public function testSetUserValue( $type, $value, $expected ) {

		$instance = new ResourceIdentifierStringValueParser( $type );

		$this->assertEquals(
			$expected,
			$instance->parse( $value )
		);
	}

	/**
	 * @dataProvider typeToUriProvider
	 */
	public function testGetResourceTargetUri( $type, $expected ) {

		$instance = new ResourceIdentifierStringValueParser( $type );

		$this->assertEquals(
			$expected,
			$instance->getResourceTargetUri()
		);
	}

	public function typeProvider() {

		$provider[] = array(
			'_sci_doi',
			'DOI'
		);

		$provider[] = array(
			'_sci_viaf',
			'VIAF'
		);

		$provider[] = array(
			'_sci_oclc',
			'OCLC'
		);

		$provider[] = array(
			'_sci_olid',
			'OLID'
		);

		$provider[] = array(
			'_sci_pmcid',
			'PMCID'
		);

		$provider[] = array(
			'_sci_pmid',
			'PMID'
		);

		return $provider;
	}

	public function typeToValueProvider() {

		// DOI

		$provider[] = array(
			'_sci_doi',
			'foo',
			false,
		);

		$provider[] = array(
			'_sci_doi',
			'10.1000.123456',
			false
		);

		$provider[] = array(
			'_sci_doi',
			'10.1000/123456',
			true
		);

		$provider[] = array(
			'_sci_doi',
			'10.1016.12.31/nature.S0735-1097(98)2000/12/31/34:7-7',
			true
		);

		$provider[] = array(
			'_sci_doi',
			'10.1007/978-3-642-28108-2_19',
			true
		);

		$provider[] = array(
			'_sci_doi',
			'10.1007.10/978-3-642-28108-2_19',
			true
		);

		$provider[] = array(
			'_sci_doi',
			'10.1016/S0735-1097(98)00347-7',
			true
		);

		$provider[] = array(
			'_sci_doi',
			'10.1579/0044-7447(2006)35\[89:RDUICP\]2.0.CO;2',
			true
		);

		$provider[] = array(
			'_sci_doi',
			'10.1038/ejcn.2010.73',
			true
		);

		$provider[] = array(
			'_sci_doi',
			'http://dx.doi.org/10.1074/jbc.M114.559054',
			true
		);

		$provider[] = array(
			'_sci_doi',
			'https://doi.org/10.1074/jbc.M114.559054',
			true
		);

		// PMC

		$provider[] = array(
			'_sci_pmcid',
			'foo',
			false,
		);

		$provider[] = array(
			'_sci_pmcid',
			'PM123456',
			false
		);

		$provider[] = array(
			'_sci_pmcid',
			'PMCID123456',
			false
		);

		$provider[] = array(
			'_sci_pmcid',
			'PMC123456',
			true
		);

		// VIAF

		$provider[] = array(
			'_sci_viaf',
			'foo',
			false,
		);

		$provider[] = array(
			'_sci_viaf',
			'123456',
			true
		);

		$provider[] = array(
			'_sci_viaf',
			'vIAf123456',
			true
		);

		$provider[] = array(
			'_sci_viaf',
			'https://viaf.org/viaf/vIAf123456',
			true
		);

		// OCLC

		$provider[] = array(
			'_sci_oclc',
			'foo',
			false,
		);

		$provider[] = array(
			'_sci_oclc',
			'123456',
			true
		);

		$provider[] = array(
			'_sci_oclc',
			'oClc123456',
			true
		);

		$provider[] = array(
			'_sci_oclc',
			'https://www.worldcat.org/oclc/oClc123456',
			true
		);

		// PMID

		$provider[] = array(
			'_sci_pmid',
			'foo',
			false,
		);

		$provider[] = array(
			'_sci_pmid',
			'123456',
			true
		);

		$provider[] = array(
			'_sci_pmid',
			'pMId123456',
			true
		);

		$provider[] = array(
			'_sci_pmid',
			'https://www.ncbi.nlm.nih.gov/pubmed/pMId123456',
			true
		);

		// OLID

		$provider[] = array(
			'_sci_olid',
			'foo',
			false,
		);

		$provider[] = array(
			'_sci_olid',
			'123456',
			false
		);

		$provider[] = array(
			'_sci_olid',
			'123456A',
			false
		);

		$provider[] = array(
			'_sci_olid',
			'OL123456',
			true
		);

		$provider[] = array(
			'_sci_olid',
			'OL123456M',
			true
		);

		$provider[] = array(
			'_sci_olid',
			'https://openlibrary.org/books/OL123456M',
			true
		);

		return $provider;
	}

	public function typeToUriProvider() {

		$provider[] = array(
			'_sci_doi',
			'https://doi.org/'
		);

		$provider[] = array(
			'_sci_viaf',
			'https://viaf.org/viaf/'
		);

		$provider[] = array(
			'_sci_oclc',
			'https://www.worldcat.org/oclc/'
		);

		$provider[] = array(
			'_sci_olid',
			'https://openlibrary.org/books/'
		);

		$provider[] = array(
			'_sci_pmcid',
			'https://www.ncbi.nlm.nih.gov/pmc/'
		);

		$provider[] = array(
			'_sci_pmid',
			'https://www.ncbi.nlm.nih.gov/pubmed/'
		);

		return $provider;
	}

}
