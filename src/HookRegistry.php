<?php

namespace SCI;

use SMW\Store;
use Onoi\Cache\Cache;
use SMW\DataTypeRegistry;
use SMW\DataValueFactory;
use SMW\CitationMatchFinder;
use SMW\ApplicationFactory;
use SMW\DIWikiPage;
use SMWDataItem as DataItem;
use Hooks;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistry {

	/**
	 * @var array
	 */
	private $handlers = array();

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 * @param Cache $cache
	 * @param array $configuration
	 */
	public function __construct( Store $store, Cache $cache, $configuration ) {
		$this->addCallbackHandlers(
			$store,
			$cache,
			$configuration
		);
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function isRegistered( $name ) {
		return Hooks::isRegistered( $name );
	}

	/**
	 * @since  1.0
	 */
	public function clear() {
		foreach ( $this->handlers as $name => $callback ) {
			Hooks::clear( $name );
		}
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return Callable|false
	 */
	public function getHandlerFor( $name ) {
		return isset( $this->handlers[$name] ) ? $this->handlers[$name] : false;
	}

	/**
	 * @since  1.0
	 */
	public function register() {
		foreach ( $this->handlers as $name => $callback ) {
			Hooks::register( $name, $callback );
		}
	}

	private function addCallbackHandlers( $store, $cache, $configuration ) {

		$propertyRegistry = new PropertyRegistry();
		$namespaceExaminer = ApplicationFactory::getInstance()->getNamespaceExaminer();

		$cacheKeyGenerator = new CacheKeyGenerator();
		$cacheKeyGenerator->setCachePrefix( $configuration['cachePrefix'] );

		$citationReferencePositionJournal = new CitationReferencePositionJournal(
			$cache,
			$cacheKeyGenerator
		);

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Property::initProperties
		 */
		$this->handlers['SMW::Property::initProperties'] = function ( $corePropertyRegistry ) use ( $propertyRegistry ) {
			return $propertyRegistry->registerTo( $corePropertyRegistry );
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::DataType::initTypes
		 */
		$this->handlers['SMW::DataType::initTypes'] = function ( $dataTypeRegistry ) use( $configuration, $citationReferencePositionJournal ) {

			$dataTypeRegistry->registerDatatype(
				'_sci_ref',
				'\SCI\DataValues\CitationReferenceValue',
				DataItem::TYPE_BLOB
			);

			$dataTypeRegistry->registerDatatype(
				'_sci_doi',
				'\SCI\DataValues\DoiValue',
				DataItem::TYPE_BLOB
			);

			$dataTypeRegistry->registerDatatype(
				'_sci_pmcid',
				'\SCI\DataValues\PmcidValue',
				DataItem::TYPE_BLOB
			);

			$dataTypeRegistry->registerExtraneousFunction(
				'CitationReferencePositionJournal',
				function() use( $citationReferencePositionJournal ) { return $citationReferencePositionJournal; }
			);

			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Parser::BeforeMagicWordsFinder
		 */
		$this->handlers['SMW::Parser::BeforeMagicWordsFinder'] = function( array &$magicWords ) {
			$magicWords = array_merge( $magicWords, array( 'SCI_NOREFERENCELIST' ) );
			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::SQLStore::AddCustomFixedPropertyTables
		 */
		$this->handlers['SMW::SQLStore::AddCustomFixedPropertyTables'] = function( &$customFixedProperties ) use( $propertyRegistry ) {

			$customFixedProperties = array();

			$properties = array(
				$propertyRegistry::SCI_CITE_KEY,
				$propertyRegistry::SCI_CITE_REFERENCE,
				$propertyRegistry::SCI_CITE_TEXT,
				$propertyRegistry::SCI_CITE,
				$propertyRegistry::SCI_DOI,
				$propertyRegistry::SCI_PMCID
			);

			foreach ( $properties as $property ) {
				$customFixedProperties[$property] = str_replace( '__', '_', $property );
			}

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
		 */
		$this->handlers['BeforePageDisplay'] = function ( &$outputPage, &$skin ) use( $namespaceExaminer ) {

			if ( !$namespaceExaminer->isSemanticEnabled( $outputPage->getTitle()->getNamespace() ) ) {
				return true;
			}

			$outputPage->addModules(
				array(
					'ext.scite.styles',
					'ext.scite.tooltip'
				)
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
		 */
		$this->handlers['OutputPageParserOutput'] = function( &$outputPage, $parserOutput ) {
			// This is bit of a crux but there is no other way to get access to
			// the ParserOutput so that it can be used in OutputPageBeforeHTML
			$outputPage->smwmagicwords = $parserOutput->getExtensionData( 'smwmagicwords' );
			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageBeforeHTML
		 */
		$this->handlers['OutputPageBeforeHTML'] = function( &$outputPage, &$text ) use ( $store, $namespaceExaminer, $citationReferencePositionJournal, $cache, $cacheKeyGenerator, $configuration ) {

			$referenceListFactory = new ReferenceListFactory(
				$store,
				$namespaceExaminer,
				$citationReferencePositionJournal
			);

			$cachedReferenceListOutputRenderer = $referenceListFactory->newCachedReferenceListOutputRenderer(
				new MediaWikiContextInteractor( $outputPage->getContext() ),
				$cache,
				$cacheKeyGenerator,
				$GLOBALS['wgParser'],
				$configuration
			);

			$cachedReferenceListOutputRenderer->addReferenceListToText( $text );

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticlePurge
		 */
		$this->handlers['SMWStore::updateDataBefore'] = function ( $store, $semanticData ) use ( $cache, $cacheKeyGenerator ) {

			$hash = $semanticData->getSubject()->getHash();

			$cache->delete(
				$cacheKeyGenerator->getCacheKeyForCitationReference( $hash )
			);

			$cache->delete(
				$cacheKeyGenerator->getCacheKeyForReferenceList( $hash )
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
		 */
		$this->handlers['ResourceLoaderGetConfigVars'] = function ( &$vars ) use ( $configuration ) {

			$vars['scite-config'] = array(
				'showTooltipForCitationReference' => $configuration['showTooltipForCitationReference'],
				'tooltipRequestCacheTTL' => $configuration['tooltipRequestCacheTTL']
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
		 */
		$this->handlers['ParserFirstCallInit'] = function ( &$parser ) use ( $namespaceExaminer, $configuration ) {

			$parserFunctionFactory = new ParserFunctionFactory();

			list( $name, $definition, $flag ) = $parserFunctionFactory->newSciteParserFunctionDefinition(
				$namespaceExaminer,
				$configuration
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			list( $name, $definition, $flag ) = $parserFunctionFactory->newReferenceListParserFunctionDefinition();

			$parser->setFunctionHook( $name, $definition, $flag );

			return true;
		};
	}

}
