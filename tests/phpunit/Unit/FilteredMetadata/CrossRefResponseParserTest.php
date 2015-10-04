<?php

namespace SCI\Tests\FilteredMetadata;

use SCI\FilteredMetadata\CrossRefResponseParser;

/**
 * @covers \SCI\FilteredMetadata\CrossRefResponseParser
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
			$instance->usesCache()
		);

		$this->assertNull(
			$instance->getMessages()
		);

		$this->assertNull(
			$instance->getFilteredRecord()
		);

		$this->assertNull(
			$instance->getRawResponse( 42 )
		);
	}

	/**
	 * @dataProvider idProvider
	 */
	public function testDoParseForId( $id, $expects ) {

		$record = $this->getMockBuilder( '\SCI\FilteredMetadata\BibliographicFilteredRecord' )
			->disableOriginalConstructor()
			->getMock();

		$crossRefFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\CrossRef\CrossRefFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$crossRefFilteredHttpResponseParser->expects( $this->any() )
			->method( 'getFilteredRecord' )
			->will( $this->returnValue( $record ) );

		$crossRefFilteredHttpResponseParser->expects( $expects )
			->method( 'doFilterResponseFor' );

		$instance = new CrossRefResponseParser( $crossRefFilteredHttpResponseParser );
		$instance->doFilterResponseFor( $id );
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
