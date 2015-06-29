<?php

namespace SCI;

use Onoi\Cache\Cache;
use SMW\NamespaceExaminer;
use SMW\DIWikiPage;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CachedReferenceListOutputRenderer {

	/**
	 * @var ReferenceListOutputRenderer
	 */
	private $referenceListOutputRenderer;

	/**
	 * @var MediaWikiContextInteractor
	 */
	private $contextInteractor;

	/**
	 * @var NamespaceExaminer
	 */
	private $namespaceExaminer;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var CacheKeyGenerator
	 */
	private $cacheKeyGenerator;

	/**
	 * @var DIWikiPage
	 */
	private $subject;

	/**
	 * @since 1.0
	 *
	 * @param ReferenceListOutputRenderer $referenceListOutputRenderer
	 * @param MediaWikiContextInteractor $contextInteractor
	 * @param NamespaceExaminer $namespaceExaminer
	 * @param Cache $cache
	 * @param CacheKeyGenerator $cacheKeyGenerator
	 */
	public function __construct( ReferenceListOutputRenderer $referenceListOutputRenderer, MediaWikiContextInteractor $contextInteractor, NamespaceExaminer $namespaceExaminer, Cache $cache, CacheKeyGenerator $cacheKeyGenerator ) {
		$this->referenceListOutputRenderer = $referenceListOutputRenderer;
		$this->contextInteractor = $contextInteractor;
		$this->namespaceExaminer = $namespaceExaminer;
		$this->cache = $cache;
		$this->cacheKeyGenerator = $cacheKeyGenerator;
	}

	/**
	 * @since 1.0
	 *
	 * @return DIWikiPage
	 */
	public function getSubject() {

		if (  $this->subject === null ) {
			$this->subject = DIWikiPage::newFromTitle( $this->contextInteractor->getTitle() );
		}

		return $this->subject;
	}

	/**
	 * @since 1.0
	 *
	 * @param string &$text
	 */
	public function addReferenceListToText( &$text ) {

		if ( $this->contextInteractor->hasMagicWord( 'SCI_NOREFERENCELIST' ) || !$this->contextInteractor->hasAction( 'view' ) ) {
			return '';
		}

		if ( !$this->namespaceExaminer->isSemanticEnabled( $this->getSubject()->getNamespace() ) ) {
			return '';
		}

		$this->addReferenceListToCorrectTextPosition(
			$text,
			$this->getRenderedHtmlReferenceList( $this->contextInteractor->getOldId() )
		);
	}

	private function addReferenceListToCorrectTextPosition( &$text, $list ) {

		// Find out whether to place the list into a custom position or not
		if ( strpos( $text, 'scite-custom-referencelist' ) !== false ) {
			return $text = preg_replace("/<div id=\"scite-custom-referencelist\">.*<\/div>/m", $list, $text );
		}

		$text .= $list;
	}

	private function getRenderedHtmlReferenceList( $oldId ) {

		$key = $this->cacheKeyGenerator->getCacheKeyForReferenceList(
			$this->getSubject()->getHash()
		);

		$revId = $oldId != 0 ? $oldId : $this->contextInteractor->getTitle()->getLatestRevID();

		if ( $this->cache->contains( $key ) ) {
			$container = $this->cache->fetch( $key );

			if ( isset( $container['revId'] ) && $container['revId'] == $revId ) { // && $container['text'] !== ''
				return $container['text'];
			}
		}

		$referenceList = $this->referenceListOutputRenderer->renderReferenceListFor(
			$this->getSubject()
		);

		// Don't cache a history/diff view
		if ( $oldId != 0 ) {
			return $referenceList;
		}

		$container = array(
			'revId' => $revId,
			'text'  => $referenceList
		);

		$this->cache->save(
			$key,
			$container
		);

		return $referenceList;
	}

}
