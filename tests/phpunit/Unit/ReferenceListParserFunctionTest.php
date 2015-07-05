<?php

namespace SCI\Tests;

use SCI\ReferenceListParserFunction;

/**
 * @covers \SCI\ReferenceListParserFunction
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class ReferenceListParserFunctionTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\ReferenceListParserFunction',
			new ReferenceListParserFunction()
		);
	}

	/**
	 * @dataProvider parametersDataProvider
	 */
	public function testDoProcessForEmptyParameter( $parameters, $expected ) {

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'toArray' )
			->will( $this->returnValue( $parameters ) );

		$instance = new ReferenceListParserFunction();

		$this->assertContains(
			$expected,
			$instance->doProcess( $parserParameterProcessor )
		);
	}

	public function parametersDataProvider() {

		$provider[] = array(
			array(),
			'span'
		);

		$provider[] = array(
			array( 'toc' => array( true ) ),
			'h2'
		);

		$provider[] = array(
			array( 'references' => array( 'Foo', 42 ) ),
			'data-references="[&quot;Foo&quot;,42]"'
		);

		return $provider;
	}

}
