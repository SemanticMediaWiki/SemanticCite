<?php

namespace SCI\Specials\CitableMetadata;

use MediaWiki\Message\Message;
use SCI\CitationResourceMatchFinder;
use SCI\FilteredMetadata\BibliographicFilteredRecord;
use SCI\FilteredMetadata\HttpResponseParserFactory;
use SMW\MediaWiki\Renderer\HtmlColumnListRenderer;
use SMW\MediaWiki\Renderer\HtmlFormRenderer;

/**
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class PageBuilder {

	/**
	 * @var HtmlFormRenderer
	 */
	private $htmlFormRenderer;

	/**
	 * @var HtmlColumnListRenderer
	 */
	private $htmlColumnListRenderer;

	/**
	 * @var CitationResourceMatchFinder
	 */
	private $citationResourceMatchFinder;

	/**
	 * @var HttpResponseParserFactory
	 */
	private $httpResponseParserFactory;

	/**
	 * @var bool
	 */
	private $isReadOnly = false;

	/**
	 * @since 1.0
	 *
	 * @param HtmlFormRenderer $htmlFormRenderer
	 * @param HtmlColumnListRenderer $htmlColumnListRenderer
	 * @param CitationResourceMatchFinder $citationResourceMatchFinder
	 * @param HttpResponseParserFactory $httpResponseParserFactory
	 */
	public function __construct( HtmlFormRenderer $htmlFormRenderer, HtmlColumnListRenderer $htmlColumnListRenderer, CitationResourceMatchFinder $citationResourceMatchFinder, HttpResponseParserFactory $httpResponseParserFactory ) {
		$this->htmlFormRenderer = $htmlFormRenderer;
		$this->htmlColumnListRenderer = $htmlColumnListRenderer;
		$this->citationResourceMatchFinder = $citationResourceMatchFinder;
		$this->httpResponseParserFactory = $httpResponseParserFactory;
	}

	/**
	 * @since 1.4
	 *
	 * @param bool $isReadOnly
	 */
	public function isReadOnly( $isReadOnly ) {
		$this->isReadOnly = (bool)$isReadOnly;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $type
	 * @param string $id
	 *
	 * @return string
	 */
	public function getRawResponseFor( $type, $id ) {
		$responseParser = $this->httpResponseParserFactory->newResponseParserForType(
			$type
		);

		$htmlResponseParserRenderer = new HtmlResponseParserRenderer(
			$responseParser
		);

		return $htmlResponseParserRenderer->getRawResponse( $id );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $type
	 * @param string $id
	 *
	 * @return string
	 */
	public function getHtmlFor( $type, $id ) {
		$text = '';
		$success = true;
		$log = '';
		$matches = '';

		if ( $type !== '' && $id !== '' ) {

			$responseParser = $this->httpResponseParserFactory->newResponseParserForType(
				$type
			);

			$htmlResponseParserRenderer = new HtmlResponseParserRenderer(
				$responseParser
			);

			$htmlResponseParserRenderer->isReadOnly(
				$this->isReadOnly
			);

			$text = $htmlResponseParserRenderer->renderTextFor( $id );

			$matches = $this->tryToFindCitationResourceMatches(
				$responseParser->getFilteredRecord()
			);

			$success = $responseParser->getMessages() === [];

			$log = $this->prepareLog(
				$responseParser->getMessages(),
				$matches,
				$responseParser->usesCache()
			);
		}

		return $this->doRenderHtml( $type, $id, $success, $text, $log, $matches );
	}

	/**
	 * Build a message in the renderer's language so the form labels match the
	 * surrounding form's language rather than defaulting to the user language.
	 */
	private function msg( string $key, ...$params ): Message {
		return wfMessage( $key, ...$params )
			->inLanguage( $this->htmlFormRenderer->getLanguage() );
	}

	private function doRenderHtml( $type, $id, $success, $text, $log, $matches ) {
		$htmlFormRenderer = $this->htmlFormRenderer;

		$types = [
			'pubmed' => 'PMID',
			'pmc'  => 'PMCID',
			'doi'  => 'DOI',
			'oclc' => 'OCLC',
			'viaf' => 'VIAF',
			'ol'   => 'OLID',
		];

		if ( $matches !== '' ) {
			$htmlFormRenderer->addParagraph(
				'<div class="smw-callout smw-callout-info">' .
				$this->msg( 'sci-metadata-search-has-match', $matches )->text() .
				'</div>'
			);
		}

		$html = $htmlFormRenderer->setName( 'sci-metadata-search-form' )
			->withFieldset()
			->setMethod( 'get' )
			->addParagraph( $this->msg( 'sci-metadata-search-intro' )->parse() ?? '' )
			->addParagraph( $this->getTypeIdIntroText() ?? '' )
			->addHorizontalRule()
			->addOptionSelectList(
				$this->msg( 'sci-metadata-search-select-label' )->text() ?? '',
				'type',
				$type,
				$types )
			->addInputField(
				'',
				'id',
				$id,
				'id',
				40 )
			->addNonBreakingSpace()
			->addSubmitButton( $this->msg( 'sci-metadata-search-form-submit' )->text() )
			->addNonBreakingSpace()
			->addCheckbox( 'Raw', 'format', 'raw' )
			->getForm();

		if ( $text !== '' && $success ) {
			$htmlFormRenderer
				->addHeader( 'h2', $this->msg( 'sci-metadata-search-header-result' )->text() ?? '' )
				->addParagraph( $text );
		}

		if ( $log !== '' ) {
			$htmlFormRenderer
				->setName( 'metadata-match' )
				->addHeader( 'h2', $this->msg( 'sci-metadata-search-header-log' )->text() ?? '' )
				->addParagraph( $log );
		}

		return $html . $htmlFormRenderer->getForm();
	}

	private function prepareLog( $messages, $matches, $usesCache ) {
		$log = [];

		foreach ( $messages as $m ) {

			if ( call_user_func_array( 'wfMessage', $m )->exists() ) {
				$m = call_user_func_array( 'wfMessage', $m )->parse();
			} else {
				$m = current( $m );
			}

			$log[] = $m;
		}

		if ( $usesCache ) {
			$log[] = $this->msg( 'sci-metadata-search-cached' )->text();
		}

		if ( $this->isReadOnly ) {
			$log[] = $this->msg( 'sci-metadata-search-read-only' )->text();
		}

		if ( $log === [] ) {
			return '';
		}

		$this->htmlColumnListRenderer->addContentsByNoIndex( $log );
		$this->htmlColumnListRenderer->setNumberOfColumns( 1 );

		return $this->htmlColumnListRenderer->getHtml();
	}

	private function getTypeIdIntroText() {
		$explain = [];

		foreach ( [ 'doi', 'oclc', 'pubmed', 'ol', 'viaf' ] as $value ) {
			$explain[] = $this->msg( 'sci-metadata-search-intro-' . $value )->parse();
		}

		$this->htmlColumnListRenderer->setColumnListClass( 'scite-metadata-search-types' );
		$this->htmlColumnListRenderer->addContentsByNoIndex( $explain );
		$this->htmlColumnListRenderer->setNumberOfColumns( 2 );

		return $this->htmlColumnListRenderer->getHtml();
	}

	private function tryToFindCitationResourceMatches( BibliographicFilteredRecord $bibliographicFilteredRecord ) {
		$html = [];

		foreach ( [ 'doi', 'oclc', 'viaf', 'olid', 'pubmed', 'pmc' ] as $type ) {
			$subjects = $this->citationResourceMatchFinder->findMatchForResourceIdentifierTypeToValue(
				$type,
				$bibliographicFilteredRecord->getSearchMatchSetValueFor( $type )
			);

			if ( $subjects !== [] ) {
				$html = array_merge(
					$html,
					$this->citationResourceMatchFinder->findCitationResourceLinks( $subjects, '', strtoupper( $type ) )
				);
			}
		}

		return $html !== [] ? '<strong>' . implode( ', ', $html ) . '</strong>' : '';
	}

}
