<?php

namespace SCI\Tests\Integration\Metadata\CrossRef;

use SCI\Metadata\Provider\CrossRef\CrossRefResponseContentParser;
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
class CrossRefResponseParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider fileProvider
	 */
	public function testParser( $id, $file, $expectedResultFile ) {

		$contents = file_get_contents( $file );

		// #Warning: Strings contain different line endings!
		$expected = str_replace( "\r\n", "\n", file_get_contents( $expectedResultFile ) );

		$crossRefRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\CrossRef\CrossRefRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$crossRefRequestContentFetcher->expects( $this->any() )
			->method( 'fetchCiteprocJsonFor' )
			->with(	$this->equalTo( $id ) )
			->will( $this->returnValue( $contents) );

		$instance = new CrossRefResponseContentParser(
			$crossRefRequestContentFetcher,
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
				str_replace( "-2F", "/", $pathinfo['filename'] ),
				$file,
				$pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.result'
			);
		}

		return $provider;
	}

}
