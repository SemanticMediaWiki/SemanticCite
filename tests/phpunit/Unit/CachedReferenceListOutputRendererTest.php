<?php

namespace SCI\Tests;

use SCI\CachedReferenceListOutputRenderer;

/**
 * @covers \SCI\CachedReferenceListOutputRenderer
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CachedReferenceListOutputRendererTest extends \PHPUnit_Framework_TestCase {

	private $referenceListOutputRenderer;
	private $contextInteractor;
	private $namespaceExaminer;
	private $cache;
	private $cacheKeyProvider;

	protected function setUp() {

		$this->referenceListOutputRenderer = $this->getMockBuilder( '\SCI\ReferenceListOutputRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$this->contextInteractor = $this->getMockBuilder( '\SCI\MediaWikiContextInteractor' )
			->disableOriginalConstructor()
			->getMock();

		$this->namespaceExaminer = $this->getMockBuilder( '\SMW\NamespaceExaminer' )
			->disableOriginalConstructor()
			->getMock();

		$this->cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
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
			->will( $this->returnValue( \Title::newFromText( __METHOD__ ) ) );

		$this->contextInteractor->expects( $this->once() )
			->method( 'hasAction' )
			->will( $this->returnValue( true ) );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->will( $this->returnValue( false ) );

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
			->with( $this->equalTo( 'SCI_NOREFERENCELIST' ) )
			->will( $this->returnValue( true ) );

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
			->will( $this->returnValue( \Title::newFromText( __METHOD__, NS_FILE ) ) );

		$this->contextInteractor->expects( $this->once() )
			->method( 'hasAction' )
			->will( $this->returnValue( true ) );

		$this->contextInteractor->expects( $this->never() )
			->method( 'getOldId' );

		$this->namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->will( $this->returnValue( true ) );

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
