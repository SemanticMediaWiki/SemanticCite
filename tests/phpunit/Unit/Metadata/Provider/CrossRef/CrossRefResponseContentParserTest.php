<?php

namespace SCI\Tests\Metadata\Provider\CrossRef;

use SCI\Metadata\Provider\CrossRef\CrossRefResponseContentParser;

/**
 * @covers \SCI\Metadata\Provider\CrossRef\CrossRefResponseContentParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class CrossRefResponseContentParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$crossRefRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\CrossRef\CrossRefRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\CrossRef\CrossRefResponseContentParser',
			new CrossRefResponseContentParser( $crossRefRequestContentFetcher, $filteredMetadataRecord )
		);
	}

	/**
	 * @dataProvider invalidIdProvider
	 */
	public function testDoParseForInvalid( $id ) {

		$crossRefRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\CrossRef\CrossRefRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new CrossRefResponseContentParser(
			$crossRefRequestContentFetcher,
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
			'PMC54846467M'
		);

		return $provider;
	}

	// Parsing is tested by CrossRefResponseParserTest integration test

}
