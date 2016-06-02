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
	 * @var CacheKeyProvider
	 */
	private $cacheKeyProvider;

	/**
	 * @var DIWikiPage
	 */
	private $subject;

	/**
	 * @var array
	 */
	private $defaultOptions = array();

	/**
	 * @since 1.0
	 *
	 * @param ReferenceListOutputRenderer $referenceListOutputRenderer
	 * @param MediaWikiContextInteractor $contextInteractor
	 * @param NamespaceExaminer $namespaceExaminer
	 * @param Cache $cache
	 * @param CacheKeyProvider $cacheKeyProvider
	 */
	public function __construct( ReferenceListOutputRenderer $referenceListOutputRenderer, MediaWikiContextInteractor $contextInteractor, NamespaceExaminer $namespaceExaminer, Cache $cache, CacheKeyProvider $cacheKeyProvider ) {
		$this->referenceListOutputRenderer = $referenceListOutputRenderer;
		$this->contextInteractor = $contextInteractor;
		$this->namespaceExaminer = $namespaceExaminer;
		$this->cache = $cache;
		$this->cacheKeyProvider = $cacheKeyProvider;
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
			return $this->removeReferenceListPlaceholder( $text );
		}

		if ( !$this->namespaceExaminer->isSemanticEnabled( $this->getSubject()->getNamespace() ) ) {
			return '';
		}

		$this->addReferenceListToCorrectTextPosition( $text );
	}

	private function removeReferenceListPlaceholder( &$text ) {

		if ( strpos( $text, 'scite-custom-referencelist' ) === false ) {
			return null;
		}

		return $text = preg_replace(
			"/" . "<div id=\"scite-custom-referencelist\"(.*)?>(<h2>|<span>)(.*)?<\/div>" . "/m",
			'',
			$text
		);
	}

	private function addReferenceListToCorrectTextPosition( &$text ) {

		// Remember the default options before trying to replace all list
		// placeholders to ensure to reset options to the default option
		// for when a list doesn't specify an option
		$this->defaultOptions = array(
			'listtype' => $this->referenceListOutputRenderer->getReferenceListType(),
			'columns'  => $this->referenceListOutputRenderer->getNumberOfReferenceListColumns(),
			'browse'   => $this->referenceListOutputRenderer->getBrowseLinkToCitationResourceState()
		);

		// Find out whether to place the list into a custom position or not
		if ( strpos( $text, 'scite-custom-referencelist' ) !== false ) {
			return $text = preg_replace_callback(
				"/" . "<div id=\"scite-custom-referencelist\"(.*)?>(<h2>|<span>)(.*)?<\/div>" . "/m",
				'self::getCustomizedRenderedHtmlReferenceList',
				$text
			);
		}

		if ( $this->contextInteractor->getTitle()->getNamespace() !== NS_FILE ) {
			$text .= $this->getRenderedHtmlReferenceList();
		}
	}

	private function getCustomizedRenderedHtmlReferenceList( $customOptions ) {

		$this->searchForReferenceListHeaderTocId( $customOptions );

		// Reset options
		$this->referenceListOutputRenderer->setReferenceListHeader( '' );
		$this->referenceListOutputRenderer->setReferenceListType( $this->defaultOptions['listtype'] );
		$this->referenceListOutputRenderer->setNumberOfReferenceListColumns( $this->defaultOptions['columns'] );
		$this->referenceListOutputRenderer->setBrowseLinkToCitationResourceState( $this->defaultOptions['browse'] );

		$customOptions = explode( 'data-', $customOptions[1] );

		$references = '';
		$fingerprint = '';

		foreach ( $customOptions as $options ) {

			if ( strpos( $options, '=' ) === false ) {
				continue;
			}

			$options = explode( '=', trim( str_replace( '"', '', $options ) ) );
			$this->doFilterValidOption( $options, $references, $fingerprint );
		}

		return $this->getRenderedHtmlReferenceList( $references, $fingerprint );
	}

	private function searchForReferenceListHeaderTocId( array $options ) {

		$headerId = array();
		$this->referenceListOutputRenderer->setReferenceListHeaderTocId( '' );

		// We know where to expect the index from preg_*
		if ( isset( $options[3] ) ) {
			preg_match("/id=\"(.*)\"/", $options[3], $headerId );
		}

		if ( $headerId !== array() ) {
			$this->referenceListOutputRenderer->setReferenceListHeaderTocId( $headerId[1] );
		}
	}

	private function doFilterValidOption( $options, &$references, &$fingerprint ) {

		$columns = '';
		$header = '';

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
				$columns = $options[1] < 0 ? 0 : $options[1];
				$this->referenceListOutputRenderer->setNumberOfReferenceListColumns(
					$columns
				);
				break;
			case 'header':
				$header = $options[1];
				$this->referenceListOutputRenderer->setReferenceListHeader( $header );
				break;
			case 'references':
				$references = $options[1];
				break;
		}

		$fingerprint = $header . $columns;
	}

	private function getRenderedHtmlReferenceList( $references = '', $fingerprint = ''  ) {

		$container = array();
		$oldId = $this->contextInteractor->getOldId();
		$lang  = $this->contextInteractor->getLanguageCode();

		// Keep the root cache entry based on the subject to ensure
		// that it can be flushed at once
		$key = $this->cacheKeyProvider->getCacheKeyForReferenceList(
			$this->getSubject()->getHash()
		);

		$revId = $oldId != 0 ? $oldId : $this->contextInteractor->getTitle()->getLatestRevID();

		// Create an individual hash for when loose references are used
		$renderedReferenceListHash = md5( $this->getSubject()->getHash() . $references . $fingerprint );

		if ( $this->cache->contains( $key ) ) {
			$container = $this->cache->fetch( $key );

			// Match against revision, languageCode, and hash
			if ( isset( $container['revId'] ) &&
				$container['revId'] == $revId &&
				isset( $container['text'][$lang][$renderedReferenceListHash] ) ) {
				return $container['text'][$lang][$renderedReferenceListHash];
			}
		}

		$renderedReferenceList = $this->referenceListOutputRenderer->doRenderReferenceListFor(
			$this->getSubject(),
			json_decode( html_entity_decode( $references ) )
		);

		// Don't cache a history/diff view
		if ( $oldId != 0 ) {
			return $renderedReferenceList;
		}

		$container['revId'] = $revId;
		$container['text'][$lang][$renderedReferenceListHash] = $renderedReferenceList;

		$this->cache->save(
			$key,
			$container
		);

		return $renderedReferenceList;
	}

}
