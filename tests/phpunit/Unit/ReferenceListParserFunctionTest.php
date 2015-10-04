<?php

namespace SCI\Tests;

use SCI\ReferenceListParserFunction;
use SMW\DIWikiPage;

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

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\ReferenceListParserFunction',
			new ReferenceListParserFunction( $parserData )
		);
	}

	/**
	 * @dataProvider parametersDataProvider
	 */
	public function testDoProcessForParameter( $parameters, $expected ) {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->any() )
			->method( 'getSubject' )
			->will( $this->returnValue( new DIWikiPage( 'Foo', NS_MAIN ) ) );

		$parserData->expects( $this->any() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'toArray' )
			->will( $this->returnValue( $parameters ) );

		$instance = new ReferenceListParserFunction( $parserData );

		$this->assertContains(
			$expected,
			$instance->doProcess( $parserParameterProcessor )
		);
	}

	public function testDoProcessForReferenceParameter() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->any() )
			->method( 'getSubject' )
			->will( $this->returnValue( new DIWikiPage( 'Foo', NS_MAIN ) ) );

		$parserData->expects( $this->once() )
			->method( 'pushSemanticDataToParserOutput' );

		$parserData->expects( $this->any() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'toArray' )
			->will( $this->returnValue( array( 'references' => array( 'Foo', 42 ) ) ) );

		$instance = new ReferenceListParserFunction( $parserData );
		$instance->doProcess( $parserParameterProcessor );
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
