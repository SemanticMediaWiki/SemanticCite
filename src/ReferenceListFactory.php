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
	public function newReferenceListOutputRenderer( $parser ) {

		$mwCollaboratorFactory = ApplicationFactory::getInstance()->newMwCollaboratorFactory();
		$htmlColumnListRenderer = $mwCollaboratorFactory->newHtmlColumnListRenderer();

		$referenceListOutputRenderer = new ReferenceListOutputRenderer(
			new CitationResourceMatchFinder( $this->store ),
			$this->citationReferencePositionJournal,
			$htmlColumnListRenderer,
			$parser
		);

		return $referenceListOutputRenderer;
	}

	/**
	 * @since  1.0
	 *
	 * @param MediaWikiContextInteractor $contextInteractor
	 * @param Cache $cache
	 * @param CacheKeyGenerator $cacheKeyGenerator
	 * @param Parser $parser
	 * @param array $configuration
	 *
	 * @return CachedReferenceListOutputRenderer
	 */
	public function newCachedReferenceListOutputRenderer( MediaWikiContextInteractor $contextInteractor, Cache $cache, CacheKeyGenerator $cacheKeyGenerator, $parser, Options $options ) {

		$referenceListOutputRenderer = $this->newReferenceListOutputRenderer(
			$parser
		);

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
			$cacheKeyGenerator
		);

		return $cachedReferenceListOutputRenderer;
	}

}