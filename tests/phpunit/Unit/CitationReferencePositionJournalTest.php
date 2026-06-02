<?php

namespace SCI\Tests;

use SCI\CitationReferencePositionJournal;
use SMW\DataItems\WikiPage;

/**
 * @covers \SCI\CitationReferencePositionJournal
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since   1.0
 *
 * @author mwjames
 */
class CitationReferencePositionJournalTest extends \PHPUnit\Framework\TestCase {

	private $cache;
	private $cacheKeyProvider;

	protected function setUp(): void {
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

		$this->assertIsArray(
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
		$subject = WikiPage::newFromText( __METHOD__ );

		$this->cacheKeyProvider->expects( $this->once() )
			->method( 'getCacheKeyForCitationReference' )
			->with( $subject->getHash() );

		$instance = new CitationReferencePositionJournal(
			$this->cache,
			$this->cacheKeyProvider
		);

		$instance->getJournalBySubject( $subject );
	}

}
