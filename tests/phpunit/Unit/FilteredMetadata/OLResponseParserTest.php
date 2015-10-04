<?php

namespace SCI\Tests\FilteredMetadata;

use SCI\FilteredMetadata\OLResponseParser;

/**
 * @covers \SCI\FilteredMetadata\OLResponseParser
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

		$olFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\OpenLibrary\OLFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$olFilteredHttpResponseParser->expects( $this->any() )
			->method( 'getFilteredRecord' )
			->will( $this->returnValue( $record ) );

		$olFilteredHttpResponseParser->expects( $expects )
			->method( 'doFilterResponseFor' );

		$instance = new OLResponseParser( $olFilteredHttpResponseParser );
		$instance->doFilterResponseFor( $id );
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
