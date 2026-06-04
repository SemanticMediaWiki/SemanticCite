<?php

namespace SCI\Tests;

use SCI\PropertyRegistry;
use SCI\ReferenceBacklinksLookup;
use SMW\DataItems\Blob;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;

/**
 * @covers \SCI\ReferenceBacklinksLookup
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since   1.0
 *
 * @author mwjames
 */
class ReferenceBacklinksLookupTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SCI\ReferenceBacklinksLookup',
			new ReferenceBacklinksLookup( $store )
		);
	}

	public function testtryToFindCitationKeyFor() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( new Property( PropertyRegistry::SCI_CITE_KEY ) )
			->willReturn( [ 'Foo', 'Bar' ] );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$instance = new ReferenceBacklinksLookup( $store );
		$instance->setStore( $store );

		$this->assertEquals(
			'Bar',
			$instance->tryToFindCitationKeyFor( WikiPage::newFromText( __METHOD__ ) )
		);
	}

	public function testTryToAddReferenceBacklinksForNoKeys() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( WikiPage::newFromText( __METHOD__ ) );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( new Property( PropertyRegistry::SCI_CITE_KEY ) )
			->willReturn( [] );

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$requestOptions = new \stdClass;
		$requestOptions->limit = 5;
		$requestOptions->offset = 0;

		$instance = new ReferenceBacklinksLookup( $store );
		$instance->setRequestOptions( $requestOptions );

		$instance->addReferenceBacklinksTo(
			$semanticData
		);
	}

	public function testAddReferenceBacklinks() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( WikiPage::newFromText( __METHOD__ ) );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->willReturn( [ WikiPage::newFromText( 'Bar' ) ] );

		$semanticData->expects( $this->atLeastOnce() )
			->method( 'addPropertyObjectValue' )
			->with(
					new Property( PropertyRegistry::SCI_CITE_REFERENCE ),
					$this->anything() );

		$queryResult = $this->getMockBuilder( '\SMW\Query\QueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->expects( $this->once() )
			->method( 'getResults' )
			->willReturn( [ WikiPage::newFromText( 'Foo' ) ] );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getQueryResult' )
			->willReturn( $queryResult );

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$instance = new ReferenceBacklinksLookup( $store );

		$instance->addReferenceBacklinksTo(
			$semanticData
		);
	}

	public function testGetSpecialPropertySearchFurtherLink() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->willReturn( [ new Blob( 'Bar' ) ] );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$property = new Property( PropertyRegistry::SCI_CITE_REFERENCE );
		$subject = WikiPage::newFromText( __METHOD__ );

		$instance = new ReferenceBacklinksLookup( $store );

		$result = $instance->getSpecialPropertySearchFurtherLink(
			$property,
			$subject,
			$html
		);

		$this->assertFalse(
			$result
		);

		$this->assertStringContainsString(
			'SearchByProperty',
			$html
		);
	}

}
