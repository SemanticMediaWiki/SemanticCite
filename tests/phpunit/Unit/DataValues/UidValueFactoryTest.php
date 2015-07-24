<?php

namespace SCI\Tests\DataValues;

use SCI\DataValues\UidValueFactory;

/**
 * @covers \SCI\DataValues\UidValueFactory
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class UidValueFactoryTest extends \PHPUnit_Framework_TestCase {


	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\DataValues\UidValueFactory',
			new UidValueFactory()
		);
	}

	public function testUnknownTypeMatchThrowsException() {

		$instance = new UidValueFactory();

		$this->setExpectedException( 'RuntimeException' );
		$instance->newUidValueForType( 'foo' );
	}

	/**
	 * @dataProvider typeProvider
	 */
	public function testUidValueForType( $type ) {

		$instance = new UidValueFactory();

		$this->assertInstanceOf(
			'\SCI\DataValues\UidValue',
			$instance->newUidValueForType( $type )
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
