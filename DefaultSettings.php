<?php

/**
 * DO NOT EDIT!
 *
 * The following default settings are to be used by the extension itself,
 * please modify settings in the LocalSettings file.
 *
 * @codeCoverageIgnore
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticCite extension, it is not a valid entry point.' );
}

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
$GLOBALS['scigShowTooltipForCitationReference'] = [
	SCI_CITEREF_NUM,
	SCI_CITEREF_KEY
];

$GLOBALS['scigTooltipRequestCacheTTLInSeconds'] = 60 * 60 * 24; // false to disable the Cache

/**
 * Setting to regulate the caching of response for received from a metadata
 * provider
 */
$GLOBALS['scigMetadataResponseCacheType'] = -1; // CACHE_ANYTHING
$GLOBALS['scigMetadataResponseCacheLifetime'] = 60 * 60 * 24;

/**
 * Number of columns displayed for the reference list
 */
$GLOBALS['scigNumberOfReferenceListColumns'] = 0; // 0 = responsive columns

/**
 * Responsive columns depend on a browser to select an appropriated number of
 * columns based on the screen width, yet it is possible to limit the responsiveness
 * to a mono list when the character length of the produced reference list is
 * lower than that for the given setting.
 */
$GLOBALS['scigResponsiveMonoColumnCharacterBoundLength'] = 600;

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
$GLOBALS['scigReferenceListCacheType'] = -1; // CACHE_ANYTHING

/**
 * Whether a strict validation on behalf of the #scite parser should be
 * enabled or not
 */
$GLOBALS['scigEnabledStrictParserValidation'] = true;

// Deprecated setting
$GLOBALS['scigStrictParserValidationEnabled'] = $GLOBALS['scigEnabledStrictParserValidation'];

/**
 * Whether an update job should be dispatched for changed citation text
 * entities or not
 */
$GLOBALS['scigEnabledCitationTextChangeUpdateJob'] = true;
