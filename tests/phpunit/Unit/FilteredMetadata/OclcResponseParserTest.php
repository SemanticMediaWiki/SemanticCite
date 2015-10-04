<?php

namespace SCI\Tests\FilteredMetadata;

use SCI\FilteredMetadata\OclcResponseParser;

/**
 * @covers \SCI\FilteredMetadata\OclcResponseParser
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

		$oclcFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Oclc\OclcFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$oclcFilteredHttpResponseParser->expects( $this->any() )
			->method( 'getFilteredRecord' )
			->will( $this->returnValue( $record ) );

		$oclcFilteredHttpResponseParser->expects( $expects )
			->method( 'doFilterResponseFor' );

		$instance = new OclcResponseParser( $oclcFilteredHttpResponseParser );
		$instance->doFilterResponseFor( $id );
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
