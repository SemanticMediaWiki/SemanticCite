<?php

namespace SCI\Tests\Metadata;

use SCI\Metadata\NullResponseContentParser;

/**
 * @covers \SCI\Metadata\NullResponseContentParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class NullResponseContentParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\Metadata\NullResponseContentParser',
			new NullResponseContentParser()
		);
	}

	public function testMethodAccess() {

		$instance = new NullResponseContentParser();

		$this->assertNull(
			$instance->getRawResponse( 42 )
		);

		$this->assertNull(
			$instance->doParseFor( 42 )
		);

		$this->assertNull(
			$instance->getFilteredMetadataRecord()
		);

		$this->assertEmpty(
			$instance->getMessages()
		);

		$this->assertFalse(
			$instance->isSuccess()
		);

		$this->assertFalse(
			$instance->usedCache()
		);
	}

}
