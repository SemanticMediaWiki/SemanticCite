<?php

namespace SCI\Tests;

use SCI\Options;

/**
 * @covers \SCI\Options
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since   1.0
 *
 * @author mwjames
 */
class OptionsTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SCI\Options',
			new Options()
		);
	}

	public function testAddOption() {
		$instance = new Options();

		$this->assertFalse(
			$instance->has( 'Foo' )
		);

		$instance->set( 'Foo', 42 );

		$this->assertEquals(
			42,
			$instance->get( 'Foo' )
		);
	}

	public function testUnregisteredKeyThrowsException() {
		$instance = new Options();

		$this->expectException( 'InvalidArgumentException' );
		$instance->get( 'Foo' );
	}

}
