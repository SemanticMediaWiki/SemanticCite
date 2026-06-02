<?php

namespace SCI\Tests;

use SCI\PreTextFormatter;

/**
 * @covers \SCI\PreTextFormatter
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since 1.4
 *
 * @author mwjames
 */
class PreTextFormatterTest extends \PHPUnit\Framework\TestCase {

	const LF = "\n";

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SCI\PreTextFormatter',
			new PreTextFormatter()
		);
	}

	/**
	 * @dataProvider parametersProvider
	 */
	public function testFormat( $params ) {
		$instance = new PreTextFormatter();

		$this->assertIsArray(
			$instance->format( $params )
		);
	}

	/**
	 * @dataProvider parametersProvider
	 */
	public function testGetFormattedSciteFuncFrom( $params, $expected ) {
		$instance = new PreTextFormatter();

		$this->assertSame(
			$expected,
			$instance->getFormattedSciteFuncFrom( $params )
		);
	}

	public function parametersProvider() {
		$provider[] = [
			[
				'Bar',
				'@Foobar'
			],
			"<pre>{{#scite:" . self::LF . " |Bar" . self::LF . "}}</pre>"
		];

		$provider[] = [
			[
				'Bar',
				'+sep=,',
				'@Foobar',
				'Foo'
			],
			"<pre>{{#scite:" . self::LF . " |Bar|+sep=," . self::LF . " |Foo" . self::LF . "}}</pre>"
		];

		return $provider;
	}

}
