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
class PropertyRegistryTest extends \PHPUnit_Framework_TestCase {

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

		$provider[] = array(
			PropertyRegistry::SCI_DOI,
			'DOI'
		);

		$provider[] = array(
			PropertyRegistry::SCI_PMCID,
			'PMCID'
		);

		$provider[] = array(
			PropertyRegistry::SCI_CITE_KEY,
			'Citation key'
		);

		$provider[] = array(
			PropertyRegistry::SCI_CITE_REFERENCE,
			'Citation reference'
		);

		$provider[] = array(
			PropertyRegistry::SCI_CITE_TEXT,
			'Citation text'
		);

		$provider[] = array(
			PropertyRegistry::SCI_CITE,
			'Citation resource'
		);

		return $provider;
	}

}
