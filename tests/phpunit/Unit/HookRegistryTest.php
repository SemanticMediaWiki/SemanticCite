<?php

namespace SCI\Tests;

use SCI\HookRegistry;
use SCI\Options;
use SMW\DataTypeRegistry;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMW\Tests\PHPUnitCompat;

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

	use PHPUnitCompat;

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

		$configuration = [
			'numberOfReferenceListColumns'       => 1,
			'browseLinkToCitationResource'       => false,
			'showTooltipForCitationReference'    => false,
			'tooltipRequestCacheTTL'             => false,
			'citationReferenceCaptionFormat'     => 1,
			'referenceListType'                  => 'ul',
			'enabledStrictParserValidation'      => true,
			'cachePrefix'                        => 'foo',
			'enabledCitationTextChangeUpdateJob' => false,
			'responsiveMonoColumnCharacterBoundLength' => 42
		];

		$instance = new HookRegistry(
			$store,
			$cache,
			new Options( $configuration )
		);

		$this->doTestRegisteredInitPropertiesHandler( $instance );
		$this->doTestRegisteredInitDataTypesHandler( $instance );
		$this->doTestRegisteredBeforeMagicWordsFinderHandler( $instance );
		$this->doTestRegisteredOutputPageParserOutput( $instance );
		$this->doTestRegisteredOutputPageBeforeHTML( $instance );
		$this->doTestRegisteredUpdateDataBefore( $instance );
		$this->doTestRegisteredAddCustomFixedPropertyTables( $instance );
		$this->doTestRegisteredResourceLoaderGetConfigVars( $instance );
		$this->doTestRegisteredParserFirstCallInit( $instance );
		$this->doTestRegisteredBeforePageDisplay( $instance );
		$this->doTestRegisteredBrowseAfterIncomingPropertiesLookupComplete( $instance );
		$this->doTestRegisteredBrowseBeforeIncomingPropertyValuesFurtherLinkCreate( $instance );
		$this->doTestRegisteredAfterDataUpdateComplete( $instance );
		$this->doTestRegisteredAfterDeleteSubjectComplete( $instance );
	}

	public function testInitExtension() {

		$vars = [];
		HookRegistry::initExtension( $vars );

		$propertyExemptionList = [
			'__sci_cite'
		];

		$config = [
			'smwgFulltextSearchPropertyExemptionList' => [],
			'smwgQueryDependencyPropertyExemptionList' => []
		];

		foreach ( $vars['wgHooks']['SMW::Config::BeforeCompletion'] as $callback ) {
			call_user_func_array( $callback, [ &$config ] );
		}

		$this->assertEquals(
			[
				'smwgFulltextSearchPropertyExemptionList' => $propertyExemptionList,
				'smwgQueryDependencyPropertyExemptionList' => $propertyExemptionList,
			],
			$config
		);
	}

	public function doTestRegisteredInitPropertiesHandler( $instance ) {

		$hook = 'SMW::Property::initProperties';

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ $propertyRegistry ]
		);
	}

	public function doTestRegisteredInitDataTypesHandler( $instance ) {

		$hook = 'SMW::DataType::initTypes';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$dataTypeRegistry = DataTypeRegistry::getInstance();

		if ( method_exists( $dataTypeRegistry, 'clearCallables' ) ) {
			 $dataTypeRegistry->clearCallables();
		}

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ $dataTypeRegistry ]
		);

		$this->assertEquals(
			'\SCI\DataValues\CitationReferenceValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_ref' )
		);

		$this->assertEquals(
			'\SCI\DataValues\ResourceIdentifierStringValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_doi' )
		);

		$this->assertEquals(
			'\SCI\DataValues\ResourceIdentifierStringValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_pmcid' )
		);

		$this->assertEquals(
			'\SCI\DataValues\ResourceIdentifierStringValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_pmid' )
		);

		$this->assertEquals(
			'\SCI\DataValues\ResourceIdentifierStringValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_viaf' )
		);

		$this->assertEquals(
			'\SCI\DataValues\ResourceIdentifierStringValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_oclc' )
		);

		$this->assertEquals(
			'\SCI\DataValues\ResourceIdentifierStringValue',
			$dataTypeRegistry->getDataTypeClassById( '_sci_olid' )
		);
	}

	public function doTestRegisteredBeforeMagicWordsFinderHandler( $instance ) {

		$hook = 'SMW::Parser::BeforeMagicWordsFinder';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$magicWords = [];

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ &$magicWords ]
		);
	}

	public function doTestRegisteredOutputPageParserOutput( $instance ) {

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
			[ &$outputPage, $parserOutput ]
		);
	}

	public function doTestRegisteredOutputPageBeforeHTML( $instance ) {

		$hook = 'OutputPageBeforeHTML';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$webRequest = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$language = $this->getMockBuilder( '\Language' )
			->disableOriginalConstructor()
			->getMock();

		$requestContext = $this->getMockBuilder( '\RequestContext' )
			->disableOriginalConstructor()
			->getMock();

		$requestContext->expects( $this->any() )
			->method( 'getRequest' )
			->will( $this->returnValue( $webRequest ) );

		$requestContext->expects( $this->any() )
			->method( 'getLanguage' )
			->will( $this->returnValue( $language ) );

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
			[ &$outputPage, &$text ]
		);
	}

	public function doTestRegisteredUpdateDataBefore( $instance ) {

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
			[ $store, $semanticData ]
		);
	}

	public function doTestRegisteredAddCustomFixedPropertyTables( $instance ) {

		$hook = 'SMW::SQLStore::AddCustomFixedPropertyTables';

		// Contains an extra to ensure previous values are not nullified
		$customFixedProperties = [ '_Foo' ];

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ &$customFixedProperties ]
		);

		$this->assertCount(
			11,
			$customFixedProperties
		);
	}

	public function doTestRegisteredResourceLoaderGetConfigVars( $instance ) {

		$hook = 'ResourceLoaderGetConfigVars';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$vars = [];

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ &$vars ]
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
			[ &$parser ]
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
			[ &$outputPage, &$skin ]
		);
	}

	public function doTestRegisteredBrowseAfterIncomingPropertiesLookupComplete( $instance ) {

		$hook = 'SMW::Browse::AfterIncomingPropertiesLookupComplete';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( DIWikiPage::newFromText( __METHOD__ ) ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ $store, $semanticData, null ]
		);
	}

	public function doTestRegisteredBrowseBeforeIncomingPropertyValuesFurtherLinkCreate( $instance ) {

		$hook = 'SMW::Browse::BeforeIncomingPropertyValuesFurtherLinkCreate';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$property = new DIProperty( 'Foo' );
		$subject = DIWikiPage::newFromText( __METHOD__ );

		$html = '';

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ $property, $subject, &$html ]
		);
	}

	public function doTestRegisteredAfterDataUpdateComplete( $instance ) {

		$hook = 'SMW::SQLStore::AfterDataUpdateComplete';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( DIWikiPage::newFromText( __METHOD__ ) ) );

		$store = $this->getMockBuilder( '\SMW\SQLStore\SQLStore' )
			->disableOriginalConstructor()
			->getMock();

		$compositePropertyTableDiffIterator = $this->getMockBuilder( '\SMW\SQLStore\CompositePropertyTableDiffIterator' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ $store, $semanticData, $compositePropertyTableDiffIterator ]
		);
	}

	public function doTestRegisteredAfterDeleteSubjectComplete( $instance ) {

		$hook = 'SMW::SQLStore::AfterDeleteSubjectComplete';

		$this->assertTrue(
			$instance->isRegistered( $hook )
		);

		$store = $this->getMockBuilder( '\SMW\SQLStore\SQLStore' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $hook ),
			[ $store, \Title::newFromText( 'Foo' ) ]
		);
	}

	private function assertThatHookIsExcutable( callable $handler, $arguments = [] ) {
		$this->assertInternalType(
			'boolean',
			call_user_func_array( $handler, $arguments )
		);
	}

}
