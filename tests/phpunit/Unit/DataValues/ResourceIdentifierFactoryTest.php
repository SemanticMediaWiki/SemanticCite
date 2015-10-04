<?php

namespace SCI\Tests\DataValues;

use SCI\DataValues\ResourceIdentifierFactory;

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

		$provider[] = array(
			'DOI'
		);

		$provider[] = array(
			'VIAF'
		);

		$provider[] = array(
			'OCLC'
		);

		$provider[] = array(
			'OLID'
		);

		$provider[] = array(
			'PMCID'
		);

		$provider[] = array(
			'PMID'
		);

		$provider[] = array(
			'pubmed'
		);

		$provider[] = array(
			'pmc'
		);

		return $provider;
	}

}
