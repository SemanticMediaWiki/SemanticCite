<?php

namespace SCI\Tests\Integration\Metadata\OpenLibrary;

use SCI\Metadata\Provider\OpenLibrary\OLResponseContentParser;
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
class OLResponseParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider fileProvider
	 */
	public function testParser( $id, $file, $expectedResultFile ) {

		$contents = file_get_contents( $file );

		// #Warning: Strings contain different line endings!
		$expected = str_replace( "\r\n", "\n", file_get_contents( $expectedResultFile ) );

		$olRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\OpenLibrary\OLRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$olRequestContentFetcher->expects( $this->any() )
			->method( 'fetchDataJsonFor' )
			->with(	$this->equalTo( $id ) )
			->will( $this->returnValue( $contents) );

		$instance = new OLResponseContentParser(
			$olRequestContentFetcher,
			new FilteredMetadataRecord()
		);

		$this->assertEquals(
			$contents,
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
				$pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.result'
			);
		}

		return $provider;
	}

}
