<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\HttpRequestContentFetcher;

/**
 * @covers \SCI\Metadata\HttpRequestContentFetcher
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class HttpRequestContentFetcherTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\HttpRequestContentFetcher',
			new HttpRequestContentFetcher( $httpRequest )
		);
	}

	public function testMethodAcces() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\CachedCurlRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->once() )
			->method( 'getLastError' );

		$httpRequest->expects( $this->once() )
			->method( 'isCached' );

		$instance = new HttpRequestContentFetcher( $httpRequest );

		$instance->getError();
		$instance->isCached();
	}

}
