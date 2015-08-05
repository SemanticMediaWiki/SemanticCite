<?php

namespace SCI\Tests;

use SCI\BrowsePropertyLookup;
use SCI\PropertyRegistry;
use SMW\DIWikiPage;
use SMW\DIProperty;

/**
 * @covers \SCI\BrowsePropertyLookup
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class BrowsePropertyLookupTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SCI\BrowsePropertyLookup',
			new BrowsePropertyLookup( $store )
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

		$instance = new BrowsePropertyLookup( $store );

		$instance->addReferenceBacklinks(
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

		$instance = new BrowsePropertyLookup( $store );

		$instance->addReferenceBacklinks(
			$semanticData
		);
	}

}
