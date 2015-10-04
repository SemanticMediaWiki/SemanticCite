<?php

namespace SCI\Tests;

use SCI\CitationResourceMatchFinder;

/**
 * @covers \SCI\CitationResourceMatchFinder
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class CitationResourceMatchFinderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SCI\CitationResourceMatchFinder',
			new CitationResourceMatchFinder( $store )
		);
	}

	/**
	 * @dataProvider uidTypeProvider
	 */
	public function testFindMatchForUidTypeOf( $key, $id ) {

		$queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getQueryResult' )
			->will( $this->returnValue( $queryResult ) );

		$instance = new CitationResourceMatchFinder( $store );

		$instance->findMatchForResourceIdentifierTypeToValue(
			$key,
			$id
		);
	}

	public function uidTypeProvider() {

		$provider[] = array(
			'oclc',
			42
		);

		$provider[] = array(
			'viaf',
			42
		);

		$provider[] = array(
			'doi',
			42
		);

		$provider[] = array(
			'pmid',
			42
		);

		$provider[] = array(
			'pmcid',
			42
		);

		$provider[] = array(
			'olid',
			42
		);

		return $provider;
	}

}
