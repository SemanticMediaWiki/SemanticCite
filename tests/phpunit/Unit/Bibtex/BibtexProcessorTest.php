<?php

namespace SCI\Tests\Bibtex;

use SCI\Bibtex\BibtexProcessor;

/**
 * @covers \SCI\Bibtex\BibtexProcessor
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class BibtexProcessorTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$bibtexParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexParser' )
			->disableOriginalConstructor()
			->getMock();

		$bibtexAuthorListParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexAuthorListParser' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Bibtex\BibtexProcessor',
			new BibtexProcessor( $bibtexParser, $bibtexAuthorListParser )
		);
	}

	public function testDoProcess() {
		$bibtexParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexParser' )
			->disableOriginalConstructor()
			->getMock();

		$bibtexParser->expects( $this->once() )
			->method( 'parse' )
			->willReturn( [ 'Foo' => 'Bar' ] );

		$bibtexAuthorListParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexAuthorListParser' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'getParameterValuesByKey' )
			->with(	'bibtex' )
			->willReturn( [] );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'addParameter' )
			->with(
				'Foo',
				'Bar' );

		$instance = new BibtexProcessor( $bibtexParser, $bibtexAuthorListParser );

		$instance->doProcess(
			$parserParameterProcessor
		);
	}

	public function testProcessAuthors() {
		$bibtexParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexParser' )
			->disableOriginalConstructor()
			->getMock();

		$bibtexParser->expects( $this->once() )
			->method( 'parse' )
			->willReturn( [ 'author' => 'Foo' ] );

		$bibtexAuthorListParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexAuthorListParser' )
			->disableOriginalConstructor()
			->getMock();

		$bibtexAuthorListParser->expects( $this->once() )
			->method( 'parse' )
			->willReturn( [ 'Foo' ] );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'getParameterValuesByKey' )
			->with(	'bibtex' )
			->willReturn( [] );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'setParameter' )
			->with(
				'author',
				[ 'Foo' ] );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'addParameter' )
			->with(
				'bibtex-author',
				'Foo' );

		$instance = new BibtexProcessor( $bibtexParser, $bibtexAuthorListParser );

		$instance->doProcess(
			$parserParameterProcessor
		);
	}

	/**
	 * @dataProvider doNotProcessParameterProvider
	 */
	public function testDoNotProcess( $parameter ) {
		$bibtexParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexParser' )
			->disableOriginalConstructor()
			->getMock();

		$bibtexParser->expects( $this->once() )
			->method( 'parse' )
			->willReturn( [ $parameter => 'Foo' ] );

		$bibtexAuthorListParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexAuthorListParser' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->any() )
			->method( 'getFirstParameter' )
			->willReturn( '' );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'hasParameter' )
			->with(	$parameter )
			->willReturn( true );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'getParameterValuesByKey' )
			->with(	'bibtex' )
			->willReturn( [] );

		$parserParameterProcessor->expects( $this->never() )
			->method( 'addParameter' );

		$instance = new BibtexProcessor( $bibtexParser, $bibtexAuthorListParser );

		$instance->doProcess(
			$parserParameterProcessor
		);
	}

	public function doNotProcessParameterProvider() {
		$provider[] = [
			'reference'
		];

		$provider[] = [
			'type'
		];

		return $provider;
	}

}
