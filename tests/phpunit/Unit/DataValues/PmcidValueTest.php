<?php

namespace SCI\Tests\DataValues;

use SCI\DataValues\PmcidValue;
use SMW\DataTypeRegistry;

/**
 * @covers \SCI\DataValues\PmcidValue
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PmcidValueTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\DataValues\PmcidValue',
			new PmcidValue()
		);
	}

	/**
	 * @dataProvider pmcidValueProvider
	 */
	public function testSetUserValue( $value, $expected ) {

		$instance = new PmcidValue();
		$instance->setUserValue( $value );

		$this->assertEquals(
			$expected,
			$instance->isValid()
		);
	}

	/**
	 * @dataProvider pmcidNonLinkedTextProvider
	 */
	public function testNonLinkedText( $value, $expectedWikiText, $expectedHtmlText ) {

		$instance = new PmcidValue();
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
	 * @dataProvider pmcidLinkedTextProvider
	 */
	public function testLinkedText( $value, $expectedWikiText, $expectedHtmlText ) {

		$instance = new PmcidValue();
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

	public function pmcidValueProvider() {

		$provider[] = array(
			'foo',
			false,
		);

		$provider[] = array(
			'PM123456',
			false
		);

		$provider[] = array(
			'PMCID123456',
			false
		);

		$provider[] = array(
			'PMC123456',
			true
		);

		return $provider;
	}

	public function pmcidNonLinkedTextProvider() {

		$provider[] = array(
			'foo',
			'',
			''
		);

		$provider[] = array(
			'PMC123456',
			'PMC123456',
			'PMC123456'
		);

		return $provider;
	}

	public function pmcidLinkedTextProvider() {

		$provider[] = array(
			'foo',
			'',
			''
		);

		$provider[] = array(
			'PMC123456',
			'<span>[https://www.ncbi.nlm.nih.gov/pmc/PMC123456 PMC123456]</span>',
			'<a href="https://www.ncbi.nlm.nih.gov/pmc/PMC123456" target="_blank">PMC123456</a>'
		);

		return $provider;
	}

}
