<?php

namespace SCI\Tests\Metadata\Search;

use SCI\Metadata\Search\ResponseParserOutputRenderer;

/**
 * @covers \SCI\Metadata\Search\ResponseParserOutputRenderer
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ResponseParserOutputRendererTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$responseParser = $this->getMockBuilder( '\Onoi\Remi\ResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SCI\Metadata\Search\ResponseParserOutputRenderer',
			new ResponseParserOutputRenderer( $responseParser )
		);
	}

	public function testGetRawResponse() {

		$responseParser = $this->getMockBuilder( '\Onoi\Remi\ResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseParser->expects( $this->once() )
			->method( 'getRawResponse' )
			->with( $this->identicalTo( 42 ) );

		$instance = new ResponseParserOutputRenderer(
			$responseParser
		);

		$instance->getRawResponse( 42 );
	}

	public function testRenderText() {

		$bibliographicFilteredRecord = $this->getMockBuilder( '\SCI\Metadata\BibliographicFilteredRecord' )
			->disableOriginalConstructor()
			->getMock();

		$responseParser = $this->getMockBuilder( '\Onoi\Remi\ResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseParser->expects( $this->once() )
			->method( 'doFilterResponseFor' )
			->with( $this->identicalTo( 42 ) );

		$responseParser->expects( $this->atLeastOnce() )
			->method( 'getMessages' )
			->will( $this->returnValue( array() ) );

		$responseParser->expects( $this->atLeastOnce() )
			->method( 'getFilteredRecord' )
			->will( $this->returnValue( $bibliographicFilteredRecord ) );

		$instance = new ResponseParserOutputRenderer(
			$responseParser
		);

		$instance->renderTextFor( 42 );
	}

}
