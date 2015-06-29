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

	public function testCanConstruct() {

		$referenceListOutputRenderer = $this->getMockBuilder( '\SCI\ReferenceListOutputRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$contextInteractor = $this->getMockBuilder( '\SCI\MediaWikiContextInteractor' )
			->disableOriginalConstructor()
			->getMock();

		$namespaceExaminer = $this->getMockBuilder( '\SMW\NamespaceExaminer' )
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$cacheKeyGenerator = $this->getMockBuilder( '\SCI\CacheKeyGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\CachedReferenceListOutputRenderer',
			new CachedReferenceListOutputRenderer(
				$referenceListOutputRenderer,
				$contextInteractor,
				$namespaceExaminer,
				$cache,
				$cacheKeyGenerator
			)
		);
	}

	public function testUnmodifiedTextForNotEnabledSemanticNamespace() {

		$referenceListOutputRenderer = $this->getMockBuilder( '\SCI\ReferenceListOutputRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$contextInteractor = $this->getMockBuilder( '\SCI\MediaWikiContextInteractor' )
			->disableOriginalConstructor()
			->getMock();

		$contextInteractor->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( __METHOD__ ) ) );

		$contextInteractor->expects( $this->once() )
			->method( 'hasAction' )
			->will( $this->returnValue( true ) );

		$namespaceExaminer = $this->getMockBuilder( '\SMW\NamespaceExaminer' )
			->disableOriginalConstructor()
			->getMock();

		$namespaceExaminer->expects( $this->once() )
			->method( 'isSemanticEnabled' )
			->will( $this->returnValue( false ) );

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$cacheKeyGenerator = $this->getMockBuilder( '\SCI\CacheKeyGenerator' )
			->disableOriginalConstructor()
			->getMock();

		$instance =	new CachedReferenceListOutputRenderer(
			$referenceListOutputRenderer,
			$contextInteractor,
			$namespaceExaminer,
			$cache,
			$cacheKeyGenerator
		);

		$text = '';
		$instance->addReferenceListToText( $text );

		$this->assertEmpty(
			$text
		);
	}

}
