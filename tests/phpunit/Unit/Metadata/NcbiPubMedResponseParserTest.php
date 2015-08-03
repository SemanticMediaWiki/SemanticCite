<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\NcbiPubMedResponseParser;

/**
 * @covers \SCI\Metadata\NcbiPubMedResponseParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class NcbiPubMedResponseParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$ncbiPubMedFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Ncbi\NcbiPubMedFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\Onoi\Remi\ResponseParser',
			new NcbiPubMedResponseParser( $ncbiPubMedFilteredHttpResponseParser )
		);
	}

	public function testInterfaceMethods() {

		$ncbiPubMedFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Ncbi\NcbiPubMedFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new NcbiPubMedResponseParser( $ncbiPubMedFilteredHttpResponseParser );

		$this->assertNull(
			$instance->usedCache()
		);

		$this->assertNull(
			$instance->getMessages()
		);

		$this->assertNull(
			$instance->getRecord()
		);

		$this->assertNull(
			$instance->getRawResponse( 42 )
		);
	}

	/**
	 * @dataProvider idProvider
	 */
	public function testDoParseForId( $id, $type, $expects ) {

		$record = $this->getMockBuilder( '\SCI\Metadata\BibliographicFilteredRecord' )
			->disableOriginalConstructor()
			->getMock();

		$record->expects( $this->at( 0 ) )
			->method( 'get' )
			->with( $this->stringContains( 'ncbi-dbtype' ) )
			->will( $this->returnValue( $type ) );

		$ncbiPubMedFilteredHttpResponseParser = $this->getMockBuilder( '\Onoi\Remi\Ncbi\NcbiPubMedFilteredHttpResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$ncbiPubMedFilteredHttpResponseParser->expects( $this->any() )
			->method( 'getRecord' )
			->will( $this->returnValue( $record ) );

		$ncbiPubMedFilteredHttpResponseParser->expects( $expects )
			->method( 'doParseFor' );

		$instance = new NcbiPubMedResponseParser( $ncbiPubMedFilteredHttpResponseParser );
		$instance->doParseFor( $id );
	}

	public function idProvider() {

		$provider[] = array(
			'abc',
			'pmc',
			 $this->never()
		);

		$provider[] = array(
			'abc',
			'pubmed',
			 $this->never()
		);

		$provider[] = array(
			'PMID54846467',
			'pmc',
			 $this->never()
		);

		$provider[] = array(
			'PMC54846467',
			'pmc',
			 $this->once()
		);

		$provider[] = array(
			'PMID54846467',
			'pmid',
			 $this->once()
		);

		$provider[] = array(
			'PMID54846467',
			'pubmed',
			 $this->once()
		);

		return $provider;
	}

}
