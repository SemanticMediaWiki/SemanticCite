<?php

namespace SCI\Tests\Metadata\Provider\Viaf;

use SCI\Metadata\Provider\Viaf\ViafRequestContentFetcher;

/**
 * @covers \SCI\Metadata\Provider\Viaf\ViafRequestContentFetcher
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class ViafRequestContentFetcherTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\Viaf\ViafRequestContentFetcher',
			new ViafRequestContentFetcher( $httpRequest )
		);
	}

	public function testFetchXml() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->once() )
			->method( 'execute' );

		$instance = new ViafRequestContentFetcher( $httpRequest );
		$instance->fetchXmlFor( 42 );
	}

}
