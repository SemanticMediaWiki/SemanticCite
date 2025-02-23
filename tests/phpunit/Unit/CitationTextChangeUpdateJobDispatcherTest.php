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
class CitationTextChangeUpdateJobDispatcherTest extends \PHPUnit\Framework\TestCase {

	private $store;
	private $referenceBacklinksLookup;

	protected function setUp() : void {

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

		$changeOp = $this->getMockBuilder( '\SMW\SQLStore\ChangeOp\ChangeOp' )
			->disableOriginalConstructor()
			->getMock();

		$changeOp->expects( $this->never() )
			->method( 'getOrderedDiffByTable' );

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$instance->dispatchUpdateJobFor( $subject, $changeOp );
	}

	/**
	 * @dataProvider compositePropertyTableDiffProvider
	 */
	public function testDispatchJobForDiffableChange( $diff ) {

		$idTable = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->setMethods( [ 'getDataItemPoolHashListFor' ] )
			->getMock();

		$idTable->expects( $this->once() )
			->method( 'getDataItemPoolHashListFor' )
			->will( $this->returnValue( [ 'Foo#0##' ] ) );

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
			->will( $this->returnValue( [ new DIWikiPage( 'Bar', NS_MAIN ) ] ) );

		$instance = new CitationTextChangeUpdateJobDispatcher(
			$this->store,
			$this->referenceBacklinksLookup
		);

		$changeOp = $this->getMockBuilder( '\SMW\SQLStore\ChangeOp\ChangeOp' )
			->disableOriginalConstructor()
			->getMock();

		$changeOp->expects( $this->once() )
			->method( 'getOrderedDiffByTable' )
			->will( $this->returnValue( $diff ) );

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->assertTrue(
			$instance->dispatchUpdateJobFor( $subject, $changeOp )
		);
	}

	public function testDispatchJobForNoValidDiff() {

		$diff = [];

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

		$changeOp = $this->getMockBuilder( '\SMW\SQLStore\ChangeOp\ChangeOp' )
			->disableOriginalConstructor()
			->getMock();

		$changeOp->expects( $this->once() )
			->method( 'getOrderedDiffByTable' )
			->will( $this->returnValue( $diff ) );

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->assertFalse(
			$instance->dispatchUpdateJobFor( $subject, $changeOp )
		);
	}

	public function compositePropertyTableDiffProvider() {

		$diff = [
			'sci_cite_text' => [
				'delete' => [
					[ 's_id' => 42 ]
				]
			]
		];

		$provider[] = [ $diff ];

		$diff = [
			'sci_cite_text' => [
				'insert' => [
					[ 's_id' => 42 ]
				]
			]
		];

		$provider[] = [ $diff ];

		return $provider;
	}

}
