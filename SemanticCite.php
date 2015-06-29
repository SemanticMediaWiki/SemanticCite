<?php

use SCI\HookRegistry;
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
	 * Whether to show the reference tooltip for when SCI_CITEREF_NUM is set
	 * or not.
	 *
	 * The requestCacheTTL specifies the expiry for a citation text that is locally
	 * cached in a browser before a new ajax-request is made.
	 *
	 * To force a browser to renew the display before the cache is expired, delete
	 * the "scite.cache" localStorage from the browser
	 */
	$GLOBALS['scigShowTooltipForCitationReference'] = true;
	$GLOBALS['scigTooltipRequestCacheTTLInSeconds'] = 60 * 60 * 24; // false to disable the Cache

	/**
	 * Number of columns displayed for the reference list
	 */
	$GLOBALS['scigNumberOfReferenceListColumns'] = 2;

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

	// Finalize registration process
	$GLOBALS['wgExtensionFunctions'][] = function() {

		$cachePrefix = $GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix'];

		$configuration = array(
			'numberOfReferenceListColumns'     => $GLOBALS['scigNumberOfReferenceListColumns'],
			'browseLinkToCitationResource'     => $GLOBALS['scigBrowseLinkToCitationResource'],
			'showTooltipForCitationReference'  => $GLOBALS['scigShowTooltipForCitationReference'],
			'tooltipRequestCacheTTL'           => $GLOBALS['scigTooltipRequestCacheTTLInSeconds'],
			'citationReferenceCaptionFormat'   => $GLOBALS['scigCitationReferenceCaptionFormat'],
			'referenceListType'                => $GLOBALS['scigReferenceListType'],
			'strictParserValidationEnabled'    => $GLOBALS['scigStrictParserValidationEnabled'],
			'cachePrefix'                      => $cachePrefix
		);

		$cacheFactory = new CacheFactory();

		$compositeCache = $cacheFactory->newCompositeCache( array(
			$cacheFactory->newFixedInMemoryLruCache( 500 ),
			$cacheFactory->newMediaWikiCache( ObjectCache::getInstance( $GLOBALS['scigReferenceListCacheType'] ) )
		) );

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			$compositeCache,
			$configuration
		);

		$hookRegistry->register();
	};

} );
