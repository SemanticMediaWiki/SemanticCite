<?php

namespace SCI\Tests;

use SCI\CitationReferencePositionJournal;

/**
 * @covers \SCI\CitationReferencePositionJournal
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class CitationReferencePositionJournalTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$cacheKeyGenerator = $this->getMockBuilder( '\SCI\CacheKeyGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\CitationReferencePositionJournal',
			new CitationReferencePositionJournal( $cache, $cacheKeyGenerator )
		);
	}

	public function testUnboundReferenceList() {

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$cacheKeyGenerator = $this->getMockBuilder( '\SCI\CacheKeyGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new CitationReferencePositionJournal( $cache, $cacheKeyGenerator );

		$this->assertNull(
			$instance->buildJournalForUnboundReferenceList( array() )
		);

		$this->assertInternalType(
			'array',
			$instance->buildJournalForUnboundReferenceList( array( 'foo' ) )
		);
	}

}
