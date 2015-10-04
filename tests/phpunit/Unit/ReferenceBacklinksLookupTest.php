<?php

namespace SCI\Tests;

use SCI\ReferenceBacklinksLookup;
use SCI\PropertyRegistry;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMWDIBlob as DIBlob;

/**
 * @covers \SCI\ReferenceBacklinksLookup
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class ReferenceBacklinksLookupTest extends \PHPUnit_Framework_TestCase {

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

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( new DIProperty( PropertyRegistry::SCI_CITE_KEY ) ) )
			->will( $this->returnValue( array( 'Foo', 'Bar' ) ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new ReferenceBacklinksLookup( $store );
		$instance->setStore( $store );

		$this->assertEquals(
			'Bar',
			$instance->tryToFindCitationKeyFor( DIWikiPage::newFromText( __METHOD__ ) )
		);
	}

	public function testTryToAddReferenceBacklinksForNoKeys() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( DIWikiPage::newFromText( __METHOD__ ) ) );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( new DIProperty( PropertyRegistry::SCI_CITE_KEY ) ) )
			->will( $this->returnValue( array() ) );

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

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

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( DIWikiPage::newFromText( __METHOD__ ) ) );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->will( $this->returnValue( array( DIWikiPage::newFromText( 'Bar' ) ) ) );

		$semanticData->expects( $this->atLeastOnce() )
			->method( 'addPropertyObjectValue' )
			->with(
					$this->equalTo( new DIProperty( PropertyRegistry::SCI_CITE_REFERENCE ) ),
					$this->anything() );

		$queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->expects( $this->once() )
			->method( 'getResults' )
			->will( $this->returnValue( array( DIWikiPage::newFromText( 'Foo' ) ) ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getQueryResult' )
			->will( $this->returnValue( $queryResult ) );

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new ReferenceBacklinksLookup( $store );

		$instance->addReferenceBacklinksTo(
			$semanticData
		);
	}

	public function testGetSpecialPropertySearchFurtherLink() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->will( $this->returnValue( array( new DIBlob( 'Bar' ) ) ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$property = new DIProperty( PropertyRegistry::SCI_CITE_REFERENCE );
		$subject = DIWikiPage::newFromText( __METHOD__ );

		$instance = new ReferenceBacklinksLookup( $store );

		$result = $instance->getSpecialPropertySearchFurtherLink(
			$property,
			$subject,
			$html
		);

		$this->assertFalse(
			$result
		);

		$this->assertContains(
			'SearchByProperty',
			$html
		);
	}

}
