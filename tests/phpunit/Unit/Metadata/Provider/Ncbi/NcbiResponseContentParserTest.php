<?php

namespace SCI\Tests\Metadata\Provider\Ncbi;

use SCI\Metadata\Provider\Ncbi\NcbiResponseContentParser;

/**
 * @covers \SCI\Metadata\Provider\Ncbi\NcbiResponseContentParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class NcbiResponseContentParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$ncbiRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Ncbi\NcbiRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\Metadata\Provider\Ncbi\NcbiResponseContentParser',
			new NcbiResponseContentParser( $ncbiRequestContentFetcher, $filteredMetadataRecord )
		);
	}

	/**
	 * @dataProvider invalidIdProvider
	 */
	public function testDoParseForInvalidNcbi( $type, $id ) {

		$ncbiRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Ncbi\NcbiRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$ncbiRequestContentFetcher->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( $type ) );

		$filteredMetadataRecord = $this->getMockBuilder( '\SCI\Metadata\FilteredMetadataRecord' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new NcbiResponseContentParser(
			$ncbiRequestContentFetcher,
			$filteredMetadataRecord
		);

		$instance->doParseFor( $id );

		$this->assertFalse(
			$instance->isSuccess()
		);
	}

	public function invalidIdProvider() {

		$provider[] = array(
			'foo',
			'abc'
		);

		$provider[] = array(
			'foo',
			'PMC54846467'
		);

		$provider[] = array(
			'pmc',
			'PMC54846467'
		);

		return $provider;
	}

	// Parsing is tested by NcbiResponseParserTest integration test

}
