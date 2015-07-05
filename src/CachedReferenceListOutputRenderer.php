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

		$this->addReferenceListToCorrectTextPosition( $text );
	}

	private function addReferenceListToCorrectTextPosition( &$text ) {

		// Find out whether to place the list into a custom position or not
		if ( strpos( $text, 'scite-custom-referencelist' ) !== false ) {
			return $text = preg_replace_callback(
				"/" . "<div id=\"scite-custom-referencelist\"(.*)?><h2>(.*)?<\/div>" . "/m",
				'self::getCustomizedRenderedHtmlReferenceList',
				$text
			);
		}

		$text .= $this->getRenderedHtmlReferenceList();
	}

	private function getCustomizedRenderedHtmlReferenceList( $customOptions ) {

		$customOptions = explode( 'data-', $customOptions[1] );
		$this->referenceListOutputRenderer->setReferenceListHeader( '' );

		$references = '';
		$header = '';

		foreach ( $customOptions as $options ) {

			if ( strpos( $options, '=' ) === false ) {
				continue;
			}

			$options = explode( '=', trim( str_replace( '"', '', $options ) ) );

			switch ( $options[0] ) {
				case 'browselinks':
					$this->referenceListOutputRenderer->setBrowseLinkToCitationResourceState(
						filter_var( $options[1], FILTER_VALIDATE_BOOLEAN )
					);
					break;
				case 'listtype':
					$this->referenceListOutputRenderer->setReferenceListType( $options[1] );
					break;
				case 'columns':
					$this->referenceListOutputRenderer->setNumberOfReferenceListColumns(
						$options[1] < 1 ? 1 : $options[1]
					);
					break;
				case 'header':
					$header = $options[1];
					$this->referenceListOutputRenderer->setReferenceListHeader( $options[1] );
					break;
				case 'references':
					$references = $options[1];
					break;
			}
		}

		return $this->getRenderedHtmlReferenceList( $references, $header );
	}

	private function getRenderedHtmlReferenceList( $references = '', $header = '' ) {

		list( $renderedReferenceList, $container, $key, $oldId, $lang, $hash ) = $this->tryToFetchReferenceListFromCache(
			$references,
			$header
		);

		if ( $renderedReferenceList ) {
			return $renderedReferenceList;
		}

		$renderedReferenceList = $this->referenceListOutputRenderer->doRenderReferenceListFor(
			$this->getSubject(),
			json_decode( html_entity_decode( $references ) )
		);

		// Don't cache a history/diff view
		if ( $oldId != 0 ) {
			return $renderedReferenceList;
		}

		$container['text'][$lang][$hash] = $renderedReferenceList;

		$this->cache->save(
			$key,
			$container
		);

		return $renderedReferenceList;
	}

	private function tryToFetchReferenceListFromCache( $references = '', $header = '' ) {

		$container = array();

		$oldId = $this->contextInteractor->getOldId();
		$lang  = $this->contextInteractor->getLanguageCode();

		// Keep the root cache entry based on the subject to ensure
		// that it can be entirely flushed
		$key = $this->cacheKeyGenerator->getCacheKeyForReferenceList(
			$this->getSubject()->getHash()
		);

		$revId = $oldId != 0 ? $oldId : $this->contextInteractor->getTitle()->getLatestRevID();

		// Create an individual hash for when nonbound references are used
		$renderedReferenceListHash = md5( $this->getSubject()->getHash() . $references . $header );

		if ( $this->cache->contains( $key ) ) {
			$container = $this->cache->fetch( $key );

			// Match against revision, languageCode, and hash
			if ( isset( $container['revId'] ) &&
				$container['revId'] == $revId &&
				isset( $container['text'][$lang][$renderedReferenceListHash] ) ) {
				return array( $container['text'][$lang][$renderedReferenceListHash] );
			}
		}

		$container['revId'] = $revId;

		return array( false, $container, $key, $oldId, $lang, $renderedReferenceListHash );
	}

}
