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
	private $cacheKeyGenerator;

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

		$this->cacheKeyGenerator = $this->getMockBuilder( '\SCI\CacheKeyGenerator' )
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
				$this->cacheKeyGenerator
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
			$this->cacheKeyGenerator
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
			$this->cacheKeyGenerator
		);

		$text = '<div id="scite-custom-referencelist"><span></span></div>';
		$instance->addReferenceListToText( $text );

		$this->assertEmpty(
			$text
		);
	}

}
