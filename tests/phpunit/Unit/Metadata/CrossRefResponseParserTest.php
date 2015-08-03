<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\CrossRefResponseParser;

/**
 * @covers \SCI\Metadata\CrossRefResponseParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class CrossRefResponseParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$crossRefFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\CrossRef\CrossRefFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\Onoi\Remi\ResponseParser',
			new CrossRefResponseParser( $crossRefFilteredHttpResponseParser )
		);
	}

	public function testInterfaceMethods() {

		$crossRefFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\CrossRef\CrossRefFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new CrossRefResponseParser( $crossRefFilteredHttpResponseParser );

		$this->assertNull(
			$instance->usedCache()
		);

		$this->assertNull(
			$instance->getMessages()
		);

		$this->assertNull(
			$instance->getRecord()
		);

		$this->assertNull(
			$instance->getRawResponse( 42 )
		);
	}

	/**
	 * @dataProvider idProvider
	 */
	public function testDoParseForId( $id, $expects ) {

		$record = $this->getMockBuilder( '\SCI\Metadata\BibliographicFilteredRecord' )
			->disableOriginalConstructor()
			->getMock();

		$crossRefFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\CrossRef\CrossRefFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$crossRefFilteredHttpResponseParser->expects( $this->any() )
			->method( 'getRecord' )
			->will( $this->returnValue( $record ) );

		$crossRefFilteredHttpResponseParser->expects( $expects )
			->method( 'doParseFor' );

		$instance = new CrossRefResponseParser( $crossRefFilteredHttpResponseParser );
		$instance->doParseFor( $id );
	}

	public function idProvider() {

		$provider[] = array(
			'abc',
			 $this->never()
		);

		$provider[] = array(
			'10.1126/science.1152662',
			 $this->once()
		);

		return $provider;
	}

}
