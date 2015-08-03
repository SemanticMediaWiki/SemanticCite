<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\OLResponseParser;

/**
 * @covers \SCI\Metadata\OLResponseParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class OLResponseParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$olFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\OpenLibrary\OLFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\Onoi\Remi\ResponseParser',
			new OLResponseParser( $olFilteredHttpResponseParser )
		);
	}

	public function testInterfaceMethods() {

		$olFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\OpenLibrary\OLFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new OLResponseParser( $olFilteredHttpResponseParser );

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

		$olFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\OpenLibrary\OLFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$olFilteredHttpResponseParser->expects( $this->any() )
			->method( 'getRecord' )
			->will( $this->returnValue( $record ) );

		$olFilteredHttpResponseParser->expects( $expects )
			->method( 'doParseFor' );

		$instance = new OLResponseParser( $olFilteredHttpResponseParser );
		$instance->doParseFor( $id );
	}

	public function idProvider() {

		$provider[] = array(
			'abc',
			 $this->once()
		);

		$provider[] = array(
			'OL54846467',
			 $this->once()
		);

		return $provider;
	}

}
