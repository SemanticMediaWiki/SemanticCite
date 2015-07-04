<?php

namespace SCI\Tests;

use SCI\ParserFunctionFactory;
use Title;
use Parser;
use ParserOptions;

/**
 * @covers \SCI\ParserFunctionFactory
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ParserFunctionFactoryTest extends \PHPUnit_Framework_TestCase {

	private $parser;

	protected function setUp() {
		parent::setUp();

		$this->parser = new Parser();
		$this->parser->Options( new ParserOptions() );
		$this->parser->clearState();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\ParserFunctionFactory',
			new ParserFunctionFactory()
		);
	}

	public function testNewSciteParserFunctionDefinition() {

		$this->parser->setTitle( Title::newFromText( __METHOD__ ) );

		$instance = new ParserFunctionFactory();

		$namespaceExaminer = $this->getMockBuilder( '\SMW\NamespaceExaminer' )
			->disableOriginalConstructor()
			->getMock();

		$options = $this->getMockBuilder( '\SCI\Options' )
			->disableOriginalConstructor()
			->getMock();

		list( $name, $definition, $flag ) = $instance->newSciteParserFunctionDefinition(
			$namespaceExaminer,
			$options
		);

		$this->assertEquals(
			'scite',
			$name
		);

		$this->assertInstanceOf(
			'\Closure',
			$definition
		);

		$text = '';

		$this->assertNotEmpty(
			call_user_func_array( $definition, array( $this->parser, $text ) )
		);
	}

	public function textNewReferenceListParserFunctionDefinition() {

		$this->parser->setTitle( Title::newFromText( __METHOD__ ) );

		$instance = new ParserFunctionFactory();

		list( $name, $definition, $flag ) = $instance->newReferenceListParserFunctionDefinition();

		$this->assertEquals(
			'referencelist',
			$name
		);

		$this->assertInstanceOf(
			'\Closure',
			$definition
		);

		$text = '';

		$this->assertNotEmpty(
			call_user_func_array( $definition, array( $this->parser, $text ) )
		);
	}

}
