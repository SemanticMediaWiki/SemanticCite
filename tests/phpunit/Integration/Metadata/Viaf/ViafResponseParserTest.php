<?php

namespace SCI\Tests\Integration\Metadata\Viaf;

use SCI\Metadata\Provider\Viaf\ViafResponseContentParser;
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
class ViafResponseParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider fileProvider
	 */
	public function testParser( $id, $file, $expectedResultFile ) {

		$contents = file_get_contents( $file );

		// #Warning: Strings contain different line endings!
		$expected = str_replace( "\r\n", "\n", file_get_contents( $expectedResultFile ) );

		$viafRequestContentFetcher = $this->getMockBuilder( '\SCI\Metadata\Provider\Viaf\ViafRequestContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$viafRequestContentFetcher->expects( $this->any() )
			->method( 'fetchXmlFor' )
			->with(	$this->equalTo( $id ) )
			->will( $this->returnValue( $contents) );

		$instance = new ViafResponseContentParser(
			$viafRequestContentFetcher,
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
		$bulkFileProvider->searchByFileExtension( 'xml' );

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
