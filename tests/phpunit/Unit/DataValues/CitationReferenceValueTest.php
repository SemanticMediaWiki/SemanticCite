<?php

namespace SCI\Tests\DataValues;

use SCI\DataValues\CitationReferenceValue;
use SMW\DataTypeRegistry;

/**
 * @covers \SCI\DataValues\CitationReferenceValue
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CitationReferenceValueTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\DataValues\CitationReferenceValue',
			new CitationReferenceValue()
		);
	}

	public function testExtraneousCallbackFunctionThrowsException() {

		$instance = new CitationReferenceValue();

		$this->setExpectedException( 'RuntimeException' );
		$instance->getExtraneousFunctionFor( 'bar' );
	}

	public function testNoValueCreatesError() {

		$citationReferencePositionJournal = $this->getMockBuilder( '\SCI\CitationReferencePositionJournal' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new CitationReferenceValue();

		$instance->setExtraneousFunctions(
			array(
				'\SCI\CitationReferencePositionJournal' => function() use ( $citationReferencePositionJournal ) {
					return $citationReferencePositionJournal;
				}
			)
		);

		$instance->setUserValue( '' );

		$this->assertNotEmpty(
			$instance->getErrors()
		);
	}

}
