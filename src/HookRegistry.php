<?php

namespace SCI;

use SMW\Store;
use Onoi\Cache\Cache;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SMW\DataTypeRegistry;
use SMW\DIWikiPage;
use SMWDataItem as DataItem;

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
	private $handlers = [];

	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @var CitationReferencePositionJournal
	 */
	private $citationReferencePositionJournal;

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 * @param Cache $cache
	 * @param Options $options
	 */
	public function __construct( Store $store, Cache $cache, Options $options ) {
		$this->options = $options;

		$this->addCallbackHandlers(
			$store,
			$cache,
			$this->options
		);
	}

	/**
	 * @note Usually only used during unit/integration testing
	 *
	 * @since  1.0
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function setOption( $key, $value ) {
		$this->options->set( $key, $value );
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function isRegistered( $name ) {
		return \MediaWiki\MediaWikiServices::getInstance()->getHookContainer()->isRegistered( $name );
	}

	/**
	 * @since  1.0
	 */
	public function clear() {

		if ( !defined( 'MW_PHPUNIT_TEST' ) ) {
			return;
		}

		foreach ( $this->handlers as $name => $callback ) {
			\MediaWiki\MediaWikiServices::getInstance()->getHookContainer()->clear( $name );
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
		// *** not clear why this has been added
		//if ( !defined( 'MW_PHPUNIT_TEST' ) ) {
		//	return;
		//}

		foreach ( $this->handlers as $name => $callback ) {
			\MediaWiki\MediaWikiServices::getInstance()->getHookContainer()->register( $name, $callback );
		}
	}

	/**
	 * @since  1.3
	 *
	 * @param array &$config
	 */
	public static function onBeforeConfigCompletion( &$config ) {

		if ( !isset( $config['smwgFulltextSearchPropertyExemptionList'] ) ) {
			return;
		}

		// Exclude those properties from indexing
		$config['smwgFulltextSearchPropertyExemptionList'] = array_merge(
			$config['smwgFulltextSearchPropertyExemptionList'],
			[ PropertyRegistry::SCI_CITE ]
		);
	}

	/**
	 * @since 2.0
	 */
	public static function initExtension() {

		$GLOBALS['wgHooks']['SMW::Config::BeforeCompletion'][] = function( &$config ) {

			$exemptionlist = [
				PropertyRegistry::SCI_CITE
			];

			// Exclude listed properties from indexing
			if ( isset( $config['smwgFulltextSearchPropertyExemptionList'] ) ) {
				$config['smwgFulltextSearchPropertyExemptionList'] = array_merge(
					$config['smwgFulltextSearchPropertyExemptionList'],
					$exemptionlist
				);
			}

			// Exclude listed properties from dependency detection as each of the
			// selected object would trigger an automatic change without the necessary
			// human intervention and can therefore produce unwanted query updates

			if ( isset( $config['smwgQueryDependencyPropertyExemptionList'] ) ) {
				$config['smwgQueryDependencyPropertyExemptionList'] = array_merge(
					$config['smwgQueryDependencyPropertyExemptionList'],
					$exemptionlist
				);
			}

			return true;
		};
	}

	public function onDataTypeInit() {
		$dataTypeRegistry = DataTypeRegistry::getInstance();

		$dataTypeRegistry->registerDatatype(
			'_sci_ref',
			'\SCI\DataValues\CitationReferenceValue',
			DataItem::TYPE_BLOB
		);

		$types = [
			'_sci_doi',
			'_sci_pmcid',
			'_sci_pmid',
			'_sci_oclc',
			'_sci_viaf',
			'_sci_olid'
		];

		foreach ( $types as $type ) {
			$dataTypeRegistry->registerDatatype(
				$type,
				'\SCI\DataValues\ResourceIdentifierStringValue',
				DataItem::TYPE_BLOB
			);
		}

		$callback = function() {
			return $this->citationReferencePositionJournal;
		};

		// SMW 3.1+
		if ( method_exists( $dataTypeRegistry, 'registerCallable' ) ) {
			$dataTypeRegistry->registerCallable( '_sci_ref', 'sci.citationreferencepositionjournal', $callback );
		} else {
			$dataTypeRegistry->registerExtraneousFunction(
				'\SCI\CitationReferencePositionJournal',
				$callback
			);
		}

		return true;
	}

	private function addCallbackHandlers( $store, $cache, $options ) {

		$propertyRegistry = new PropertyRegistry();
		$namespaceExaminer = ApplicationFactory::getInstance()->getNamespaceExaminer();

		$cacheKeyProvider = new CacheKeyProvider();
		$cacheKeyProvider->setCachePrefix( $options->get( 'cachePrefix' ) );

		$this->citationReferencePositionJournal = new CitationReferencePositionJournal(
			$cache,
			$cacheKeyProvider
		);

		$referenceBacklinksLookup = new ReferenceBacklinksLookup( $store );

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Property::initProperties
		 */
		$this->handlers['SMW::Property::initProperties'] = function ( $corePropertyRegistry ) use ( $propertyRegistry ) {
			return $propertyRegistry->registerTo( $corePropertyRegistry );
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::DataType::initTypes
		 */
		$this->handlers['SMW::DataType::initTypes'] = [ $this, 'onDataTypeInit' ];

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Browse::AfterIncomingPropertiesLookupComplete
		 */
		$this->handlers['SMW::Browse::AfterIncomingPropertiesLookupComplete'] = function ( $store, $semanticData, $requestOptions ) use ( $referenceBacklinksLookup ) {

			$referenceBacklinksLookup->setRequestOptions( $requestOptions );
			$referenceBacklinksLookup->setStore( $store );

			$referenceBacklinksLookup->addReferenceBacklinksTo(
				$semanticData
			);

			return true;
		};

		$this->handlers['SMW::Browse::BeforeIncomingPropertyValuesFurtherLinkCreate'] = function ( $property, $subject, &$html ) use ( $referenceBacklinksLookup ) {
			return $referenceBacklinksLookup->getSpecialPropertySearchFurtherLink( $property, $subject, $html );
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Parser::BeforeMagicWordsFinder
		 */
		$this->handlers['SMW::Parser::BeforeMagicWordsFinder'] = function( array &$magicWords ) {
			$magicWords = array_merge( $magicWords, [ 'SCI_NOREFERENCELIST' ] );
			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::SQLStore::AddCustomFixedPropertyTables
		 */
		$this->handlers['SMW::SQLStore::AddCustomFixedPropertyTables'] = function( array &$customFixedProperties ) use( $propertyRegistry ) {

			$properties = [
				$propertyRegistry::SCI_CITE_KEY,
				$propertyRegistry::SCI_CITE_REFERENCE,
				$propertyRegistry::SCI_CITE_TEXT,
				$propertyRegistry::SCI_CITE,
				$propertyRegistry::SCI_DOI,
				$propertyRegistry::SCI_PMCID,
				$propertyRegistry::SCI_PMID,
				$propertyRegistry::SCI_OCLC,
				$propertyRegistry::SCI_VIAF,
				$propertyRegistry::SCI_OLID
			];

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

			$outputPage->addModuleStyles( 'ext.scite.styles' );

			$outputPage->addModules(
				[
					'ext.scite.styles',
					'ext.scite.tooltip'
				]
			);

			return true;
		};

		/**
		 * @note This is bit of a hack but there is no other way to get access to
		 * the ParserOutput so that it can be used in OutputPageBeforeHTML
		 *
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
		 */
		$this->handlers['OutputPageParserOutput'] = function( &$outputPage, $parserOutput ) {
			$outputPage->smwmagicwords = $parserOutput->getExtensionData( 'smwmagicwords' );
			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageBeforeHTML
		 */
		$this->handlers['OutputPageBeforeHTML'] = function( &$outputPage, &$text ) use ( $store, $namespaceExaminer, $cache, $cacheKeyProvider, $options ) {

			$referenceListFactory = new ReferenceListFactory(
				$store,
				$namespaceExaminer,
				$this->citationReferencePositionJournal
			);

			$cachedReferenceListOutputRenderer = $referenceListFactory->newCachedReferenceListOutputRenderer(
				new MediaWikiContextInteractor( $outputPage->getContext() ),
				$cache,
				$cacheKeyProvider,
				$options
			);

			$cachedReferenceListOutputRenderer->addReferenceListToText( $text );

			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks#SMWStore::updateDataBefore
		 */
		$this->handlers['SMWStore::updateDataBefore'] = function ( $store, $semanticData ) use ( $cache, $cacheKeyProvider ) {

			$hash = $semanticData->getSubject()->getHash();

			// No remaining reference means it is time to purge the cache once
			// more because as long as a CiteRef exists, CitationReferencePositionJournal
			// is able recompute and update the entries but with no CiteRef left
			// the last entry will remain and needs to be purged at this point
			if ( !$this->citationReferencePositionJournal->hasCitationReference( $semanticData ) ) {
				$cache->delete(
					$cacheKeyProvider->getCacheKeyForCitationReference( $hash )
				);
			}

			$cache->delete(
				$cacheKeyProvider->getCacheKeyForReferenceList( $hash )
			);

			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks#SMW::SQLStore::AfterDeleteSubjectComplete
		 */
		$this->handlers['SMW::SQLStore::AfterDeleteSubjectComplete'] = function ( $store, $title ) use ( $cache, $cacheKeyProvider ) {

			$hash = DIWikiPage::newFromTitle( $title )->getHash();

			$cache->delete(
				$cacheKeyProvider->getCacheKeyForCitationReference( $hash )
			);

			$cache->delete(
				$cacheKeyProvider->getCacheKeyForReferenceList( $hash )
			);

			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks#SMW::SQLStore::AfterDataUpdateComplete
		 */
		$this->handlers['SMW::SQLStore::AfterDataUpdateComplete'] = function ( $store, $semanticData, $compositePropertyTableDiffIterator ) use ( $referenceBacklinksLookup, $options ) {

			$referenceBacklinksLookup->setStore( $store );

			$citationTextChangeUpdateJobDispatcher = new CitationTextChangeUpdateJobDispatcher(
				$store,
				$referenceBacklinksLookup
			);

			$citationTextChangeUpdateJobDispatcher->setEnabledUpdateJobState(
				$options->get( 'enabledCitationTextChangeUpdateJob' )
			);

			$citationTextChangeUpdateJobDispatcher->dispatchUpdateJobFor(
				$semanticData->getSubject(),
				$compositePropertyTableDiffIterator
			);

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
		 */
		$this->handlers['ResourceLoaderGetConfigVars'] = function ( &$vars ) use ( $options ) {

			$vars['ext.scite.config'] = [
				'showTooltipForCitationReference' => $options->get( 'showTooltipForCitationReference' ),
				'tooltipRequestCacheTTL' => $options->get( 'tooltipRequestCacheTTL' ),
				'cachePrefix' => $options->get( 'cachePrefix' )
			];

			return true;
		};

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
		 */
		$this->handlers['ParserFirstCallInit'] = function ( &$parser ) use ( $namespaceExaminer, $options ) {

			$parserFunctionFactory = new ParserFunctionFactory();

			list( $name, $definition, $flag ) = $parserFunctionFactory->newSciteParserFunctionDefinition(
				$namespaceExaminer,
				$options
			);

			$parser->setFunctionHook( $name, $definition, $flag );

			list( $name, $definition, $flag ) = $parserFunctionFactory->newReferenceListParserFunctionDefinition();

			$parser->setFunctionHook( $name, $definition, $flag );

			return true;
		};
	}

}
