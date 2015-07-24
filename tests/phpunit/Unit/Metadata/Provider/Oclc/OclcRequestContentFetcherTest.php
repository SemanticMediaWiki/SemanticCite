<?php

namespace SCI\Tests\Metadata\Provider\Oclc;

use SCI\Metadata\Provider\Oclc\OclcRequestContentFetcher;

/**
 * @covers \SCI\Metadata\Provider\Oclc\OclcRequestContentFetcher
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class OclcRequestContentFetcherTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\Oclc\OclcRequestContentFetcher',
			new OclcRequestContentFetcher( $httpRequest )
		);
	}

	public function testFetchJsonLd() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->once() )
			->method( 'execute' );

		$instance = new OclcRequestContentFetcher( $httpRequest );
		$instance->fetchJsonLdFor( 42 );
	}

}
