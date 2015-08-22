<?php

namespace SCI\Tests\Metadata\Search;

use SCI\Metadata\Search\PageBuilder;

/**
 * @covers \SCI\Metadata\Search\PageBuilder
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PageBuilderTest extends \PHPUnit_Framework_TestCase {

	private $htmlFormRenderer;
	private $hmlColumnListRenderer;
	private $citationResourceMatchFinder;
	private $httpResponseParserFactory;

	protected function setUp() {

		$this->htmlFormRenderer = $this->getMockBuilder( '\SMW\MediaWiki\Renderer\HtmlFormRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$this->hmlColumnListRenderer = $this->getMockBuilder( '\SMW\MediaWiki\Renderer\HtmlColumnListRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$this->citationResourceMatchFinder = $this->getMockBuilder( '\SCI\CitationResourceMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpResponseParserFactory = $this->getMockBuilder( '\SCI\Metadata\HttpResponseParserFactory' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$instance = new PageBuilder(
			$this->htmlFormRenderer,
			$this->hmlColumnListRenderer,
			$this->citationResourceMatchFinder,
			$this->httpResponseParserFactory
		);

		$this->assertInstanceOf(
			'\SCI\Metadata\Search\PageBuilder',
			$instance
		);
	}

	public function testGetRawResponse() {

		$responseParser = $this->getMockBuilder( '\Onoi\Remi\ResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseParser->expects( $this->once() )
			->method( 'getRawResponse' )
			->with( $this->identicalTo( 42 ) );

		$this->httpResponseParserFactory->expects( $this->once() )
			->method( 'newResponseParserForType' )
			->with( $this->stringContains( 'foo') )
			->will( $this->returnValue( $responseParser ) );

		$instance = new PageBuilder(
			$this->htmlFormRenderer,
			$this->hmlColumnListRenderer,
			$this->citationResourceMatchFinder,
			$this->httpResponseParserFactory
		);

		$instance->getRawResponseFor( 'foo', 42 );
	}

	public function testGetHtml() {

		$bibliographicFilteredRecord = $this->getMockBuilder( '\SCI\Metadata\BibliographicFilteredRecord' )
			->disableOriginalConstructor()
			->getMock();

		$responseParser = $this->getMockBuilder( '\Onoi\Remi\ResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseParser->expects( $this->once() )
			->method( 'doParseFor' )
			->with( $this->identicalTo( 42 ) );

		$responseParser->expects( $this->any() )
			->method( 'getMessages' )
			->will( $this->returnValue( array() ) );

		$responseParser->expects( $this->atLeastOnce() )
			->method( 'getRecord' )
			->will( $this->returnValue( $bibliographicFilteredRecord ) );

		$message = $this->getMockBuilder( '\Message' )
			->disableOriginalConstructor()
			->getMock();

		$messageBuilder = $this->getMockBuilder( '\SMW\MediaWiki\MessageBuilder' )
			->disableOriginalConstructor()
			->getMock();

		$messageBuilder->expects( $this->any() )
			->method( 'getMessage' )
			->will( $this->returnValue( $message ) );

		$htmlFormRenderer = $this->getMockBuilder( '\SMW\MediaWiki\Renderer\HtmlFormRenderer' )
			->disableOriginalConstructor()
			->setMethods( array( 'getMessageBuilder', 'getForm' ) )
			->getMock();

		$htmlFormRenderer->expects( $this->atLeastOnce() )
			->method( 'getMessageBuilder' )
			->will( $this->returnValue( $messageBuilder ) );

		$this->citationResourceMatchFinder->expects( $this->atLeastOnce() )
			->method( 'findMatchForUidTypeOf' )
			->will( $this->returnValue( array() ) );

		$this->httpResponseParserFactory->expects( $this->once() )
			->method( 'newResponseParserForType' )
			->with( $this->stringContains( 'foo') )
			->will( $this->returnValue( $responseParser ) );

		$instance =	new PageBuilder(
			$htmlFormRenderer,
			$this->hmlColumnListRenderer,
			$this->citationResourceMatchFinder,
			$this->httpResponseParserFactory
		);

		$instance->getHtmlFor( 'foo', 42 );
	}

}
