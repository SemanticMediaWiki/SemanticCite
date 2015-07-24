<?php

namespace SCI\Tests\Metadata\Provider\Ncbi;

use SCI\Metadata\Provider\Ncbi\NcbiRequestContentFetcher;

/**
 * @covers \SCI\Metadata\Provider\Ncbi\NcbiRequestContentFetcher
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class NcbiRequestContentFetcherTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\Ncbi\NcbiRequestContentFetcher',
			new NcbiRequestContentFetcher( $httpRequest, 'foo' )
		);
	}

	public function testFetchSummary() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->once() )
			->method( 'execute' );

		$instance = new NcbiRequestContentFetcher( $httpRequest, 'foo' );
		$instance->fetchSummaryFor( 42 );

		$this->assertEquals(
			'foo',
			$instance->getType()
		);
	}

	public function testFetchAbstract() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->once() )
			->method( 'execute' );

		$instance = new NcbiRequestContentFetcher( $httpRequest, 'foo' );
		$instance->fetchAbstractFor( 42 );
	}

}
