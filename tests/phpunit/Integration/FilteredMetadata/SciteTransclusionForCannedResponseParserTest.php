<?php

namespace SCI\Tests\Integration\FilteredMetadata;

use SCI\FilteredMetadata\HttpResponseParserFactory;
use SCI\FilteredMetadata\BibliographicFilteredRecord;

/**
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SciteTransclusionForCannedResponseParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider crossrefFileProvider
	 */
	public function testCrossRefResponseParser( $id, $inputFile, $expectedResultFile ) {

		list( $id, $httpRequest, $expected ) = $this->prepareFileContents(
			$id,
			$inputFile,
			$expectedResultFile
		);

		$httpResponseParserFactory = new HttpResponseParserFactory(
			$httpRequest
		);

		$instance = $httpResponseParserFactory->newResponseParserForType( 'doi' );
		$instance->doFilterResponseFor( $id );

		$this->assertEquals(
			$expected,
			$instance->getFilteredRecord()->asSciteTransclusion()
		);
	}

	/**
	 * @dataProvider olFileProvider
	 */
	public function testOLResponseParser( $id, $inputFile, $expectedResultFile ) {

		list( $id, $httpRequest, $expected ) = $this->prepareFileContents(
			$id,
			$inputFile,
			$expectedResultFile
		);

		$httpResponseParserFactory = new HttpResponseParserFactory(
			$httpRequest
		);

		$instance = $httpResponseParserFactory->newResponseParserForType( 'ol' );
		$instance->doFilterResponseFor( $id );

		$this->assertEquals(
			$expected,
			$instance->getFilteredRecord()->asSciteTransclusion()
		);
	}

	/**
	 * @dataProvider viafFileProvider
	 */
	public function testViafResponseParser( $id, $inputFile, $expectedResultFile ) {

		list( $id, $httpRequest, $expected ) = $this->prepareFileContents(
			$id,
			$inputFile,
			$expectedResultFile
		);

		$httpResponseParserFactory = new HttpResponseParserFactory(
			$httpRequest
		);

		$instance = $httpResponseParserFactory->newResponseParserForType( 'viaf' );
		$instance->doFilterResponseFor( $id );

		$this->assertEquals(
			$expected,
			$instance->getFilteredRecord()->asSciteTransclusion()
		);
	}

	/**
	 * @dataProvider oclcFileProvider
	 */
	public function testOclcResponseParser( $id, $inputFile, $expectedResultFile ) {

		list( $id, $httpRequest, $expected ) = $this->prepareFileContents(
			$id,
			$inputFile,
			$expectedResultFile
		);

		$httpResponseParserFactory = new HttpResponseParserFactory(
			$httpRequest
		);

		$instance = $httpResponseParserFactory->newResponseParserForType( 'oclc' );
		$instance->doFilterResponseFor( $id );

		$this->assertEquals(
			$expected,
			$instance->getFilteredRecord()->asSciteTransclusion()
		);
	}

	/**
	 * @dataProvider pubMedFileProvider
	 */
	public function testPubMedResponseParser( $id, $type, $jsonInputFile, $xmlInputFile, $expectedResultFile ) {

		list( $id, $httpRequest, $expected ) = $this->prepareFileContents(
			$id,
			$jsonInputFile,
			$expectedResultFile,
			$xmlInputFile
		);

		$httpResponseParserFactory = new HttpResponseParserFactory(
			$httpRequest
		);

		$instance = $httpResponseParserFactory->newResponseParserForType( $type );
		$instance->doFilterResponseFor( $id );

		$this->assertEquals(
			$expected,
			$instance->getFilteredRecord()->asSciteTransclusion()
		);
	}

	public function crossrefFileProvider() {

		$path = __DIR__ . '/Fixtures/';
		$provider = array();

		$provider[] = array(
			'10.1007/978-0-387-76978-3',
			$path . '10.1007-2F978-0-387-76978-3.json',
			$path . '10.1007-2F978-0-387-76978-3.expected'
		);

		return $provider;
	}

	public function olFileProvider() {

		$path = __DIR__ . '/Fixtures/';
		$provider = array();

		$provider[] = array(
			'OL2206423M',
			$path . 'OL2206423M.json',
			$path . 'OL2206423M.expected'
		);

		$provider[] = array(
			'0385081308',
			$path . 'OL2206423M.json',
			$path . 'OL2206423M.expected'
		);

		return $provider;
	}

	public function viafFileProvider() {

		$path = __DIR__ . '/Fixtures/';
		$provider = array();

		$provider[] = array(
			'253484422',
			$path . 'VIAF253484422.xml',
			$path . 'VIAF253484422.expected'
		);

		return $provider;
	}

	public function oclcFileProvider() {

		$path = __DIR__ . '/Fixtures/';
		$provider = array();

		$provider[] = array(
			'74330434',
			$path . 'OCLC74330434.json',
			$path . 'OCLC74330434.expected'
		);

		$provider[] = array(
			'41266045',
			$path . 'OCLC41266045.json',
			$path . 'OCLC41266045.expected'
		);

		return $provider;
	}

	public function pubMedFileProvider() {

		$path = __DIR__ . '/Fixtures/';
		$provider = array();

		$provider[] = array(
			'PMC2776723',
			'pmc',
			$path . 'PMC2776723.json',
			$path . 'PMC2776723.xml',
			$path . 'PMC2776723.expected'
		);

		return $provider;
	}


	private function prepareFileContents( $id, $inputFileA, $expectedResultFile, $inputFileB = false ) {

		$inputFileAContents = file_get_contents( $inputFileA );
		$inputFileBContents = $inputFileB ? file_get_contents( $inputFileB ) : '';

		$expected = str_replace( "\r\n", "\n", file_get_contents( $expectedResultFile ) );

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest->expects( $this->any() )
			->method( 'execute' )
			->will( $this->onConsecutiveCalls(
				$inputFileAContents, $inputFileBContents ) );

		$httpRequest->expects( $this->any() )
			->method( 'getLastError' )
			->will( $this->returnValue( '' ) );

		return array( $id, $httpRequest, $expected );
	}

}
