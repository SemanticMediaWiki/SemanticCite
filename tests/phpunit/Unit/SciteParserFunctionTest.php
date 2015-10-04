<?php

namespace SCI\Tests;

use SCI\SciteParserFunction;
use SMW\DIWikiPage;

/**
 * @covers \SCI\SciteParserFunction
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class SciteParserFunctionTest extends \PHPUnit_Framework_TestCase {

	private $parserData;
	private $namespaceExaminer;
	private $citationTextTemplateRenderer;
	private $mediaWikiNsContentMapper;
	private $bibtexProcessor;

	protected function setUp() {

		$this->parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$this->parserData->expects( $this->any() )
			->method( 'getSubject' )
			->will( $this->returnValue( DIWikiPage::newFromText( __METHOD__ ) ) );

		$this->namespaceExaminer = $this->getMockBuilder( '\SMW\NamespaceExaminer' )
			->disableOriginalConstructor()
			->getMock();

		$this->citationTextTemplateRenderer = $this->getMockBuilder( '\SCI\CitationTextTemplateRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$this->mediaWikiNsContentMapper = $this->getMockBuilder( '\SCI\MediaWikiNsContentMapper' )
			->disableOriginalConstructor()
			->getMock();

		$this->bibtexProcessor = $this->getMockBuilder( '\SCI\Bibtex\BibtexProcessor' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$instance = new SciteParserFunction(
			$this->parserData,
			$this->namespaceExaminer,
			$this->citationTextTemplateRenderer,
			$this->mediaWikiNsContentMapper,
			$this->bibtexProcessor
		);

		$this->assertInstanceOf(
			'\SCI\SciteParserFunction',
			$instance
		);
	}

	public function testErrorForNotEnabledNamespace() {

		$this->parserData->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( __METHOD__ ) ) );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->will( $this->returnValue( false ) );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SciteParserFunction(
			$this->parserData,
			$this->namespaceExaminer,
			$this->citationTextTemplateRenderer,
			$this->mediaWikiNsContentMapper,
			$this->bibtexProcessor
		);

		$this->assertInternalType(
			'string',
			$instance->doProcess( $parserParameterProcessor )
		);
	}

	public function testErrorForEnabledNamespaceButMissingReference() {

		$this->parserData->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( __METHOD__ ) ) );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->will( $this->returnValue( true ) );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SciteParserFunction(
			$this->parserData,
			$this->namespaceExaminer,
			$this->citationTextTemplateRenderer,
			$this->mediaWikiNsContentMapper,
			$this->bibtexProcessor
		);

		$this->assertInternalType(
			'string',
			$instance->doProcess( $parserParameterProcessor )
		);
	}

	public function testErrorForEnabledNamespaceButMissingType() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$this->parserData->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( __METHOD__ ) ) );

		$this->parserData->expects( $this->any() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->will( $this->returnValue( true ) );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->any() )
			->method( 'hasParameter' )
			->will( $this->returnCallback( function( $key ) {
				return $key === 'reference' ? true : false; } ) );

		$parserParameterProcessor->expects( $this->any() )
			->method( 'getParameterValuesFor' )
			->will( $this->returnCallback( function( $key ) {
				return $key === 'reference' ? array( 'Foo' ) : null; } ) );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'toArray' )
			->will( $this->returnValue( array( '_ERRP' => array( 'Foo' ) ) ) );

		$instance = new SciteParserFunction(
			$this->parserData,
			$this->namespaceExaminer,
			$this->citationTextTemplateRenderer,
			$this->mediaWikiNsContentMapper,
			$this->bibtexProcessor
		);

		$instance->setStrictParserValidationState( true );

		$this->assertInternalType(
			'string',
			$instance->doProcess( $parserParameterProcessor )
		);
	}

}
