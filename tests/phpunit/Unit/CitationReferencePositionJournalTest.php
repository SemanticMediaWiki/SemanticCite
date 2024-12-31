<?php

namespace SCI\Tests;

use SCI\CitationReferencePositionJournal;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SCI\CitationReferencePositionJournal
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class CitationReferencePositionJournalTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	private $cache;
	private $cacheKeyProvider;

	protected function setUp() : void {

		$this->cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$this->cacheKeyProvider = $this->getMockBuilder( '\SCI\CacheKeyProvider' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\CitationReferencePositionJournal',
			new CitationReferencePositionJournal( $this->cache, $this->cacheKeyProvider )
		);
	}

	public function testUnboundReferenceList() {

		$instance = new CitationReferencePositionJournal(
			$this->cache,
			$this->cacheKeyProvider
		);

		$this->assertNull(
			$instance->buildJournalForUnboundReferenceList( [] )
		);

		$this->assertInternalType(
			'array',
			$instance->buildJournalForUnboundReferenceList( [ 'foo' ] )
		);
	}

	public function testTryToAddJournalEntryForNullSubject() {

		$instance = new CitationReferencePositionJournal(
			$this->cache,
			$this->cacheKeyProvider
		);

		$this->assertNull(
			$instance->addJournalEntryFor( null, 'foo' )
		);
	}

	public function testTryToGetJournalBySubject() {

		$subject = \SMW\DIWikiPage::newFromText( __METHOD__ );

		$this->cacheKeyProvider->expects( $this->once() )
			->method( 'getCacheKeyForCitationReference' )
			->with( $this->equalTo( $subject->getHash() ) );

		$instance = new CitationReferencePositionJournal(
			$this->cache,
			$this->cacheKeyProvider
		);

		$instance->getJournalBySubject( $subject );
	}

}
