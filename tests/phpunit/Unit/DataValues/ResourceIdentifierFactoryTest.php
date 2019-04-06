<?php

namespace SCI\Tests\DataValues;

use SCI\DataValues\ResourceIdentifierFactory;
use SCI\Tests\PHPUnitCompat;

/**
 * @covers \SCI\DataValues\ResourceIdentifierFactory
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ResourceIdentifierFactoryTest extends \PHPUnit_Framework_TestCase {

	use PHPUnitCompat;

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\DataValues\ResourceIdentifierFactory',
			new ResourceIdentifierFactory()
		);
	}

	public function testUnknownTypeMatchThrowsException() {

		$instance = new ResourceIdentifierFactory();

		$this->setExpectedException( 'RuntimeException' );
		$instance->newResourceIdentifierStringValueForType( 'foo' );
	}

	/**
	 * @dataProvider typeProvider
	 */
	public function testUidValueForType( $type ) {

		$instance = new ResourceIdentifierFactory();

		$this->assertInstanceOf(
			'\SCI\DataValues\ResourceIdentifierStringValue',
			$instance->newResourceIdentifierStringValueForType( $type )
		);
	}

	public function typeProvider() {

		$provider[] = [
			'DOI'
		];

		$provider[] = [
			'VIAF'
		];

		$provider[] = [
			'OCLC'
		];

		$provider[] = [
			'OLID'
		];

		$provider[] = [
			'PMCID'
		];

		$provider[] = [
			'PMID'
		];

		$provider[] = [
			'pubmed'
		];

		$provider[] = [
			'pmc'
		];

		return $provider;
	}

}
