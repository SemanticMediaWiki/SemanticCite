<?php

namespace SCI\Tests\Specials;

/**
 * @covers \SCI\Specials\SpecialFindCitableMetadata
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class SpecialFindCitableMetadataTest extends \PHPUnit\Framework\TestCase {

	private $request;
	private $config;
	private $message;
	private $user;
	private $outputPage;
	private $context;

	protected function setUp(): void {
		$this->request = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->config = $this->getMockBuilder( '\Config' )
			->disableOriginalConstructor()
			->getMock();

		$this->message = $this->getMockBuilder( '\Message' )
			->disableOriginalConstructor()
			->getMock();

		$this->user = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$this->outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$this->context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @dataProvider variableProvider
	 */
	public function testCanExecute( $requestValues, $queryString ) {
		$this->request->expects( $this->once() )
			->method( 'getValues' )
			->willReturn( $requestValues );

		$this->context->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( \MediaWiki\Title\Title::newFromText( __METHOD__ ) );

		$SpecialFindCitableMetadata = $this->getMockBuilder( '\SCI\Specials\SpecialFindCitableMetadata' )
			->disableOriginalConstructor()
			->onlyMethods( [
				'getContext',
				'getOutput',
				'msg',
				'getConfig',
				'getRequest',
				'getUser',
				'userCanExecute' ]
			)
			->getMock();

		$SpecialFindCitableMetadata->expects( $this->any() )
			->method( 'getContext' )
			->willReturn( $this->context );

		$SpecialFindCitableMetadata->expects( $this->any() )
			->method( 'getOutput' )
			->willReturn( $this->outputPage );

		$SpecialFindCitableMetadata->expects( $this->any() )
			->method( 'getUser' )
			->willReturn( $this->user );

		$SpecialFindCitableMetadata->expects( $this->any() )
			->method( 'msg' )
			->willReturn( $this->message );

		$SpecialFindCitableMetadata->expects( $this->any() )
			->method( 'getConfig' )
			->willReturn( $this->config );

		$SpecialFindCitableMetadata->expects( $this->any() )
			->method( 'getRequest' )
			->willReturn( $this->request );

		$SpecialFindCitableMetadata->expects( $this->once() )
			->method( 'userCanExecute' )
			->willReturn( true );

		$SpecialFindCitableMetadata->execute( $queryString );
	}

	public function variableProvider() {
		$provider[] = [
			[],
			''
		];

		$provider[] = [
			[],
			'DOI/10.123'
		];

		return $provider;
	}

}
