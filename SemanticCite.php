<?php

use SCI\HookRegistry;
use SCI\Options;
use SMW\ApplicationFactory;
use Onoi\Cache\CacheFactory;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticCite/
 *
 * @defgroup SCI Semantic Citation
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticCite extension, it is not a valid entry point.' );
}

if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.24', 'lt' ) ) {
	die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticCite/">SemanticCite</a> is only compatible with MediaWiki 1.24 or above. You need to upgrade MediaWiki first.' );
}

if ( defined( 'SCI_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SCI_VERSION', '1.0-alpha' );

/**
 * @codeCoverageIgnore
 */
call_user_func( function () {

	// Register the extension
	$GLOBALS['wgExtensionCredits']['semantic'][ ] = array(
		'path'           => __FILE__,
		'name'           => 'Semantic Cite',
		'author'         => array( 'James Hong Kong' ),
		'url'            => 'https://github.com/SemanticMediaWiki/SemanticCite/',
		'descriptionmsg' => 'sci-desc',
		'version'        => SCI_VERSION,
		'license-name'   => 'GPL-2.0+',
	);

	// Register message files
	$GLOBALS['wgMessagesDirs']['semantic-cite'] = __DIR__ . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['semantic-cite-magic'] = __DIR__ . '/i18n/SemanticCite.magic.php';
	$GLOBALS['wgExtensionMessagesFiles']['semantic-cite-alias'] = __DIR__ . '/i18n/SemanticCite.alias.php';

	$GLOBALS['wgSpecialPages']['FindCitableMetadata'] = '\SCI\Specials\SpecialFindCitableMetadata';

	// Restrict access to the meta search for registered users only
	$GLOBALS['wgAvailableRights'][] = 'sci-metadatasearch';
	$GLOBALS['wgGroupPermissions']['user']['sci-metadatasearch'] = true;

	// Register resource files
	$extensionPathParts = explode( DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR , __DIR__, 2 );

	$GLOBALS['wgResourceModules']['ext.scite.styles'] = array(
		'styles'  => 'res/scite.styles.css',
		'localBasePath' => __DIR__ ,
		'remoteExtPath' => end( $extensionPathParts ),
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
		'remoteExtPath' => end( $extensionPathParts ),
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
		'styles'  => 'res/jquery.qtip.css',
		'scripts' => array(
			'res/scite.cache.js',
			'res/scite.tooltip.js'
		),
		'localBasePath' => __DIR__ ,
		'remoteExtPath' => end( $extensionPathParts ),
		'dependencies'  => array(
			'ext.jquery.qtip',
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

	// In-text citation reference format options
	define( 'SCI_CITEREF_NUM', 1 );
	define( 'SCI_CITEREF_KEY', 2 );

	/**
	 * Specifies the caption format of the citation reference, either as a number
	 * or as the annotated key
	 */
	$GLOBALS['scigCitationReferenceCaptionFormat'] = SCI_CITEREF_NUM;

	/**
	 * Enables a tooltip for a specific citation reference format
	 *
	 * The requestCacheTTL specifies the expiry for a citation text that is locally
	 * cached in a browser before a new ajax-request is made.
	 *
	 * To force a browser to renew the display before the cache is expired, delete
	 * the "scite.cache" localStorage from the browser
	 */
	$GLOBALS['scigShowTooltipForCitationReference'] = array(
		SCI_CITEREF_NUM,
		SCI_CITEREF_KEY
	);

	$GLOBALS['scigTooltipRequestCacheTTLInSeconds'] = 60 * 60 * 24; // false to disable the Cache
	$GLOBALS['scigMetadataRequestCacheTTLInSeconds'] = 60 * 60 * 24;

	/**
	 * Number of columns displayed for the reference list
	 */
	$GLOBALS['scigNumberOfReferenceListColumns'] = 0; // 0 = responsive columns

	/**
	 * Specifies the reference list type of which can be either 'ol' or 'ul'
	 */
	$GLOBALS['scigReferenceListType'] = 'ol';

	/**
	 * Whether to generate a "Browse" link to a citation resource or not.
	 */
	$GLOBALS['scigBrowseLinkToCitationResource'] = true;

	/**
	 * Specify which cache type to be used, if no cache should be used at all,
	 * use CACHE_NONE
	 */
	$GLOBALS['scigReferenceListCacheType'] = CACHE_ANYTHING;

	/**
	 * Whether a strict validation on behalf of the #scite parser should be
	 * enabled or not
	 */
	$GLOBALS['scigStrictParserValidationEnabled'] = true;

	/**
	 * Whether an update job should be dispatched for changed citation text
	 * entities or not
	 */
	$GLOBALS['scigEnabledCitationTextChangeUpdateJob'] = true;

	// Finalize registration process
	$GLOBALS['wgExtensionFunctions'][] = function() {

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
			'strictParserValidationEnabled'      => $GLOBALS['scigStrictParserValidationEnabled'],
			'cachePrefix'                        => $GLOBALS['scigCachePrefix'],
			'enabledCitationTextChangeUpdateJob' => $GLOBALS['scigEnabledCitationTextChangeUpdateJob']
		);

		$cacheFactory = new CacheFactory();

		$compositeCache = $cacheFactory->newCompositeCache( array(
			$cacheFactory->newFixedInMemoryLruCache( 500 ),
			$cacheFactory->newMediaWikiCache( ObjectCache::getInstance( $GLOBALS['scigReferenceListCacheType'] ) )
		) );

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			$compositeCache,
			new Options( $configuration )
		);

		$hookRegistry->register();
	};

} );
