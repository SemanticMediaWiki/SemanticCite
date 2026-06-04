<?php

namespace SCI;

use MediaWiki\MediaWikiServices;
use SMW\Services\ServicesFactory as ApplicationFactory;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\ObjectCache\CachedBagOStuff;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticCite/
 *
 * @defgroup SCI Semantic Cite
 *
 * @license GPL-2.0-or-later
 * @since 7.0
 *
 * @codeCoverageIgnore
 */
class Setup {

	/**
	 * @since 1.1
	 */
	public static function onExtensionFunction() {
		// Require a global because MW's Special page is missing an interface
		// to inject dependencies
		$GLOBALS['scigCachePrefix'] = $GLOBALS['wgCachePrefix'] === false ? \MediaWiki\WikiMap\WikiMap::getCurrentWikiId() : $GLOBALS['wgCachePrefix'];

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
			self::newCompositeCache( $GLOBALS['scigReferenceListCacheType'] ),
			new Options( $configuration )
		);

		$hookRegistry->register();
	}

	/**
	 * Builds an object cache that wraps a MediaWiki backend object cache with an
	 * in-process layer (a fixed-size hash cache) so that repeated lookups within
	 * the same request avoid hitting the backend.
	 *
	 * This replaces the former `onoi/cache` composite cache; `CachedBagOStuff`
	 * provides the equivalent in-memory-over-persistent behaviour using only
	 * MediaWiki built-ins.
	 *
	 * @since 7.0
	 *
	 * @param int|string $cacheType
	 *
	 * @return BagOStuff
	 */
	public static function newCompositeCache( $cacheType ): BagOStuff {
		$bagOStuff = MediaWikiServices::getInstance()->getObjectCacheFactory()->getInstance(
			$cacheType
		);

		return new CachedBagOStuff( $bagOStuff, [ 'maxKeys' => 500 ] );
	}

}
