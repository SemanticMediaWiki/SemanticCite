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
class ParserFunctionFactoryTest extends \PHPUnit\Framework\TestCase {

	private $parser;

	protected function setUp() : void {
		parent::setUp();

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'getNamespace' )
			->will( $this->returnValue( NS_MAIN ) );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$this->parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$this->parser->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->parser->expects( $this->any() )
			->method( 'getOutput' )
			->will( $this->returnValue( $parserOutput ) );
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
			call_user_func_array( $definition, [ $this->parser, $text ] )
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
			call_user_func_array( $definition, [ $this->parser, $text ] )
		);
	}

}
