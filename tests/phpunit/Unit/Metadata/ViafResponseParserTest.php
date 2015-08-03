<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\ViafResponseParser;

/**
 * @covers \SCI\Metadata\ViafResponseParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class ViafResponseParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$viafFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Viaf\ViafFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\Onoi\Remi\ResponseParser',
			new ViafResponseParser( $viafFilteredHttpResponseParser )
		);
	}

	public function testInterfaceMethods() {

		$viafFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Viaf\ViafFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ViafResponseParser( $viafFilteredHttpResponseParser );

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

		$viafFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Viaf\ViafFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$viafFilteredHttpResponseParser->expects( $this->any() )
			->method( 'getRecord' )
			->will( $this->returnValue( $record ) );

		$viafFilteredHttpResponseParser->expects( $expects )
			->method( 'doParseFor' );

		$instance = new ViafResponseParser( $viafFilteredHttpResponseParser );
		$instance->doParseFor( $id );
	}

	public function idProvider() {

		$provider[] = array(
			'abc',
			 $this->never()
		);

		$provider[] = array(
			'VIAF54846467',
			 $this->once()
		);

		return $provider;
	}

}
