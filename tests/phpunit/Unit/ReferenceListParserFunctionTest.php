<?php

namespace SCI\Tests;

use SCI\ReferenceListParserFunction;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;

/**
 * @covers \SCI\ReferenceListParserFunction
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since   1.0
 *
 * @author mwjames
 */
class ReferenceListParserFunctionTest extends \PHPUnit\Framework\TestCase {

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
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->any() )
			->method( 'getSubject' )
			->willReturn( new WikiPage( 'Foo', NS_MAIN ) );

		$parserData->expects( $this->any() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'toArray' )
			->willReturn( $parameters );

		$instance = new ReferenceListParserFunction( $parserData );

		$this->assertStringContainsString(
			$expected,
			$instance->doProcess( $parserParameterProcessor )
		);
	}

	public function testDoProcessForReferenceParameter() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->any() )
			->method( 'getSubject' )
			->willReturn( new WikiPage( 'Foo', NS_MAIN ) );

		$parserData->expects( $this->once() )
			->method( 'copyToParserOutput' );

		$parserData->expects( $this->any() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'toArray' )
			->willReturn( [ 'references' => [ 'Foo', 42 ] ] );

		$instance = new ReferenceListParserFunction( $parserData );
		$instance->doProcess( $parserParameterProcessor );
	}

	public function parametersDataProvider() {
		$provider[] = [
			[],
			'span'
		];

		$provider[] = [
			[ 'toc' => [ true ] ],
			'h2'
		];

		$provider[] = [
			[ 'references' => [ 'Foo', 42 ] ],
			'data-references="[&quot;Foo&quot;,42]"'
		];

		return $provider;
	}

}
