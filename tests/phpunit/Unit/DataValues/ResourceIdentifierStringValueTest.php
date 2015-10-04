<?php

namespace SCI\Tests\DataValues;

use SCI\DataValues\ResourceIdentifierStringValue;

/**
 * @covers \SCI\DataValues\ResourceIdentifierStringValue
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ResourceIdentifierStringValueTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider typeProvider
	 */
	public function testCanConstruct( $type ) {

		$this->assertInstanceOf(
			'\SCI\DataValues\ResourceIdentifierStringValue',
			new ResourceIdentifierStringValue()
		);
	}

	/**
	 * @dataProvider doiNonLinkedTextProvider
	 */
	public function testNonLinkedText( $type, $value, $expectedWikiText, $expectedHtmlText ) {

		$instance = new ResourceIdentifierStringValue( $type );
		$instance->setUserValue( $value );

		$this->assertEquals(
			$expectedWikiText,
			$instance->getShortWikiText()
		);

		$this->assertEquals(
			$expectedWikiText,
			$instance->getLongWikiText()
		);

		$this->assertEquals(
			$expectedHtmlText,
			$instance->getShortHTMLText()
		);

		$this->assertEquals(
			$expectedHtmlText,
			$instance->getLongHTMLText()
		);
	}

	/**
	 * @dataProvider doiLinkedTextProvider
	 */
	public function testLinkedText( $type, $value, $expectedWikiText, $expectedHtmlText ) {

		$instance = new ResourceIdentifierStringValue( $type );
		$instance->setUserValue( $value );

		$this->assertEquals(
			$expectedWikiText,
			$instance->getShortWikiText( 'foo' )
		);

		$this->assertEquals(
			$expectedWikiText,
			$instance->getLongWikiText( 'foo' )
		);

		$this->assertEquals(
			$expectedHtmlText,
			$instance->getShortHTMLText( 'foo' )
		);

		$this->assertEquals(
			$expectedHtmlText,
			$instance->getLongHTMLText( 'foo' )
		);
	}

	/**
	 * @dataProvider outputFormattedTextProvider
	 */
	public function testOutputFormattedText( $type, $value, $format, $expectedWikiText, $expectedHtmlText ) {

		$instance = new ResourceIdentifierStringValue( $type );
		$instance->setUserValue( $value );
		$instance->setOutputFormat( $value );

		$this->assertEquals(
			$expectedWikiText,
			$instance->getShortWikiText()
		);

		$this->assertEquals(
			$expectedWikiText,
			$instance->getLongWikiText()
		);

		$this->assertEquals(
			$expectedHtmlText,
			$instance->getShortHTMLText()
		);

		$this->assertEquals(
			$expectedHtmlText,
			$instance->getLongHTMLText()
		);
	}

	public function typeProvider() {

		$provider[] = array(
			'_sci_doi',
		);

		$provider[] = array(
			'_sci_viaf',
		);

		$provider[] = array(
			'_sci_oclc',
		);

		$provider[] = array(
			'_sci_olid',
		);

		$provider[] = array(
			'_sci_pmcid',
		);

		$provider[] = array(
			'_sci_pmid',
		);

		return $provider;
	}

	public function doiNonLinkedTextProvider() {

		// DOI

		$provider[] = array(
			'_sci_doi',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_doi',
			'10.1000/123456',
			'10.1000/123456',
			'10.1000/123456'
		);

		$provider[] = array(
			'_sci_doi',
			'http://dx.doi.org/10.1000/123456',
			'10.1000/123456',
			'10.1000/123456'
		);

		$provider[] = array(
			'_sci_doi',
			'http://dx.doi.org/10.1000/abc.456',
			'10.1000/abc.456',
			'10.1000/abc.456'
		);

		// PMC

		$provider[] = array(
			'_sci_pmcid',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_pmcid',
			'PMC123456',
			'PMC123456',
			'PMC123456'
		);

		// VIAF

		$provider[] = array(
			'_sci_viaf',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_viaf',
			'VIAF123456',
			'123456',
			'123456'
		);

		// OCLC

		$provider[] = array(
			'_sci_oclc',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_oclc',
			'OCLC123456',
			'123456',
			'123456'
		);

		// PMID

		$provider[] = array(
			'_sci_pmid',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_pmid',
			'PMID123456',
			'123456',
			'123456'
		);

		// OLID

		$provider[] = array(
			'_sci_olid',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_olid',
			'OL123456M',
			'OL123456M',
			'OL123456M'
		);

		return $provider;
	}

	public function doiLinkedTextProvider() {

		// DOI

		$provider[] = array(
			'_sci_doi',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_doi',
			'10.1000/123456',
			'<span>[https://doi.org/10.1000%2F123456 10.1000/123456]</span>',
			'<a href="https://doi.org/10.1000/123456" target="_blank">10.1000/123456</a>'
		);

		$provider[] = array(
			'_sci_doi',
			'http://dx.doi.org/10.1000/123456',
			'<span>[https://doi.org/10.1000%2F123456 10.1000/123456]</span>',
			'<a href="https://doi.org/10.1000/123456" target="_blank">10.1000/123456</a>'
		);

		// PMC

		$provider[] = array(
			'_sci_pmcid',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_pmcid',
			'PMC123456',
			'<span>[https://www.ncbi.nlm.nih.gov/pmc/PMC123456 PMC123456]</span>',
			'<a href="https://www.ncbi.nlm.nih.gov/pmc/PMC123456" target="_blank">PMC123456</a>'
		);

		// VIAF

		$provider[] = array(
			'_sci_viaf',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_viaf',
			'VIAF123456',
			'<span>[https://viaf.org/viaf/123456 123456]</span>',
			'<a href="https://viaf.org/viaf/123456" target="_blank">123456</a>'
		);

		// OCLC

		$provider[] = array(
			'_sci_oclc',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_oclc',
			'OCLC123456',
			'<span>[https://www.worldcat.org/oclc/123456 123456]</span>',
			'<a href="https://www.worldcat.org/oclc/123456" target="_blank">123456</a>'
		);

		// PMID

		$provider[] = array(
			'_sci_pmid',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_pmid',
			'PMID123456',
			'<span>[https://www.ncbi.nlm.nih.gov/pubmed/123456 123456]</span>',
			'<a href="https://www.ncbi.nlm.nih.gov/pubmed/123456" target="_blank">123456</a>'
		);

		// OLID

		$provider[] = array(
			'_sci_olid',
			'foo',
			'',
			''
		);

		$provider[] = array(
			'_sci_olid',
			'OL123456M',
			'<span>[https://openlibrary.org/books/OL123456M OL123456M]</span>',
			'<a href="https://openlibrary.org/books/OL123456M" target="_blank">OL123456M</a>'
		);

		return $provider;
	}

	public function outputFormattedTextProvider() {

		// DOI

		$provider[] = array(
			'_sci_doi',
			'10.1000/123456',
			'-',
			'10.1000/123456',
			'10.1000/123456'
		);

		$provider[] = array(
			'_sci_doi',
			'http://dx.doi.org/10.1000/123456',
			'-',
			'10.1000/123456',
			'10.1000/123456'
		);

		return$provider;
	}

}
