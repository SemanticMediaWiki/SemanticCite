<?php

namespace SCI\Tests;

use SCI\CacheKeyProvider;

/**
 * @covers \SCI\CacheKeyProvider
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class CacheKeyProviderTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\CacheKeyProvider',
			new CacheKeyProvider()
		);
	}

	public function testGetKey() {

		$instance = new CacheKeyProvider();
		$instance->setCachePrefix( 'foo' );

		$this->assertStringContainsString(
			'foo',
			$instance->getCacheKeyForCitationReference( 'abc' )
		);

		$this->assertStringContainsString(
			':ref:',
			$instance->getCacheKeyForCitationReference( 123 )
		);

		$this->assertStringContainsString(
			'foo',
			$instance->getCacheKeyForReferenceList( 'def' )
		);

		$this->assertStringContainsString(
			':reflist:',
			$instance->getCacheKeyForReferenceList( 456 )
		);
	}

}
