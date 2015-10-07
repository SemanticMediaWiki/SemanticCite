<?php

namespace SCI;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CacheKeyProvider {

	/**
	 * Update the version to force a recache for all items due to
	 * required changes
	 */
	const VERSION = '1.2';

	/**
	 * @var string
	 */
	private $cachePrefix = '';

	/**
	 * @since 1.0
	 *
	 * @param string $cachePrefix
	 */
	public function setCachePrefix( $cachePrefix ) {
		$this->cachePrefix = $cachePrefix;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $hash
	 *
	 * @return string
	 */
	public function getCacheKeyForCitationReference( $hash ) {
		return $this->cachePrefix . ':sci:ref:' . md5( $hash . self::VERSION );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $hash
	 *
	 * @return string
	 */
	public function getCacheKeyForReferenceList( $hash ) {
		return $this->cachePrefix . ':sci:reflist:' . md5( $hash . self::VERSION );
	}

}
