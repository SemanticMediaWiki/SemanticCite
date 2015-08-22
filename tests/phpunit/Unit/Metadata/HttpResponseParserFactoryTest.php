<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\HttpResponseParserFactory;

/**
 * @covers \SCI\Metadata\HttpResponseParserFactory
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class HttpResponseParserFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\HttpResponseParserFactory',
			new HttpResponseParserFactory( $httpRequest )
		);
	}

	/**
	 * @dataProvider typeProvider
	 */
	public function testHttpResponseContentParserForType( $type ) {

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new HttpResponseParserFactory( $httpRequest );

		$this->assertInstanceOf(
			'\Onoi\Remi\ResponseParser',
			$instance->newResponseParserForType( $type )
		);
	}

	public function typeProvider() {

		$provider[] = array(
			'pubmed'
		);

		$provider[] = array(
			'pmc'
		);

		$provider[] = array(
			'doi'
		);

		$provider[] = array(
			'oclc'
		);

		$provider[] = array(
			'viaf'
		);

		$provider[] = array(
			'ol'
		);

		// Unknown = null
		$provider[] = array(
			'foo'
		);

		return $provider;
	}

}
