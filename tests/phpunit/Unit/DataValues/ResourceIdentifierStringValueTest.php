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
class ResourceIdentifierStringValueTest extends \PHPUnit\Framework\TestCase {

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

		$provider[] = [
			'_sci_doi',
		];

		$provider[] = [
			'_sci_viaf',
		];

		$provider[] = [
			'_sci_oclc',
		];

		$provider[] = [
			'_sci_olid',
		];

		$provider[] = [
			'_sci_pmcid',
		];

		$provider[] = [
			'_sci_pmid',
		];

		return $provider;
	}

	public function doiNonLinkedTextProvider() {

		// DOI

		$provider[] = [
			'_sci_doi',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_doi',
			'10.1000/123456',
			'10.1000/123456',
			'10.1000/123456'
		];

		$provider[] = [
			'_sci_doi',
			'http://dx.doi.org/10.1000/123456',
			'10.1000/123456',
			'10.1000/123456'
		];

		$provider[] = [
			'_sci_doi',
			'http://dx.doi.org/10.1000/abc.456',
			'10.1000/abc.456',
			'10.1000/abc.456'
		];

		// PMC

		$provider[] = [
			'_sci_pmcid',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_pmcid',
			'PMC123456',
			'PMC123456',
			'PMC123456'
		];

		// VIAF

		$provider[] = [
			'_sci_viaf',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_viaf',
			'VIAF123456',
			'123456',
			'123456'
		];

		// OCLC

		$provider[] = [
			'_sci_oclc',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_oclc',
			'OCLC123456',
			'123456',
			'123456'
		];

		// PMID

		$provider[] = [
			'_sci_pmid',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_pmid',
			'PMID123456',
			'123456',
			'123456'
		];

		// OLID

		$provider[] = [
			'_sci_olid',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_olid',
			'OL123456M',
			'OL123456M',
			'OL123456M'
		];

		return $provider;
	}

	public function doiLinkedTextProvider() {

		// DOI

		$provider[] = [
			'_sci_doi',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_doi',
			'10.1000/123456',
			'<span class="plainlinks">[https://doi.org/10.1000%2F123456 10.1000/123456]</span>',
			'<a href="https://doi.org/10.1000/123456" target="_blank">10.1000/123456</a>'
		];

		$provider[] = [
			'_sci_doi',
			'http://dx.doi.org/10.1000/123456',
			'<span class="plainlinks">[https://doi.org/10.1000%2F123456 10.1000/123456]</span>',
			'<a href="https://doi.org/10.1000/123456" target="_blank">10.1000/123456</a>'
		];

		// PMC

		$provider[] = [
			'_sci_pmcid',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_pmcid',
			'PMC123456',
			'<span class="plainlinks">[https://www.ncbi.nlm.nih.gov/pmc/PMC123456 PMC123456]</span>',
			'<a href="https://www.ncbi.nlm.nih.gov/pmc/PMC123456" target="_blank">PMC123456</a>'
		];

		// VIAF

		$provider[] = [
			'_sci_viaf',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_viaf',
			'VIAF123456',
			'<span class="plainlinks">[https://viaf.org/viaf/123456 123456]</span>',
			'<a href="https://viaf.org/viaf/123456" target="_blank">123456</a>'
		];

		// OCLC

		$provider[] = [
			'_sci_oclc',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_oclc',
			'OCLC123456',
			'<span class="plainlinks">[https://www.worldcat.org/oclc/123456 123456]</span>',
			'<a href="https://www.worldcat.org/oclc/123456" target="_blank">123456</a>'
		];

		// PMID

		$provider[] = [
			'_sci_pmid',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_pmid',
			'PMID123456',
			'<span class="plainlinks">[https://www.ncbi.nlm.nih.gov/pubmed/123456 123456]</span>',
			'<a href="https://www.ncbi.nlm.nih.gov/pubmed/123456" target="_blank">123456</a>'
		];

		// OLID

		$provider[] = [
			'_sci_olid',
			'foo',
			'',
			''
		];

		$provider[] = [
			'_sci_olid',
			'OL123456M',
			'<span class="plainlinks">[https://openlibrary.org/books/OL123456M OL123456M]</span>',
			'<a href="https://openlibrary.org/books/OL123456M" target="_blank">OL123456M</a>'
		];

		return $provider;
	}

	public function outputFormattedTextProvider() {

		// DOI

		$provider[] = [
			'_sci_doi',
			'10.1000/123456',
			'-',
			'10.1000/123456',
			'10.1000/123456'
		];

		$provider[] = [
			'_sci_doi',
			'http://dx.doi.org/10.1000/123456',
			'-',
			'10.1000/123456',
			'10.1000/123456'
		];

		return$provider;
	}

}
