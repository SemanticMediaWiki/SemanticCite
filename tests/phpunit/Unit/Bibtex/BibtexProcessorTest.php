<?php

namespace SCI\Tests\Bibtex;

use SCI\Bibtex\BibtexProcessor;

/**
 * @covers \SCI\Bibtex\BibtexProcessor
 * @group semantic-citation
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

		$this->assertInstanceOf(
			'\SCI\Bibtex\BibtexProcessor',
			new BibtexProcessor( $bibtexParser )
		);
	}

	public function testDoProcess() {

		$bibtexParser = $this->getMockBuilder( '\SCI\Bibtex\BibtexParser' )
			->disableOriginalConstructor()
			->getMock();

		$bibtexParser->expects( $this->once() )
			->method( 'parse' )
			->will( $this->returnValue( array( 'Foo' => 'Bar' ) ) );

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

		$instance = new BibtexProcessor( $bibtexParser );

		$instance->doProcess(
			$parserParameterProcessor
		);
	}

}
