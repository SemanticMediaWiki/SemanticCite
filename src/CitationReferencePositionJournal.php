<?php

namespace SCI;

use Onoi\Cache\Cache;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMW\SemanticData;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CitationReferencePositionJournal {

	/**
	 * @var Cache
	 */
	private $cache = null;

	/**
	 * @var CacheKeyProvider
	 */
	private $cacheKeyProvider;

	/**
	 * @var array
	 */
	private static $citationReferenceJournal = array();

	/**
	 * @since 1.0
	 *
	 * @param Cache $cache
	 * @param CacheKeyProvider $cacheKeyProvider
	 */
	public function __construct( Cache $cache, CacheKeyProvider $cacheKeyProvider ) {
		$this->cache = $cache;
		$this->cacheKeyProvider = $cacheKeyProvider;
	}

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage $subject
	 *
	 * @return boolean
	 */
	public function hasCitationReference( SemanticData $semanticData ) {
		return $semanticData->getPropertyValues( new DIProperty( PropertyRegistry::SCI_CITE_REFERENCE ) ) !== array();
	}

	/**
	 * @since 1.0
	 *
	 * @param  DIWikiPage $subject
	 *
	 * @return array|null
	 */
	public function getJournalBySubject( DIWikiPage $subject ) {
		return $this->hasJournalForHash( $subject->getHash() ) ? self::$citationReferenceJournal[$subject->getHash()] : null;
	}

	/**
	 * @note Build a journal from unbound references (loose from the subject invoked
	 * citation references), the position isn't important because those will not
	 * be linked to any CiteRef anchors.
	 *
	 * @since 1.0
	 *
	 * @param  array $referenceList
	 *
	 * @return array|null
	 */
	public function buildJournalForUnboundReferenceList( array $referenceList ) {

		if ( $referenceList === array() ) {
			return null;
		}

		$journal = array(
			'total' => 0,
			'reference-list' => array(),
			'reference-pos'  => array()
		);

		$journal['total'] = count( $referenceList );

		foreach ( $referenceList as $reference ) {
			$journal['reference-pos'][$reference] = array();
			$journal['reference-list'][$reference] = $reference;
		}

		return $journal;
	}

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage|null $subject
	 * @param string $reference
	 *
	 * @return string|null
	 */
	public function findLastReferencePositionEntryFor( DIWikiPage $subject = null, $reference ) {

		if ( $subject === null ) {
			return;
		}

		$referenceHash = md5( $reference );
		$hash = $subject->getHash();

		// Tracks the current parser run invoked by the InTextAnnotationParser
		$uniqid = $subject->getContextReference();

		if ( $this->hasJournalForHash( $hash ) &&
			self::$citationReferenceJournal[$hash]['uniqid'] === $uniqid &&
			isset( self::$citationReferenceJournal[$hash]['reference-pos'][$referenceHash] ) ) {
			return end( self::$citationReferenceJournal[$hash]['reference-pos'][$referenceHash] );
		}

		return null;
	}

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage|null $subject
	 * @param string $reference
	 */
	public function addJournalEntryFor( DIWikiPage $subject = null, $reference ) {

		if ( $subject === null ) {
			return;
		}

		$referenceHash = md5( $reference );
		$hash = $subject->getHash();

		// Tracks the current parser run invoked by the InTextAnnotationParser
		$uniqid = $subject->getContextReference();

		if ( !$this->hasJournalForHash( $hash ) || self::$citationReferenceJournal[$hash]['uniqid'] !== $uniqid ) {
			$journal = array(
				'uniqid'     => $uniqid,
				'total'      => 0,
				'reference-list' => array(),
				'reference-pos'  => array()
			);
		} else {
			$journal = self::$citationReferenceJournal[$hash];
		}

		// Caching the hash -> human readable key so that it can be reused
		// without any additional DB/Store access by the ReferenceListOutputRenderer
		$journal['reference-list'][$referenceHash] = $reference;

		// New reference, increase the total of existing citations for this
		// subject and set the position level to 'a'
		if ( !isset( $journal['reference-pos'][$referenceHash] ) || $journal['reference-pos'][$referenceHash] === array() ) {
			$journal['total']++;
			$journal['reference-pos'][$referenceHash][] = $journal['total'] . '-' . 'a';
		} else {
			// Found a citation with the same reference but for a different in-text
			// location.
			//
			// Increment the minor to a -> b -> c in order to create a map of
			// locations like 1-a, 1-b, 2-a etc.
			//
			// The exact position is specified by something like
			// like 57d0dc056f9e5d5c6cfc77fc27091f60-1-a and to be used within
			// html as link anchor
			list( $major, $minor ) = explode( '-', end( $journal['reference-pos'][$referenceHash] ) );
			$journal['reference-pos'][$referenceHash][] = $major . '-' . ++$minor;
		}

	 	self::$citationReferenceJournal[$hash] = $journal;

	 	// Safeguard against a repeated call to the hashlist for when the static
	 	// cache is empty
		$this->cache->save(
			$this->cacheKeyProvider->getCacheKeyForCitationReference( $hash ),
			self::$citationReferenceJournal
		);
	}

	private function hasJournalForHash( $hash ) {

		if ( self::$citationReferenceJournal === array() ) {
			self::$citationReferenceJournal = $this->cache->fetch(
				$this->cacheKeyProvider->getCacheKeyForCitationReference( $hash )
			);
		}

		return isset( self::$citationReferenceJournal[$hash] );
	}

}
