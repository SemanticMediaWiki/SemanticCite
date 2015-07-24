<?php

namespace SCI\Tests\Metadata\Provider\Viaf;

use SCI\Metadata\Provider\Viaf\ViafResponseContentParser;

/**
 * @covers \SCI\Metadata\Provider\Viaf\ViafResponseContentParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class ViafResponseContentParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$viafRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Viaf\ViafRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\Viaf\ViafResponseContentParser',
			new ViafResponseContentParser( $viafRequestContentFetcher, $filteredMetadataRecord )
		);
	}

	/**
	 * @dataProvider invalidIdProvider
	 */
	public function testDoParseForInvalidViaf( $id ) {

		$viafRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Viaf\ViafRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ViafResponseContentParser(
			$viafRequestContentFetcher,
			$filteredMetadataRecord
		);

		$instance->doParseFor( $id );

		$this->assertFalse(
			$instance->isSuccess()
		);
	}

	public function invalidIdProvider() {

		$provider[] = array(
			'abc'
		);

		$provider[] = array(
			'VIAF54846467'
		);

		return $provider;
	}

	// Parsing is tested by ViafResponseParserTest integration test

}
