<?php

namespace SCI\Tests;

use SCI\HookRegistry;
use SCI\Options;
use SMW\DataTypeRegistry;
use SMW\DIWikiPage;

/**
 * @covers \SCI\HookRegistry
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$options = $this->getMockBuilder( '\SCI\Options' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\HookRegistry',
			new HookRegistry( $store, $cache, $options )
		);
	}

	public function testRegister() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$configuration = array(
			'numberOfReferenceListColumns'     => 1,
			'browseLinkToCitationResource'     => false,
			'showTooltipForCitationReference'  => false,
			'tooltipRequestCacheTTL'           => false,
			'citationReferenceCaptionFormat'   => 1,
			'referenceListType'                => 'ul',
			'strictParserValidationEnabled'    => true,
			'cachePrefix'                      => 'foo'
		);

		$instance = new HookRegistry(
			$store,
			$cache,
			new Options( $configuration )
		);

		$this->doTestRegistereddInitPropertiesHandler( $instance );
		$this->doTestRegistereddInitDataTypesHandler( $instance );
		$this->doTestRegistereddBeforeMagicWordsFinderHandler( $instance );
		$this->doTestRegistereddOutputPageParserOutput( $instance );
		$this->doTestRegistereddOutputPageBeforeHTML( $instance );
		$this->doTestRegistereddUpdateDataBefore( $instance );
		$this->doTestRegistereddAddCustomFixedPropertyTables( $instance );
		$this->doTestRegisteredResourceLoaderGetConfigVars( $instance );
		$this->doTestRegisteredParserFirstCallInit( $instance );
		$this->doTestRegisteredBeforePageDisplay( $instance );
	}

	public function doTestRegistereddInitPropertiesHandler( $instance ) {

		$hook = 'SMW::Property::initProperties';

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( $propertyRegistry )
		);
	}

	public function doTestRegistereddInitDataTypesHandler( $instance ) {

		$hook = 'SMW::DataType::initTypes';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$dataTypeRegistry = DataTypeRegistry::getInstance();

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( $dataTypeRegistry )
		);

		$this->assertEquals(
			'\SCI\DataValues\CitationReferenceValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_ref' )
		);

		$this->assertEquals(
			'\SCI\DataValues\DoiValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_doi' )
		);

		$this->assertEquals(
			'\SCI\DataValues\PmcidValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_pmcid' )
		);
	}

	public function doTestRegistereddBeforeMagicWordsFinderHandler( $instance ) {

		$hook = 'SMW::Parser::BeforeMagicWordsFinder';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$magicWords = array();

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( &$magicWords )
		);
	}

	public function doTestRegistereddOutputPageParserOutput( $instance ) {

		$hook = 'OutputPageParserOutput';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( &$outputPage, $parserOutput )
		);
	}

	public function doTestRegistereddOutputPageBeforeHTML( $instance ) {

		$hook = 'OutputPageBeforeHTML';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$webRequest = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$requestContext = $this->getMockBuilder( '\RequestContext' )
			->disableOriginalConstructor()
			->getMock();

		$requestContext->expects( $this->any() )
			->method( 'getRequest' )
			->will( $this->returnValue( $webRequest ) );

		$requestContext->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( 'Foo' ) ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->any() )
			->method( 'getContext' )
			->will( $this->returnValue( $requestContext ) );

		$text = '';

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( &$outputPage, &$text )
		);
	}

	public function doTestRegistereddUpdateDataBefore( $instance ) {

		$hook = 'SMWStore::updateDataBefore';

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->any() )
			->method( 'getSubject' )
			->will( $this->returnValue( new DIWikiPage( 'Foo', NS_MAIN ) ) );

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( $store, $semanticData )
		);
	}

	public function doTestRegistereddAddCustomFixedPropertyTables( $instance ) {

		$hook = 'SMW::SQLStore::AddCustomFixedPropertyTables';

		$customFixedProperties = array();

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( &$customFixedProperties )
		);
	}

	public function doTestRegisteredResourceLoaderGetConfigVars( $instance ) {

		$hook = 'ResourceLoaderGetConfigVars';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$vars = array();

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( &$vars )
		);
	}

	public function doTestRegisteredParserFirstCallInit( $instance ) {

		$hook = 'ParserFirstCallInit';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( &$parser )
		);
	}

	public function doTestRegisteredBeforePageDisplay( $instance ) {

		$hook = 'BeforePageDisplay';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( 'Foo' ) ) );

		$skin = $this->getMockBuilder( '\Skin' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			array( &$outputPage, &$skin )
		);
	}

	private function assertThatHookIsExcutable( \Closure $handler, $arguments = array() ) {
		$this->assertInternalType(
			'boolean',
			call_user_func_array( $handler, $arguments )
		);
	}

}
