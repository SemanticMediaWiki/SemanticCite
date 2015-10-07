<?php

namespace SCI\Tests;

use SCI\ReferenceListFactory;
use SCI\Options;

/**
 * @covers \SCI\ReferenceListFactory
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class ReferenceListFactoryTest extends \PHPUnit_Framework_TestCase {

	private $store;
	private $namespaceExaminer;
	private $citationReferencePositionJournal;

	protected function setUp() {

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->namespaceExaminer = $this->getMockBuilder( '\SMW\NamespaceExaminer' )
			->disableOriginalConstructor()
			->getMock();

		$this->citationReferencePositionJournal = $this->getMockBuilder( '\SCI\CitationReferencePositionJournal' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\ReferenceListFactory',
			new ReferenceListFactory( $this->store, $this->namespaceExaminer, $this->citationReferencePositionJournal )
		);
	}

	public function testCanConstructReferenceListOutputRenderer() {

		$instance = new ReferenceListFactory(
			$this->store,
			$this->namespaceExaminer,
			$this->citationReferencePositionJournal
		);

		$this->assertInstanceOf(
			'\SCI\ReferenceListOutputRenderer',
			$instance->newReferenceListOutputRenderer()
		);
	}

	public function testCanConstructCachedReferenceListOutputRenderer() {

		$mediaWikiContextInteractor = $this->getMockBuilder( '\SCI\MediaWikiContextInteractor' )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$cacheKeyProvider = $this->getMockBuilder( '\SCI\CacheKeyProvider' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ReferenceListFactory(
			$this->store,
			$this->namespaceExaminer,
			$this->citationReferencePositionJournal
		);

		$options = new Options( array(
			'numberOfReferenceListColumns' => null,
			'referenceListType' => null,
			'browseLinkToCitationResource' => null,
			'citationReferenceCaptionFormat' => null
		) );

		$this->assertInstanceOf(
			'\SCI\CachedReferenceListOutputRenderer',
			$instance->newCachedReferenceListOutputRenderer( $mediaWikiContextInteractor, $cache, $cacheKeyProvider, $options )
		);
	}

}
