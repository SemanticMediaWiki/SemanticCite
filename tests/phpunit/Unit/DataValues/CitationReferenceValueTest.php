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
class CitationReferenceValueTest extends \PHPUnit\Framework\TestCase {

	private $dataValueServiceFactory;

	protected function setUp() : void {
		parent::setUp();

		$constraintValueValidator = $this->getMockBuilder( '\SMW\DataValues\ValueValidators\ConstraintValueValidator' )
			->disableOriginalConstructor()
			->getMock();

		$this->dataValueServiceFactory = $this->getMockBuilder( '\SMW\Services\DataValueServiceFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->dataValueServiceFactory->expects( $this->any() )
			->method( 'getConstraintValueValidator' )
			->will( $this->returnValue( $constraintValueValidator ) );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\DataValues\CitationReferenceValue',
			new CitationReferenceValue()
		);
	}

	public function testNoValueCreatesError() {

		$citationReferencePositionJournal = $this->getMockBuilder( '\SCI\CitationReferencePositionJournal' )
			->disableOriginalConstructor()
			->getMock();

		$callback = function() use ( $citationReferencePositionJournal ) {
			return $citationReferencePositionJournal;
		};

		$instance = new CitationReferenceValue();
		$instance->addCallable( 'sci.citationreferencepositionjournal', $callback );

		$instance->setDataValueServiceFactory(
			$this->dataValueServiceFactory
		);

		$instance->setUserValue( '' );

		$this->assertNotEmpty(
			$instance->getErrors()
		);
	}

}
