<?php

namespace SCI\Tests\DataValues;

use SCI\DataValues\DoiValue;

/**
 * @covers \SCI\DataValues\DoiValue
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class DoiValueTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\DataValues\DoiValue',
			new DoiValue()
		);
	}

	/**
	 * @dataProvider doiValueProvider
	 */
	public function testSetUserValue( $value, $expected ) {

		$instance = new DoiValue();
		$instance->setUserValue( $value );

		$this->assertEquals(
			$expected,
			$instance->isValid()
		);
	}

	/**
	 * @dataProvider doiNonLinkedTextProvider
	 */
	public function testNonLinkedText( $value, $expectedWikiText, $expectedHtmlText ) {

		$instance = new DoiValue();
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
	public function testLinkedText( $value, $expectedWikiText, $expectedHtmlText ) {

		$instance = new DoiValue();
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

	public function doiValueProvider() {

		$provider[] = array(
			'foo',
			false,
		);

		$provider[] = array(
			'10.1000.123456',
			false
		);

		$provider[] = array(
			'10.1000/123456',
			true
		);

		$provider[] = array(
			'10.1016.12.31/nature.S0735-1097(98)2000/12/31/34:7-7',
			true
		);

		$provider[] = array(
			'10.1007/978-3-642-28108-2_19',
			true
		);

		$provider[] = array(
			'10.1007.10/978-3-642-28108-2_19',
			true
		);

		$provider[] = array(
			'10.1016/S0735-1097(98)00347-7',
			true
		);

		$provider[] = array(
			'10.1579/0044-7447(2006)35\[89:RDUICP\]2.0.CO;2',
			true
		);

		$provider[] = array(
			'10.1038/ejcn.2010.73',
			true
		);

		$provider[] = array(
			'http://dx.doi.org/10.1074/jbc.M114.559054',
			true
		);

		$provider[] = array(
			'https://doi.org/10.1074/jbc.M114.559054',
			true
		);

		return $provider;
	}

	public function doiNonLinkedTextProvider() {

		$provider[] = array(
			'foo',
			'',
			''
		);

		$provider[] = array(
			'10.1000/123456',
			'10.1000/123456',
			'10.1000/123456'
		);

		$provider[] = array(
			'http://dx.doi.org/10.1000/123456',
			'10.1000/123456',
			'10.1000/123456'
		);

		return $provider;
	}

	public function doiLinkedTextProvider() {

		$provider[] = array(
			'foo',
			'',
			''
		);

		$provider[] = array(
			'10.1000/123456',
			'<span>[https://doi.org/10.1000%2F123456 10.1000/123456]</span>',
			'<a href="https://doi.org/10.1000/123456" target="_blank">10.1000/123456</a>'
		);

		$provider[] = array(
			'http://dx.doi.org/10.1000/123456',
			'<span>[https://doi.org/10.1000%2F123456 10.1000/123456]</span>',
			'<a href="https://doi.org/10.1000/123456" target="_blank">10.1000/123456</a>'
		);

		return $provider;
	}

}
