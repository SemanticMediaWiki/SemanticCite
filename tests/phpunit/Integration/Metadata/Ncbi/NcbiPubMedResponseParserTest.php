<?php

namespace SCI\Tests\Integration\Metadata\Ncbi;

use SCI\Metadata\Provider\Ncbi\NcbiPubMedResponseContentParser;
use SCI\Metadata\FilteredMetadataRecord;
use SMW\Tests\Utils\UtilityFactory;

/**
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class NcbiPubMedResponseParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider fileProvider
	 */
	public function testParser( $id, $jsonFile, $xmlFile, $expectedResultFile ) {

		$type = strpos( $id, 'PMC' ) !== false ? 'pmc' : 'pubmed';

		if ( $type === 'pubmed' ) {
			$id = str_replace( array( 'PMID', 'PMC' ), '', $id );
		}

		$jsonContents = file_get_contents( $jsonFile );
		$xmlContents = file_get_contents( $xmlFile );

		// #Warning: Strings contain different line endings!
		$expected = str_replace( "\r\n", "\n", file_get_contents( $expectedResultFile ) );

		$ncbiRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Ncbi\NcbiRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$ncbiRequestContentFetcher->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( $type ) );

		$ncbiRequestContentFetcher->expects( $this->any() )
			->method( 'getError' )
			->will( $this->returnValue( '' ) );

		$ncbiRequestContentFetcher->expects( $this->any() )
			->method( 'fetchSummaryFor' )
			->with(	$this->stringContains( str_replace( array( 'PMID', 'PMC' ), '', $id ) ) )
			->will( $this->returnValue( $jsonContents ) );

		$ncbiRequestContentFetcher->expects( $this->any() )
			->method( 'fetchAbstractFor' )
			->with(	$this->stringContains( str_replace( array( 'PMID', 'PMC' ), '', $id ) ) )
			->will( $this->returnValue( $xmlContents ) );

		$instance = new NcbiPubMedResponseContentParser(
			$ncbiRequestContentFetcher,
			new FilteredMetadataRecord()
		);

		$this->assertEquals(
			$jsonContents . $xmlContents,
			$instance->getRawResponse( $id )
		);

		$instance->doParseFor( $id );

		$this->assertContains(
			$expected,
			$instance->getFilteredMetadataRecord()->asSciteTransclusion()
		);
	}

	public function fileProvider() {

		$provider = array();

		$bulkFileProvider = UtilityFactory::getInstance()->newBulkFileProvider( __DIR__ . '/Fixtures' );
		$bulkFileProvider->searchByFileExtension( 'json' );

		foreach ( $bulkFileProvider->getFiles() as $file ) {

			$pathinfo = pathinfo( $file  );

			$provider[basename( $file )] = array(
				$pathinfo['filename'],
				$file,
				$pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.xml',
				$pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.result'
			);
		}

		return $provider;
	}

}
