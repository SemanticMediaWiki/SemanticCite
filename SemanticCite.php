<?php

use SCI\HookRegistry;
use SCI\Options;
use SMW\ApplicationFactory;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticCite/
 *
 * @defgroup SCI Semantic Citation
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticCite extension, it is not a valid entry point.' );
}

if ( defined( 'SCI_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

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

		// In case extension.json is being used, the the succeeding steps are
		// expected to be handled by the ExtensionRegistry
		self::initExtension();

		$GLOBALS['wgExtensionFunctions'][] = function() {
			self::onExtensionFunction();
		};
	}

	/**
	 * @since 1.1
	 */
	public static function initExtension() {

		define( 'SCI_VERSION', '1.4.0-alpha' );

		// Register the extension
		$GLOBALS['wgExtensionCredits']['semantic'][ ] = array(
			'path'           => __FILE__,
			'name'           => 'Semantic Cite',
			'author'         => array( 'James Hong Kong' ),
			'url'            => 'https://github.com/SemanticMediaWiki/SemanticCite/',
			'descriptionmsg' => 'sci-desc',
			'version'        => SCI_VERSION,
			'license-name'   => 'GPL-2.0+'
		);

		// Register message files
		$GLOBALS['wgMessagesDirs']['SemanticCite'] = __DIR__ . '/i18n';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticCiteMagic'] = __DIR__ . '/i18n/SemanticCite.magic.php';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticCiteAlias'] = __DIR__ . '/i18n/SemanticCite.alias.php';

		$GLOBALS['wgSpecialPages']['FindCitableMetadata'] = '\SCI\Specials\SpecialFindCitableMetadata';

		// Restrict access to the meta search for registered users only
		$GLOBALS['wgAvailableRights'][] = 'sci-metadatasearch';
		$GLOBALS['wgGroupPermissions']['user']['sci-metadatasearch'] = true;

		// Register resource files
		$GLOBALS['wgResourceModules']['ext.scite.styles'] = array(
			'styles'  => 'res/scite.styles.css',
			'localBasePath' => __DIR__ ,
			'remoteExtPath' => 'SemanticCite',
			'position' => 'top',
			'group'    => 'ext.smw',
			'targets' => array(
				'mobile',
				'desktop'
			)
		);

		$GLOBALS['wgResourceModules']['ext.scite.metadata'] = array(
			'scripts' => array(
				'res/scite.text.selector.js',
				'res/scite.page.creator.js'
			),
			'localBasePath' => __DIR__ ,
			'remoteExtPath' => 'SemanticCite',
			'position' => 'top',
			'group'    => 'ext.smw',
			'dependencies'  => array(
				'ext.scite.styles',
				'mediawiki.api'
			),
			'targets' => array(
				'mobile',
				'desktop'
			)
		);

		$GLOBALS['wgResourceModules']['ext.scite.tooltip'] = array(
			'scripts' => array(
				'res/scite.tooltip.js'
			),
			'localBasePath' => __DIR__ ,
			'remoteExtPath' => 'SemanticCite',
			'dependencies'  => array(
				'onoi.qtip',
				'onoi.blobstore',
				'onoi.util',
				'ext.scite.styles',
				'mediawiki.api.parse'
			),
			'messages' => array(
				'sci-tooltip-citation-lookup-failure',
				'sci-tooltip-citation-lookup-failure-multiple'
			),
			'targets' => array(
				'mobile',
				'desktop'
			)
		);

		self::onBeforeExtensionFunction();
	}

	/**
	 * Register hooks that require to be listed as soon as possible and preferable
	 * before the execution of onExtensionFunction
	 *
	 * @since 1.3
	 */
	public static function onBeforeExtensionFunction() {
		$GLOBALS['wgHooks']['SMW::Config::BeforeCompletion'][] = '\SCI\HookRegistry::onBeforeConfigCompletion';
	}

	/**
	 * @since 1.3
	 */
	public static function doCheckRequirements() {

		if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.24', 'lt' ) ) {
			die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticCite/">SemanticCite</a> is only compatible with MediaWiki 1.24 or above. You need to upgrade MediaWiki first.' );
		}

		if ( !defined( 'SMW_VERSION' ) ) {
			die( '<b>Error:</b> <a href="https://github.com/SemanticMediaWiki/SemanticCite/">Semantic Cite</a> requires the <a href="https://github.com/SemanticMediaWiki/SemanticMediaWiki/">Semantic MediaWiki</a> extension, please enable or install the extension first.' );
		}
	}

	/**
	 * @since 1.1
	 */
	public static function onExtensionFunction() {

		// Check requirements after LocalSetting.php has been processed
		self::doCheckRequirements();

		// Require a global because MW's Special page is missing an interface
		// to inject dependencies
		$GLOBALS['scigCachePrefix'] = $GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix'];

		$configuration = array(
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
		);

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
		return SCI_VERSION;
	}

}
