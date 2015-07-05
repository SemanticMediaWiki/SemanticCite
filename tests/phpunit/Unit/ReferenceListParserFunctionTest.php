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

	public function testDoProcessForEmptyParameter() {

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'toArray' )
			->will( $this->returnValue( array() ) );

		$instance = new ReferenceListParserFunction();

		$this->assertInternalType(
			'string',
			$instance->doProcess( $parserParameterProcessor )
		);
	}

}
