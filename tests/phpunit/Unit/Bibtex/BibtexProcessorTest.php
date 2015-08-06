<?php

namespace SCI\Tests\Bibtex;

use SCI\Bibtex\BibtexProcessor;

/**
 * @covers \SCI\Bibtex\BibtexProcessor
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class BibtexProcessorTest extends \PHPUnit_Framework_TestCase {

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
			->will( $this->returnValue( array( 'Foo' => 'Bar' ) ) );

		$bibtexAuthorListParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexAuthorListParser' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'getParameterValuesFor' )
			->with(	$this->equalTo( 'bibtex' ) )
			->will( $this->returnValue( array() ) );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'addParameter' )
			->with(
				$this->equalTo( 'Foo' ),
				$this->equalTo( 'Bar' ) );

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
			->will( $this->returnValue( array( 'author' => 'Foo' ) ) );

		$bibtexAuthorListParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexAuthorListParser' )
			->disableOriginalConstructor()
			->getMock();

		$bibtexAuthorListParser->expects( $this->once() )
			->method( 'parse' )
			->will( $this->returnValue( array( 'Foo' ) ) );

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->once() )
			->method( 'getParameterValuesFor' )
			->with(	$this->equalTo( 'bibtex' ) )
			->will( $this->returnValue( array( ) ) );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'setParameter' )
			->with(
				$this->equalTo( 'author' ),
				$this->equalTo( array( 'Foo' ) ) );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'addParameter' )
			->with(
				$this->equalTo( 'bibtex-author' ),
				$this->equalTo( 'Foo' ) );

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
			->will( $this->returnValue( array( $parameter => 'Foo' ) ) );

		$bibtexAuthorListParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexAuthorListParser' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor = $this->getMockBuilder( '\SMW\ParserParameterProcessor' )
			->disableOriginalConstructor()
			->getMock();

		$parserParameterProcessor->expects( $this->any() )
			->method( 'getFirstParameter' )
			->will( $this->returnValue( '' ) );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'hasParameter' )
			->with(	$this->equalTo( $parameter ) )
			->will( $this->returnValue( true ) );

		$parserParameterProcessor->expects( $this->once() )
			->method( 'getParameterValuesFor' )
			->with(	$this->equalTo( 'bibtex' ) )
			->will( $this->returnValue( array( ) ) );

		$parserParameterProcessor->expects( $this->never() )
			->method( 'addParameter' );

		$instance = new BibtexProcessor( $bibtexParser, $bibtexAuthorListParser );

		$instance->doProcess(
			$parserParameterProcessor
		);
	}

	public function doNotProcessParameterProvider() {

		$provider[] = array(
			'reference'
		);

		$provider[] = array(
			'type'
		);

		return $provider;
	}

}
