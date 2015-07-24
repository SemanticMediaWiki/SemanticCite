<?php

namespace SCI\Tests\Integration\Metadata\Oclc;

use SCI\Metadata\Provider\Oclc\OclcResponseContentParser;
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
class OclcResponseParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider fileProvider
	 */
	public function testParser( $id, $file, $expectedResultFile ) {

		$contents = file_get_contents( $file );

		// #Warning: Strings contain different line endings!
		$expected = str_replace( "\r\n", "\n", file_get_contents( $expectedResultFile ) );

		$oclcRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Oclc\OclcRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$oclcRequestContentFetcher->expects( $this->any() )
			->method( 'fetchJsonLdFor' )
			->with(	$this->equalTo( $id ) )
			->will( $this->returnValue( $contents) );

		$instance = new OclcResponseContentParser(
			$oclcRequestContentFetcher,
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
