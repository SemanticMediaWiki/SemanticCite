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
	private $httpRequestProviderFactory;

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

		$this->httpRequestProviderFactory = $this->getMockBuilder( '\SCI\Metadata\HttpRequestProviderFactory' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$instance = new PageBuilder(
			$this->htmlFormRenderer,
			$this->hmlColumnListRenderer,
			$this->citationResourceMatchFinder,
			$this->httpRequestProviderFactory
		);

		$this->assertInstanceOf(
			'\SCI\Metadata\Search\PageBuilder',
			$instance
		);
	}

	public function testGetRawResponse() {

		$responseContentParser = $this->getMockBuilder( '\SCI\Metadata\ResponseContentParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseContentParser->expects( $this->once() )
			->method( 'getRawResponse' )
			->with( $this->identicalTo( 42 ) );

		$this->httpRequestProviderFactory->expects( $this->once() )
			->method( 'newResponseContentParserForType' )
			->with( $this->stringContains( 'foo') )
			->will( $this->returnValue( $responseContentParser ) );

		$instance = new PageBuilder(
			$this->htmlFormRenderer,
			$this->hmlColumnListRenderer,
			$this->citationResourceMatchFinder,
			$this->httpRequestProviderFactory
		);

		$instance->getRawResponseFor( 'foo', 42 );
	}

	public function testGetHtml() {

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$responseContentParser = $this->getMockBuilder( '\SCI\Metadata\ResponseContentParser' )
			->disableOriginalConstructor()
			->setMethods( array( 'getFilteredMetadataRecord' ) )
			->getMockForAbstractClass();

		$responseContentParser->expects( $this->once() )
			->method( 'doParseFor' )
			->with( $this->identicalTo( 42 ) );

		$responseContentParser->expects( $this->atLeastOnce() )
			->method( 'getFilteredMetadataRecord' )
			->will( $this->returnValue( $filteredMetadataRecord ) );

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

		$this->httpRequestProviderFactory->expects( $this->once() )
			->method( 'newResponseContentParserForType' )
			->with( $this->stringContains( 'foo') )
			->will( $this->returnValue( $responseContentParser ) );

		$instance =	new PageBuilder(
			$htmlFormRenderer,
			$this->hmlColumnListRenderer,
			$this->citationResourceMatchFinder,
			$this->httpRequestProviderFactory
		);

		$instance->getHtmlFor( 'foo', 42 );
	}

}
