<?php

namespace SCI\Tests;

use SCI\CachedReferenceListOutputRenderer;

/**
 * @covers \SCI\CachedReferenceListOutputRenderer
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class CachedReferenceListOutputRendererTest extends \PHPUnit\Framework\TestCase {

	private $referenceListOutputRenderer;
	private $contextInteractor;
	private $namespaceExaminer;
	private $cache;
	private $cacheKeyProvider;

	protected function setUp(): void {
		$this->referenceListOutputRenderer = $this->getMockBuilder( '\SCI\ReferenceListOutputRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$this->contextInteractor = $this->getMockBuilder( '\SCI\MediaWikiContextInteractor' )
			->disableOriginalConstructor()
			->getMock();

		$this->namespaceExaminer = $this->getMockBuilder( '\SMW\NamespaceExaminer' )
			->disableOriginalConstructor()
			->getMock();

		$this->cache = $this->getMockBuilder( '\Wikimedia\ObjectCache\BagOStuff' )
			->disableOriginalConstructor()
			->getMock();

		$this->cacheKeyProvider = $this->getMockBuilder( '\SCI\CacheKeyProvider' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SCI\CachedReferenceListOutputRenderer',
			new CachedReferenceListOutputRenderer(
				$this->referenceListOutputRenderer,
				$this->contextInteractor,
				$this->namespaceExaminer,
				$this->cache,
				$this->cacheKeyProvider
			)
		);
	}

	public function testUnmodifiedTextForNotEnabledSemanticNamespace() {
		$this->contextInteractor->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( \MediaWiki\Title\Title::newFromText( __METHOD__ ) );

		$this->contextInteractor->expects( $this->once() )
			->method( 'hasAction' )
			->willReturn( true );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->willReturn( false );

		$instance =	new CachedReferenceListOutputRenderer(
			$this->referenceListOutputRenderer,
			$this->contextInteractor,
			$this->namespaceExaminer,
			$this->cache,
			$this->cacheKeyProvider
		);

		$text = '';
		$instance->addReferenceListToText( $text );

		$this->assertEmpty(
			$text
		);
	}

	public function testRemovalOfPlaceholderForDisabledReferencelist() {
		$this->contextInteractor->expects( $this->once() )
			->method( 'hasMagicWord' )
			->with( 'SCI_NOREFERENCELIST' )
			->willReturn( true );

		$instance =	new CachedReferenceListOutputRenderer(
			$this->referenceListOutputRenderer,
			$this->contextInteractor,
			$this->namespaceExaminer,
			$this->cache,
			$this->cacheKeyProvider
		);

		$text = '<div id="scite-custom-referencelist"><span></span></div>';
		$instance->addReferenceListToText( $text );

		$this->assertEmpty(
			$text
		);
	}

	public function testNoAutoReferencelistOnFileNamespace() {
		$this->contextInteractor->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->willReturn( \MediaWiki\Title\Title::newFromText( __METHOD__, NS_FILE ) );

		$this->contextInteractor->expects( $this->once() )
			->method( 'hasAction' )
			->willReturn( true );

		$this->contextInteractor->expects( $this->never() )
			->method( 'getOldId' );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->willReturn( true );

		$instance =	new CachedReferenceListOutputRenderer(
			$this->referenceListOutputRenderer,
			$this->contextInteractor,
			$this->namespaceExaminer,
			$this->cache,
			$this->cacheKeyProvider
		);

		$text = '';
		$instance->addReferenceListToText( $text );
	}

}
