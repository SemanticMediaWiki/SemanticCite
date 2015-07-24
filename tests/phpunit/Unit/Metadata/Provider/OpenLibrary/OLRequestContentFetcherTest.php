<?php

namespace SCI\Tests\Metadata\Provider\OpenLibrary;

use SCI\Metadata\Provider\OpenLibrary\OLRequestContentFetcher;

/**
 * @covers \SCI\Metadata\Provider\OpenLibrary\OLRequestContentFetcher
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class OLRequestContentFetcherTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\OpenLibrary\OLRequestContentFetcher',
			new OLRequestContentFetcher( $httpRequest )
		);
	}

	public function testFetchDataJson() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->once() )
			->method( 'execute' );

		$instance = new OLRequestContentFetcher( $httpRequest );
		$instance->fetchDataJsonFor( 42 );
	}

}
