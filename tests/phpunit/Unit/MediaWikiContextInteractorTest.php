<?php

namespace SCI\Tests;

use MediaWiki\Context\IContextSource;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;
use SCI\MediaWikiContextInteractor;

/**
 * @covers \SCI\MediaWikiContextInteractor
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since   1.0
 *
 * @author mwjames
 * @reviewer thomas-topway-it
 */
class MediaWikiContextInteractorTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\MediaWikiContextInteractor',
			new MediaWikiContextInteractor( $context )
		);
	}

	public function testHasAction() {
		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->any() )
			->method( 'getActionName' )
			->willReturn( 'view' );

		$instance = new MediaWikiContextInteractor( $context );

		$this->assertTrue(
			$instance->hasAction( 'view' )
		);
	}

	/**
	 * @dataProvider oldidDirectionProvider
	 */
	public function testGetOldId( $direction ) {
		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$webRequest = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$webRequest->expects( $this->any() )
			->method( 'getText' )
			->willReturn( $direction );

		$context->expects( $this->any() )
			->method( 'getRequest' )
			->willReturn( $webRequest );

		$context->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( Title::newFromText( __METHOD__ ) );

		$instance = new MediaWikiContextInteractor( $context );

		$this->assertIsInt(
			$instance->getOldId()
		);
	}

	public function oldidDirectionProvider() {
		$provider = [];

		$provider[] = [
			'next'
		];

		$provider[] = [
			'prev'
		];

		$provider[] = [
			'cur'
		];

		return $provider;
	}

	public function testHasMagicWordFromOutputPageMetadata() {
		$instance = new MediaWikiContextInteractor(
			$this->newContextWithMagicWords( [ 'FOO', 'SCI_NOREFERENCELIST', 'BAR' ] )
		);

		$this->assertTrue(
			$instance->hasMagicWord( 'SCI_NOREFERENCELIST' )
		);
	}

	public function testHasNoMagicWordMissingFromOutputPageMetadata() {
		$instance = new MediaWikiContextInteractor(
			$this->newContextWithMagicWords( [ 'FOO', 'BAR' ] )
		);

		$this->assertFalse(
			$instance->hasMagicWord( 'SCI_NOREFERENCELIST' )
		);
	}

	public function testHasNoMagicWordOnPageWithoutMagicWords() {
		$instance = new MediaWikiContextInteractor(
			$this->newContextWithMagicWords( null )
		);

		$this->assertFalse(
			$instance->hasMagicWord( 'SCI_NOREFERENCELIST' )
		);
	}

	private function newContextWithMagicWords( ?array $magicWords ): IContextSource {
		$metadata = new ParserOutput();
		$metadata->setExtensionData( 'smwmagicwords', $magicWords );

		$outputPage = $this->createStub( OutputPage::class );
		$outputPage->method( 'getMetadata' )->willReturn( $metadata );

		$context = $this->createStub( IContextSource::class );
		$context->method( 'getOutput' )->willReturn( $outputPage );

		return $context;
	}

}
