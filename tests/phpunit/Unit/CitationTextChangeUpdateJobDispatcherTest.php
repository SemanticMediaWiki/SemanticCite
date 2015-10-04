<?php

namespace SCI\Tests;

use SCI\CitationTextChangeUpdateJobDispatcher;
use SMW\DIWikiPage;

/**
 * @covers \SCI\CitationTextChangeUpdateJobDispatcher
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CitationTextChangeUpdateJobDispatcherTest extends \PHPUnit_Framework_TestCase {

	private $store;
	private $referenceBacklinksLookup;

	protected function setUp() {

		$this->store = $this->getMockBuilder( '\SMW\SQLStore\SQLStore' )
			->disableOriginalConstructor()
			->getMock();

		$this->referenceBacklinksLookup = $this->getMockBuilder( '\SCI\ReferenceBacklinksLookup' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\CitationTextChangeUpdateJobDispatcher',
			new CitationTextChangeUpdateJobDispatcher(
				$this->store,
				$this->referenceBacklinksLookup
			)
		);
	}

	public function testDisabledDispatchJob() {

		$instance = new CitationTextChangeUpdateJobDispatcher(
			$this->store,
			$this->referenceBacklinksLookup
		);

		$instance->setEnabledUpdateJobState( false );

		$compositePropertyTableDiffIterator = $this->getMockBuilder( '\SMW\SQLStore\CompositePropertyTableDiffIterator' )
			->disableOriginalConstructor()
			->getMock();

		$compositePropertyTableDiffIterator->expects( $this->never() )
			->method( 'getOrderedDiffByTable' );

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$instance->dispatchUpdateJobFor( $subject, $compositePropertyTableDiffIterator );
	}

	/**
	 * @dataProvider compositePropertyTableDiffProvider
	 */
	public function testDispatchJobForDiffableChange( $diff ) {

		$idTable = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->setMethods( array( 'getDataItemPoolHashListFor' ) )
			->getMock();

		$idTable->expects( $this->once() )
			->method( 'getDataItemPoolHashListFor' )
			->will( $this->returnValue( array( 'Foo#0##' ) ) );

		$propertyTableInfoFetcher = $this->getMockBuilder( '\SMW\SQLStore\PropertyTableInfoFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$this->store->expects( $this->once() )
			->method( 'getPropertyTableInfoFetcher' )
			->will( $this->returnValue( $propertyTableInfoFetcher ) );

		$this->store->expects( $this->once() )
			->method( 'getObjectIds' )
			->will( $this->returnValue( $idTable ) );

		$this->referenceBacklinksLookup->expects( $this->once() )
			->method( 'findReferenceBacklinksFor' )
			->will( $this->returnValue( array( new DIWikiPage( 'Bar', NS_MAIN ) ) ) );

		$instance = new CitationTextChangeUpdateJobDispatcher(
			$this->store,
			$this->referenceBacklinksLookup
		);

		$compositePropertyTableDiffIterator = $this->getMockBuilder( '\SMW\SQLStore\CompositePropertyTableDiffIterator' )
			->disableOriginalConstructor()
			->getMock();

		$compositePropertyTableDiffIterator->expects( $this->once() )
			->method( 'getOrderedDiffByTable' )
			->will( $this->returnValue( $diff ) );

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->assertTrue(
			$instance->dispatchUpdateJobFor( $subject, $compositePropertyTableDiffIterator )
		);
	}

	public function testDispatchJobForNoValidDiff() {

		$diff = array();

		$propertyTableInfoFetcher = $this->getMockBuilder( '\SMW\SQLStore\PropertyTableInfoFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$this->store->expects( $this->once() )
			->method( 'getPropertyTableInfoFetcher' )
			->will( $this->returnValue( $propertyTableInfoFetcher ) );

		$instance = new CitationTextChangeUpdateJobDispatcher(
			$this->store,
			$this->referenceBacklinksLookup
		);

		$compositePropertyTableDiffIterator = $this->getMockBuilder( '\SMW\SQLStore\CompositePropertyTableDiffIterator' )
			->disableOriginalConstructor()
			->getMock();

		$compositePropertyTableDiffIterator->expects( $this->once() )
			->method( 'getOrderedDiffByTable' )
			->will( $this->returnValue( $diff ) );

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->assertFalse(
			$instance->dispatchUpdateJobFor( $subject, $compositePropertyTableDiffIterator )
		);
	}

	public function compositePropertyTableDiffProvider() {

		$diff = array(
			'sci_cite_text' => array(
				'delete' => array(
					's_id' => 42
				)
			)
		);

		$provider[] = array( $diff );

		$diff = array(
			'sci_cite_text' => array(
				'insert' => array(
					's_id' => 42
				)
			)
		);

		$provider[] = array( $diff );

		return $provider;
	}

}
