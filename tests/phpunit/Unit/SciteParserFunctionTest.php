<?php

namespace SCI\Tests;

use SCI\SciteParserFunction;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;

/**
 * @covers \SCI\SciteParserFunction
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since   1.0
 *
 * @author mwjames
 */
class SciteParserFunctionTest extends \PHPUnit\Framework\TestCase {

	private $parserData;
	private $namespaceExaminer;
	private $citationTextTemplateRenderer;
	private $mediaWikiNsContentMapper;
	private $bibtexProcessor;

	protected function setUp(): void {
		$this->parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$this->parserData->expects( $this->any() )
			->method( 'getSubject' )
			->willReturn( WikiPage::newFromText( __METHOD__ ) );

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
			->willReturn( \MediaWiki\Title\Title::newFromText( __METHOD__ ) );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->willReturn( false );

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

		$this->assertIsString(
			$instance->doProcess( $parserParameterProcessor )
		);
	}

	public function testErrorForEnabledNamespaceButMissingReference() {
		$this->parserData->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( \MediaWiki\Title\Title::newFromText( __METHOD__ ) );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->willReturn( true );

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

		$this->assertIsString(
			$instance->doProcess( $parserParameterProcessor )
		);
	}

	public function testErrorForEnabledNamespaceButMissingType() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$this->parserData->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( \MediaWiki\Title\Title::newFromText( __METHOD__ ) );

		$this->parserData->expects( $this->any() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->willReturn( true );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->any() )
			->method( 'hasParameter' )
			->willReturnCallback( static function ( $key ) {
				return $key === 'reference' ? true : false;
			} );

		$parserParameterProcessor->expects( $this->any() )
			->method( 'getParameterValuesByKey' )
			->willReturnCallback( static function ( $key ) {
				return $key === 'reference' ? [ 'Foo' ] : null;
			} );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'toArray' )
			->willReturn( [ '_ERRP' => [ 'Foo' ] ] );

		$instance = new SciteParserFunction(
			$this->parserData,
			$this->namespaceExaminer,
			$this->citationTextTemplateRenderer,
			$this->mediaWikiNsContentMapper,
			$this->bibtexProcessor
		);

		$instance->setStrictParserValidationState( true );

		$this->assertIsString(
			$instance->doProcess( $parserParameterProcessor )
		);
	}

}
