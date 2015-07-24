<?php

namespace SCI\Tests\Metadata\Search;

use SCI\Metadata\Search\SpecialFindMetadataById;

/**
 * @covers \SCI\Metadata\Search\SpecialFindMetadataById
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SpecialFindMetadataByIdTest extends \PHPUnit_Framework_TestCase {

	private $request;
	private $config;
	private $message;
	private $user;
	private $outputPage;
	private $context;

	protected function setUp() {

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
			->will( $this->returnValue( $requestValues ) );

		$this->context->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( __METHOD__ ) ) );

		$specialFindMetadataById = $this->getMockBuilder( '\SCI\Metadata\Search\SpecialFindMetadataById' )
			->disableOriginalConstructor()
			->setMethods( array(
				'getContext',
				'getOutput',
				'msg',
				'getConfig',
				'getRequest',
				'getUser',
				'userCanExecute' )
			)
			->getMock();

		$specialFindMetadataById->expects( $this->any() )
			->method( 'getContext' )
			->will( $this->returnValue( $this->context ) );

		$specialFindMetadataById->expects( $this->any() )
			->method( 'getOutput' )
			->will( $this->returnValue( $this->outputPage ) );

		$specialFindMetadataById->expects( $this->any() )
			->method( 'getUser' )
			->will( $this->returnValue( $this->user ) );

		$specialFindMetadataById->expects( $this->any() )
			->method( 'msg' )
			->will( $this->returnValue( $this->message ) );

		$specialFindMetadataById->expects( $this->any() )
			->method( 'getConfig' )
			->will( $this->returnValue( $this->config ) );

		$specialFindMetadataById->expects( $this->any() )
			->method( 'getRequest' )
			->will( $this->returnValue( $this->request ) );

		$specialFindMetadataById->expects( $this->once() )
			->method( 'userCanExecute' )
			->will( $this->returnValue( true ) );

		$specialFindMetadataById->execute( $queryString );
	}

	public function variableProvider() {

		$provider[] = array(
			array(),
			''
		);

		$provider[] = array(
			array(),
			'DOI/10.123'
		);

		return $provider;
	}

}
