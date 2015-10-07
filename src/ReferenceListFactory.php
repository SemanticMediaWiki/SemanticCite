<?php

namespace SCI;

use SMW\Store;
use SMW\NamespaceExaminer;
use SMW\ApplicationFactory;
use Onoi\Cache\Cache;
use Parser;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ReferenceListFactory {

	/**
	 * @var Store
	 */
	private $store;

	/**
	 * @var NamespaceExaminer
	 */
	private $namespaceExaminer;

	/**
	 * @var CitationReferencePositionJournal
	 */
	private $citationReferencePositionJournal;

	/**
	 * @since  1.0
	 *
	 * @param Store $store
	 * @param NamespaceExaminer $namespaceExaminer
	 * @param CitationReferencePositionJournal $citationReferencePositionJournal
	 */
	public function __construct( Store $store, NamespaceExaminer $namespaceExaminer, CitationReferencePositionJournal $citationReferencePositionJournal ) {
		$this->store = $store;
		$this->namespaceExaminer = $namespaceExaminer;
		$this->citationReferencePositionJournal = $citationReferencePositionJournal;
	}

	/**
	 * @since  1.0
	 *
	 * @return ReferenceListOutputRenderer
	 */
	public function newReferenceListOutputRenderer() {

		$mwCollaboratorFactory = ApplicationFactory::getInstance()->newMwCollaboratorFactory();
		$htmlColumnListRenderer = $mwCollaboratorFactory->newHtmlColumnListRenderer();

		$referenceListOutputRenderer = new ReferenceListOutputRenderer(
			new CitationResourceMatchFinder( $this->store ),
			$this->citationReferencePositionJournal,
			$htmlColumnListRenderer
		);

		return $referenceListOutputRenderer;
	}

	/**
	 * @since  1.0
	 *
	 * @param MediaWikiContextInteractor $contextInteractor
	 * @param Cache $cache
	 * @param CacheKeyProvider $cacheKeyProvider
	 * @param Options $options
	 *
	 * @return CachedReferenceListOutputRenderer
	 */
	public function newCachedReferenceListOutputRenderer( MediaWikiContextInteractor $contextInteractor, Cache $cache, CacheKeyProvider $cacheKeyProvider, Options $options ) {

		$referenceListOutputRenderer = $this->newReferenceListOutputRenderer();

		$referenceListOutputRenderer->setNumberOfReferenceListColumns(
			$options->get( 'numberOfReferenceListColumns' )
		);

		$referenceListOutputRenderer->setReferenceListType(
			$options->get( 'referenceListType' )
		);

		$referenceListOutputRenderer->setBrowseLinkToCitationResourceState(
			$options->get( 'browseLinkToCitationResource' )
		);

		$referenceListOutputRenderer->setCitationReferenceCaptionFormat(
			$options->get( 'citationReferenceCaptionFormat' )
		);

		$cachedReferenceListOutputRenderer = new CachedReferenceListOutputRenderer(
			$referenceListOutputRenderer,
			$contextInteractor,
			$this->namespaceExaminer,
			$cache,
			$cacheKeyProvider
		);

		return $cachedReferenceListOutputRenderer;
	}

}
