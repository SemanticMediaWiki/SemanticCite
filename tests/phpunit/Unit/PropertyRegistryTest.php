<?php

namespace SCI\Tests;

use SCI\PropertyRegistry;
use SMW\PropertyRegistry as CorePropertyRegistry;

/**
 * @covers \SCI\PropertyRegistry
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyRegistryTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\PropertyRegistry',
			new PropertyRegistry()
		);
	}

	/**
	 * @dataProvider propertyIdProvider
	 */
	public function testRegisterTo( $id, $label ) {

		$corePropertyRegistry = CorePropertyRegistry::getInstance();

		$instance = new PropertyRegistry();
		$instance->registerTo( $corePropertyRegistry );

		$this->assertNotEmpty(
			$corePropertyRegistry->findPropertyLabelById ( $id )
		);

		$this->assertSame(
			$label,
			$corePropertyRegistry->findPropertyLabelById ( $id )
		);
	}

	public function propertyIdProvider() {

		$provider[] = [
			PropertyRegistry::SCI_DOI,
			'DOI'
		];

		$provider[] = [
			PropertyRegistry::SCI_PMCID,
			'PMCID'
		];

		$provider[] = [
			PropertyRegistry::SCI_CITE_KEY,
			'Citation key'
		];

		$provider[] = [
			PropertyRegistry::SCI_CITE_REFERENCE,
			'Citation reference'
		];

		$provider[] = [
			PropertyRegistry::SCI_CITE_TEXT,
			'Citation text'
		];

		$provider[] = [
			PropertyRegistry::SCI_CITE,
			'Citation resource'
		];

		return $provider;
	}

}
