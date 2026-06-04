<?php

namespace SCI\Specials;

use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use SCI\CitationResourceMatchFinder;
use SCI\FilteredMetadata\HttpResponseParserFactory;
use SCI\FilteredMetadata\MediaWikiHttpRequest;
use SCI\Setup;
use SCI\Specials\CitableMetadata\PageBuilder;
use SMW\Services\ServicesFactory as ApplicationFactory;

/**
 * @license GPL-2.0-or-later
 * @since   1.0
 *
 * @author mwjames
 */
class SpecialFindCitableMetadata extends SpecialPage {

	/**
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		parent::__construct( 'FindCitableMetadata', 'sci-metadatasearch' );
	}

	/**
	 * @see SpecialPage::getGroupName
	 */
	protected function getGroupName() {
		return 'wiki';
	}

	/**
	 * @see SpecialPage::execute
	 */
	public function execute( $query ) {
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}

		$output = $this->getOutput();
		$output->setPageTitle( $this->msg( 'findmetadatabyid' )->text() );
		$this->setHeaders();

		$output->addModuleStyles( 'ext.scite.styles' );

		$output->addModules(
			[
				'ext.scite.metadata'
			]
		);

		[ $type, $id, $format ] = $this->getRequestParameters(
			$query
		);

		if ( $type === 'pmid' ) {
			$type = 'pubmed';
		}

		$this->callPageBuilderFor( $type, $id, $format );
	}

	private function callPageBuilderFor( $type, $id, $format ) {
		$pageBuilder = $this->newPageBuilder();

		if ( $format === 'raw' ) {
			$this->getOutput()->disable();
			header( "Content-type: text/plain; charset=utf-8" );
			header( 'Cache-Control: private, no-cache, must-revalidate' );

			$html = $pageBuilder->getRawResponseFor(
				$type, $id
			);

			return print $html;
		}

		$html = $pageBuilder->getHtmlFor(
			$type, $id
		);

		$this->getOutput()->addHTML( $html );
		$this->addExternalHelpLinkFor( 'sci-specials-citablemetadata-helplink' );
	}

	private function getRequestParameters( $query ) {
		$request = $this->getRequest()->getValues();

		if ( strpos( $query, '/' ) !== false ) {
			[ $type, $id ] = explode( '/', $query, 2 );
			$request['type'] = $type;
			$request['id'] = $id;
		}

		$id = isset( $request['id'] ) ? trim( $request['id'] ) : '';
		$type = isset( $request['type'] ) ? strtolower( $request['type'] ) : '';
		$format = isset( $request['format'] ) ? strtolower( $request['format'] ) : '';

		return [ $type, $id, $format ];
	}

	private function newPageBuilder() {
		$applicationFactory = ApplicationFactory::getInstance();
		$mwCollaboratorFactory = $applicationFactory->newMwCollaboratorFactory();

		$htmlFormRenderer = $mwCollaboratorFactory->newHtmlFormRenderer(
			$this->getContext()->getTitle(),
			$this->getLanguage()
		);

		$httpRequest = new MediaWikiHttpRequest(
			MediaWikiServices::getInstance()->getHttpRequestFactory(),
			Setup::newCompositeCache( $GLOBALS['scigMetadataResponseCacheType'] )
		);

		$httpRequest->setOption( MediaWikiHttpRequest::RESPONSE_CACHE_TTL, $GLOBALS['scigMetadataResponseCacheLifetime'] );
		$httpRequest->setOption( MediaWikiHttpRequest::RESPONSE_CACHE_PREFIX, $GLOBALS['scigCachePrefix'] . ':sci:meta:' );

		$pageBuilder = new PageBuilder(
			$htmlFormRenderer,
			$mwCollaboratorFactory->newHtmlColumnListRenderer(),
			new CitationResourceMatchFinder( $applicationFactory->getStore() ),
			new HttpResponseParserFactory( $httpRequest )
		);

		$pageBuilder->isReadOnly( MediaWikiServices::getInstance()->getReadOnlyMode()->isReadOnly() );

		return $pageBuilder;
	}

	/**
	 * FIXME MW 1.25
	 */
	private function addExternalHelpLinkFor( $key ) {
		if ( !method_exists( $this, 'addHelpLink' ) ) {
			return null;
		}

		$this->getOutput()->addHelpLink( wfMessage( $key )->escaped(), true );
	}

}
