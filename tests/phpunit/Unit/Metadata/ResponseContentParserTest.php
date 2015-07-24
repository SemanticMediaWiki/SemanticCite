<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\ResponseContentParser;

/**
 * @covers \SCI\Metadata\ResponseContentParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class ResponseContentParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$responseContentParser = $this->getMockBuilder( '\SCI\Metadata\ResponseContentParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SCI\Metadata\ResponseContentParser',
			$responseContentParser
		);
	}

	public function testAddMessage() {

		$responseContentParser = $this->getMockBuilder( '\SCI\Metadata\ResponseContentParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$responseContentParser->addMessage( 'Foo' );
		$responseContentParser->addMessage( array( 'sci-foo' ) );
		$responseContentParser->addMessage( array( 'Bar' ) );

		$expected = array(
			'Foo',
			'&lt;sci-foo&gt;',
			'Bar'
		);

		$this->assertEquals(
			$expected,
			$responseContentParser->getMessages()
		);
	}

}
