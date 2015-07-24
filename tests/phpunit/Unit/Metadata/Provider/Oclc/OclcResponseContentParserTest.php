<?php

namespace SCI\Tests\Metadata\Provider\Oclc;

use SCI\Metadata\Provider\Oclc\OclcResponseContentParser;

/**
 * @covers \SCI\Metadata\Provider\Oclc\OclcResponseContentParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class OclcResponseContentParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$oclcRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Oclc\OclcRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\Oclc\OclcResponseContentParser',
			new OclcResponseContentParser( $oclcRequestContentFetcher, $filteredMetadataRecord )
		);
	}

	/**
	 * @dataProvider invalidIdProvider
	 */
	public function testDoParseForInvalidOclc( $id ) {

		$oclcRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Oclc\OclcRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new OclcResponseContentParser(
			$oclcRequestContentFetcher,
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
			'OCLC54846467'
		);

		return $provider;
	}

	// Parsing is tested by OclcResponseParserTest integration test

}
