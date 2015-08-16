<?php

namespace SCI\Tests;

use SCI\CitationTextTemplateRenderer;

/**
 * @covers \SCI\CitationTextTemplateRenderer
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class CitationTextTemplateRendererTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$wikitextTemplateRenderer = $this->getMockBuilder( '\SMW\MediaWiki\Renderer\WikitextTemplateRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\CitationTextTemplateRenderer',
			new CitationTextTemplateRenderer( $wikitextTemplateRenderer, $parser )
		);
	}

}
