<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\OclcResponseParser;

/**
 * @covers \SCI\Metadata\OclcResponseParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class OclcResponseParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$oclcNumFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Oclc\OclcFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\Onoi\Remi\ResponseParser',
			new OclcResponseParser( $oclcNumFilteredHttpResponseParser )
		);
	}

	public function testInterfaceMethods() {

		$oclcFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Oclc\OclcFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new OclcResponseParser( $oclcFilteredHttpResponseParser );

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

		$oclcFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Oclc\OclcFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$oclcFilteredHttpResponseParser->expects( $this->any() )
			->method( 'getRecord' )
			->will( $this->returnValue( $record ) );

		$oclcFilteredHttpResponseParser->expects( $expects )
			->method( 'doParseFor' );

		$instance = new OclcResponseParser( $oclcFilteredHttpResponseParser );
		$instance->doParseFor( $id );
	}

	public function idProvider() {

		$provider[] = array(
			'abc',
			 $this->never()
		);

		$provider[] = array(
			'OCLC54846467',
			 $this->once()
		);

		return $provider;
	}

}
