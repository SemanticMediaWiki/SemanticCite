<?php

use SCI\HookRegistry;
use SCI\Options;
use SMW\ApplicationFactory;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticCite/
 *
 * @defgroup SCI Semantic Cite
 */
SemanticCite::load();

/**
 * @codeCoverageIgnore
 */
class SemanticCite {

	/**
	 * @since 1.3
	 *
	 * @note It is expected that this function is loaded before LocalSettings.php
	 * to ensure that settings and global functions are available by the time
	 * the extension is activated.
	 */
	public static function load() {

		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			include_once __DIR__ . '/vendor/autoload.php';
		}

		// Load DefaultSettings
		require_once __DIR__ . '/DefaultSettings.php';
	}

	/**
	 * @since 1.1
	 */
	public static function initExtension( $credits = [] ) {

		// See https://phabricator.wikimedia.org/T151136
		define( 'SCI_VERSION', isset( $credits['version'] ) ? $credits['version'] : 'UNKNOWN' );

		// Extend the upgrade key provided by SMW to ensure that an DB
		// schema is updated accordingly before using the extension
		if ( isset( $GLOBALS['smwgUpgradeKey'] ) ) {
		//	$GLOBALS['smwgUpgradeKey'] .= ':scite:2018-09';
		}

		// Register message files
		$GLOBALS['wgMessagesDirs']['SemanticCite'] = __DIR__ . '/i18n';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticCiteMagic'] = __DIR__ . '/i18n/SemanticCite.magic.php';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticCiteAlias'] = __DIR__ . '/i18n/SemanticCite.alias.php';

		$GLOBALS['wgSpecialPages']['FindCitableMetadata'] = '\SCI\Specials\SpecialFindCitableMetadata';

		// Restrict access to the meta search for registered users only
		$GLOBALS['wgAvailableRights'][] = 'sci-metadatasearch';
		$GLOBALS['wgGroupPermissions']['user']['sci-metadatasearch'] = true;

		// Register resource files
		$GLOBALS['wgResourceModules']['ext.scite.styles'] = [
			'styles'  => 'res/scite.styles.css',
			'localBasePath' => __DIR__ ,
			'remoteExtPath' => 'SemanticCite',
			'position' => 'top',
			'group'    => 'ext.smw',
			'targets' => [
				'mobile',
				'desktop'
			]
		];

		$GLOBALS['wgResourceModules']['ext.scite.metadata'] = [
			'scripts' => [
				'res/scite.text.selector.js',
				'res/scite.page.creator.js'
			],
			'localBasePath' => __DIR__ ,
			'remoteExtPath' => 'SemanticCite',
			'position' => 'top',
			'group'    => 'ext.smw',
			'dependencies'  => [
				'ext.scite.styles',
				'mediawiki.api'
			],
			'targets' => [
				'mobile',
				'desktop'
			]
		];

		$GLOBALS['wgResourceModules']['ext.scite.tooltip'] = [
			'scripts' => [
				'res/scite.tooltip.js'
			],
			'localBasePath' => __DIR__ ,
			'remoteExtPath' => 'SemanticCite',
			'dependencies'  => [
				'onoi.qtip',
				'onoi.blobstore',
				'onoi.util',
				'ext.scite.styles'
			],
			'messages' => [
				'sci-tooltip-citation-lookup-failure',
				'sci-tooltip-citation-lookup-failure-multiple'
			],
			'targets' => [
				'mobile',
				'desktop'
			]
		];

		// Register hooks that require to be listed as soon as possible and preferable
		// before the execution of onExtensionFunction
		HookRegistry::initExtension( $GLOBALS );
	}

	/**
	 * @since 1.1
	 */
	public static function onExtensionFunction() {

		if ( !defined( 'SMW_VERSION' ) ) {
			if ( PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg' ) {
				die( "\nThe 'Semantic Cite' extension requires 'Semantic MediaWiki' to be installed and enabled.\n" );
			} else {
				die( '<b>Error:</b> The <a href="https://github.com/SemanticMediaWiki/SemanticCite/">Semantic Cite</a> extension requires <a href="https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> to be installed and enabled.<br />' );
			}
		}

		// Require a global because MW's Special page is missing an interface
		// to inject dependencies
		$GLOBALS['scigCachePrefix'] = $GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix'];

		$configuration = [
			'numberOfReferenceListColumns'       => $GLOBALS['scigNumberOfReferenceListColumns'],
			'browseLinkToCitationResource'       => $GLOBALS['scigBrowseLinkToCitationResource'],
			'showTooltipForCitationReference'    => $GLOBALS['scigShowTooltipForCitationReference'],
			'tooltipRequestCacheTTL'             => $GLOBALS['scigTooltipRequestCacheTTLInSeconds'],
			'citationReferenceCaptionFormat'     => $GLOBALS['scigCitationReferenceCaptionFormat'],
			'referenceListType'                  => $GLOBALS['scigReferenceListType'],
			'enabledStrictParserValidation'      => $GLOBALS['scigEnabledStrictParserValidation'],
			'cachePrefix'                        => $GLOBALS['scigCachePrefix'],
			'enabledCitationTextChangeUpdateJob' => $GLOBALS['scigEnabledCitationTextChangeUpdateJob'],
			'responsiveMonoColumnCharacterBoundLength' => $GLOBALS['scigResponsiveMonoColumnCharacterBoundLength']
		];

		$applicationFactory = ApplicationFactory::getInstance();

		$hookRegistry = new HookRegistry(
			$applicationFactory->getStore(),
			$applicationFactory->newCacheFactory()->newMediaWikiCompositeCache( $GLOBALS['scigReferenceListCacheType'] ),
			new Options( $configuration )
		);

		$hookRegistry->register();
	}

	/**
	 * @since 1.1
	 *
	 * @return string|null
	 */
	public static function getVersion() {

		if ( !defined( 'SCI_VERSION' ) ) {
			return null;
		}

		return SCI_VERSION;
	}

}
