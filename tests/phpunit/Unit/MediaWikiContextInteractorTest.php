<?php

namespace SCI\Tests;

use SCI\MediaWikiContextInteractor;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SCI\MediaWikiContextInteractor
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 * @reviewer thomas-topway-it
 */
class MediaWikiContextInteractorTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

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
			->willReturn( 'view');

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
			->will( $this->returnValue( $direction ) );

		$context->expects( $this->any() )
			->method( 'getRequest' )
			->will( $this->returnValue( $webRequest ) );

		$context->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( __METHOD__ ) ) );

		$instance = new MediaWikiContextInteractor( $context );

		$this->assertInternalType(
			'integer',
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

}
