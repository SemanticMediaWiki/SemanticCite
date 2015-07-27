<?php

namespace SCI\Tests\Metadata\Search;

use SCI\Metadata\Search\ResponseContentOutputRenderer;

/**
 * @covers \SCI\Metadata\Search\ResponseContentOutputRenderer
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ResponseContentOutputRendererTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$responseContentParser = $this->getMockBuilder( '\SCI\Metadata\ResponseContentParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SCI\Metadata\Search\ResponseContentOutputRenderer',
			new ResponseContentOutputRenderer( $responseContentParser )
		);
	}

	public function testGetRawResponse() {

		$responseContentParser = $this->getMockBuilder( '\SCI\Metadata\ResponseContentParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseContentParser->expects( $this->once() )
			->method( 'getRawResponse' )
			->with( $this->identicalTo( 42 ) );

		$instance = new ResponseContentOutputRenderer(
			$responseContentParser
		);

		$instance->getRawResponse( 42 );
	}

	public function testRenderText() {

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$responseContentParser = $this->getMockBuilder( '\SCI\Metadata\ResponseContentParser' )
			->disableOriginalConstructor()
			->setMethods( array( 'getFilteredMetadataRecord', 'getMessages' ) )
			->getMockForAbstractClass();

		$responseContentParser->expects( $this->once() )
			->method( 'doParseFor' )
			->with( $this->identicalTo( 42 ) );

		$responseContentParser->expects( $this->atLeastOnce() )
			->method( 'getMessages' )
			->will( $this->returnValue( array( 'foo' ) ) );

		$responseContentParser->expects( $this->atLeastOnce() )
			->method( 'getFilteredMetadataRecord' )
			->will( $this->returnValue( $filteredMetadataRecord ) );

		$instance = new ResponseContentOutputRenderer(
			$responseContentParser
		);

		$instance->renderTextFor( 42 );
	}

}
