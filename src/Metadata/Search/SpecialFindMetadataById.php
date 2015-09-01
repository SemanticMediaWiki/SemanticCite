<?php

namespace SCI\Metadata\Search;

use SpecialPage;
use SMW\ApplicationFactory;
use Onoi\HttpRequest\HttpRequestFactory;
use SCI\Metadata\ResponseParserFactory;
use SCI\CitationResourceMatchFinder;

/**
 * @license GNU GPL v2+
 * @since   1.1
 *
 * @author mwjames
 */
class SpecialFindMetadataById extends SpecialPage {

	/**
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		parent::__construct( 'FindMetadataById', 'sci-metasearch' );
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
			array(
				'ext.scite.metadata'
			)
		);

		list( $type, $id, $format ) = $this->getRequestParameters(
			$query
		);

		$this->callPageBuilderFor( $type, $id, $format );
	}

	private function callPageBuilderFor( $type, $id, $format ) {

		$pageBuilder = $this->newPageBuilder();

		if ( $format === 'raw' ) {
			$this->getOutput()->disable();
			header( "Content-type: text/plain; charset=utf-8" );

			$html = $pageBuilder->getRawResponseFor(
				$type, $id
			);

			return print $html;
		}

		$html = $pageBuilder->getHtmlFor(
			$type, $id
		);

		$this->getOutput()->addHTML( $html );
	}

	private function getRequestParameters( $query ) {

		$request = $this->getRequest()->getValues();

		if ( strpos( $query, '/' ) !== false ) {
			list( $type, $id ) = explode( '/', $query, 2 );
			$request['type'] = $type;
			$request['id'] = $id;
		}

		$id = isset( $request['id'] ) ? trim( $request['id'] ) : '';
		$type = isset( $request['type'] ) ? strtolower( $request['type'] ) : '';
		$format = isset( $request['format'] ) ? strtolower( $request['format'] ) : '';

		return array( $type, $id, $format );
	}

	private function newPageBuilder() {

		$applicationFactory = ApplicationFactory::getInstance();
		$mwCollaboratorFactory = $applicationFactory->newMwCollaboratorFactory();

		$htmlFormRenderer = $mwCollaboratorFactory->newHtmlFormRenderer(
			$this->getContext()->getTitle(),
			$this->getLanguage()
		);

		$httpRequestFactory = new HttpRequestFactory(
			$applicationFactory->getCache()
		);

		$httpRequest = $httpRequestFactory->newCachedCurlRequest();
		$httpRequest->setExpiryInSeconds( $GLOBALS['scigMetadataRequestCacheTTLInSeconds'] );
		$httpRequest->setCachePrefix( $GLOBALS['scigCachePrefix'] . ':' );

		$pageBuilder = new PageBuilder(
			$htmlFormRenderer,
			$mwCollaboratorFactory->newHtmlColumnListRenderer(),
			new CitationResourceMatchFinder( $applicationFactory->getStore() ),
			new ResponseParserFactory( $httpRequest )
		);

		return $pageBuilder;
	}

}
