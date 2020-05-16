<?php

namespace SCI\Tests;

use SCI\ReferenceListOutputRenderer;
use SMW\DIWikiPage;
use SMW\Tests\PHPUnitCompat;

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

	use PHPUnitCompat;

	private $citationResourceMatchFinder;
	private $citationReferencePositionJournal;
	private $htmlColumnListRenderer;

	protected function setUp() : void {

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

		$instance->setBrowseLinkToCitationResourceVisibility( true );

		$this->assertEquals(
			true,
			$instance->getBrowseLinkToCitationResourceVisibility()
		);
	}

	public function testRenderReferenceListForIncompleteJournal() {

		$this->citationReferencePositionJournal->expects( $this->once() )
			->method( 'getJournalBySubject' )
			->will( $this->returnValue( [
				'reference-pos'  => [ 'abc' => [] ],
				'reference-list' => [ 'abc' => 123 ] ] ) );

		$this->citationResourceMatchFinder->expects( $this->once() )
			->method( 'findCitationTextFor' )
			->will( $this->returnValue( [ [], '' ] ) );

		$instance =	new ReferenceListOutputRenderer(
			$this->citationResourceMatchFinder,
			$this->citationReferencePositionJournal,
			$this->htmlColumnListRenderer
		);

		$instance->setNumberOfReferenceListColumns( 0 );
		$instance->setResponsiveMonoColumnCharacterBoundLength( 100 );
		$instance->setBrowseLinkToCitationResourceVisibility( true );

		$this->assertInternalType(
			'string',
			$instance->doRenderReferenceListFor( DIWikiPage::newFromText( 'Foo' ) )
		);
	}

}
