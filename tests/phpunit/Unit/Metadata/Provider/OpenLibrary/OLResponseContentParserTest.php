<?php

namespace SCI\Tests\Metadata\Provider\OpenLibrary;

use SCI\Metadata\Provider\OpenLibrary\OLResponseContentParser;

/**
 * @covers \SCI\Metadata\Provider\OpenLibrary\OLResponseContentParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class OLResponseContentParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$olRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\OpenLibrary\OLRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\OpenLibrary\OLResponseContentParser',
			new OLResponseContentParser( $olRequestContentFetcher, $filteredMetadataRecord )
		);
	}

	/**
	 * @dataProvider invalidIdProvider
	 */
	public function testDoParseForInvalidViaf( $id ) {

		$olRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\OpenLibrary\OLRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new OLResponseContentParser(
			$olRequestContentFetcher,
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
			'OL54846467'
		);

		return $provider;
	}

	// Parsing is tested by OLResponseParserTest integration test

}
