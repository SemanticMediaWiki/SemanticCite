<?php

namespace SCI\Tests\Specials\CitableMetadata;

use SCI\Specials\CitableMetadata\HtmlResponseParserRenderer;

/**
 * @covers \SCI\Specials\CitableMetadata\HtmlResponseParserRenderer
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class HtmlResponseParserRendererTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$responseParser = $this->getMockBuilder( '\SCI\FilteredMetadata\ResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SCI\Specials\CitableMetadata\HtmlResponseParserRenderer',
			new HtmlResponseParserRenderer( $responseParser )
		);
	}

	public function testGetRawResponse() {
		$responseParser = $this->getMockBuilder( '\SCI\FilteredMetadata\ResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseParser->expects( $this->once() )
			->method( 'getRawResponse' )
			->with( $this->identicalTo( 42 ) );

		$instance = new HtmlResponseParserRenderer(
			$responseParser
		);

		$instance->getRawResponse( 42 );
	}

	public function testRenderText() {
		$bibliographicFilteredRecord = $this->getMockBuilder( '\SCI\FilteredMetadata\BibliographicFilteredRecord' )
			->disableOriginalConstructor()
			->getMock();

		$responseParser = $this->getMockBuilder( '\SCI\FilteredMetadata\ResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseParser->expects( $this->once() )
			->method( 'doFilterResponseFor' )
			->with( $this->identicalTo( 42 ) );

		$responseParser->expects( $this->atLeastOnce() )
			->method( 'getMessages' )
			->willReturn( [] );

		$responseParser->expects( $this->atLeastOnce() )
			->method( 'getFilteredRecord' )
			->willReturn( $bibliographicFilteredRecord );

		$instance = new HtmlResponseParserRenderer(
			$responseParser
		);

		$instance->renderTextFor( 42 );
	}

}
