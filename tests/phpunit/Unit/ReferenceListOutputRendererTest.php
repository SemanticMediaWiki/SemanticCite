<?php

namespace SCI\Tests;

use SCI\ReferenceListOutputRenderer;
use SMW\DIWikiPage;

/**
 * @covers \SCI\ReferenceListOutputRenderer
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ReferenceListOutputRendererTest extends \PHPUnit_Framework_TestCase {

	private $citationResourceMatchFinder;
	private $citationReferencePositionJournal;
	private $htmlColumnListRenderer;

	protected function setUp() {

		$this->citationResourceMatchFinder = $this->getMockBuilder( '\SCI\CitationResourceMatchFinder' )
			->disableOriginalConstructor()
			->getMock();

		$this->citationReferencePositionJournal = $this->getMockBuilder( '\SCI\CitationReferencePositionJournal' )
			->disableOriginalConstructor()
			->getMock();

		$this->htmlColumnListRenderer = $this->getMockBuilder( '\SMW\MediaWiki\Renderer\HtmlColumnListRenderer' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\ReferenceListOutputRenderer',
			new ReferenceListOutputRenderer(
				$this->citationResourceMatchFinder,
				$this->citationReferencePositionJournal,
				$this->htmlColumnListRenderer
			)
		);
	}

	public function testSetterGetterForExternalUse() {

		$instance =	new ReferenceListOutputRenderer(
			$this->citationResourceMatchFinder,
			$this->citationReferencePositionJournal,
			$this->htmlColumnListRenderer
		);

		$instance->setNumberOfReferenceListColumns( 4 );

		$this->assertEquals(
			4,
			$instance->getNumberOfReferenceListColumns()
		);

		$instance->setReferenceListType( 'ol' );

		$this->assertEquals(
			'ol',
			$instance->getReferenceListType()
		);

		$instance->setBrowseLinkToCitationResourceState( true );

		$this->assertEquals(
			true,
			$instance->getBrowseLinkToCitationResourceState()
		);
	}

	public function testRenderReferenceListForIncompleteJournal() {

		$this->citationReferencePositionJournal->expects( $this->once() )
			->method( 'getJournalBySubject' )
			->will( $this->returnValue( array(
				'reference-pos'  => array( 'abc' => array() ),
				'reference-list' => array( 'abc' => 123 ) ) ) );

		$this->citationResourceMatchFinder->expects( $this->once() )
			->method( 'findCitationTextFor' )
			->will( $this->returnValue( array( array(), '' ) ) );

		$instance =	new ReferenceListOutputRenderer(
			$this->citationResourceMatchFinder,
			$this->citationReferencePositionJournal,
			$this->htmlColumnListRenderer
		);

		$instance->setNumberOfReferenceListColumns( 0 );
		$instance->setBrowseLinkToCitationResourceState( true );

		$this->assertInternalType(
			'string',
			$instance->doRenderReferenceListFor( DIWikiPage::newFromText( 'Foo' ) )
		);
	}

}
