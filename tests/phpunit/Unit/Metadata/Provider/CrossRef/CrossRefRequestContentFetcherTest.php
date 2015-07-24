<?php

namespace SCI\Tests\Metadata\Provider\CrossRef;

use SCI\Metadata\Provider\CrossRef\CrossRefRequestContentFetcher;

/**
 * @covers \SCI\Metadata\Provider\CrossRef\CrossRefRequestContentFetcher
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class CrossRefRequestContentFetcherTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\CrossRef\CrossRefRequestContentFetcher',
			new CrossRefRequestContentFetcher( $httpRequest )
		);
	}

	public function testFetchBibtex() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->once() )
			->method( 'execute' );

		$instance = new CrossRefRequestContentFetcher( $httpRequest );
		$instance->fetchBibtexFor( 42 );
	}

	public function testFetchCiteprocJson() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->once() )
			->method( 'execute' );

		$instance = new CrossRefRequestContentFetcher( $httpRequest );
		$instance->fetchCiteprocJsonFor( 42 );
	}

}
