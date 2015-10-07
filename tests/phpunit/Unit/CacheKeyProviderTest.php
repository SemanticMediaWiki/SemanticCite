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
class CacheKeyProviderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\CacheKeyProvider',
			new CacheKeyProvider()
		);
	}

	public function testGetKey() {

		$instance = new CacheKeyProvider();
		$instance->setCachePrefix( 'foo' );

		$this->assertContains(
			'foo',
			$instance->getCacheKeyForCitationReference( 'abc' )
		);

		$this->assertContains(
			':ref:',
			$instance->getCacheKeyForCitationReference( 123 )
		);

		$this->assertContains(
			'foo',
			$instance->getCacheKeyForReferenceList( 'def' )
		);

		$this->assertContains(
			':reflist:',
			$instance->getCacheKeyForReferenceList( 456 )
		);
	}

}
