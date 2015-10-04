<?php

namespace SCI\Specials\CitableMetadata;

use SMW\ApplicationFactory;
use SCI\CitationResourceMatchFinder;
use SMW\MediaWiki\Renderer\HtmlFormRenderer;
use SMW\MediaWiki\Renderer\HtmlColumnListRenderer;
use SCI\FilteredMetadata\HttpResponseParserFactory;
use SCI\FilteredMetadata\BibliographicFilteredRecord;

/**
 * @license GNU GPL v2+
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

		if ( $type !== '' && $id !== '' ) {

			$responseParser = $this->httpResponseParserFactory->newResponseParserForType(
				$type
			);

			$htmlResponseParserRenderer = new HtmlResponseParserRenderer(
				$responseParser
			);

			$text = $htmlResponseParserRenderer->renderTextFor( $id );

			$matches = $this->tryToFindCitationResourceMatches(
				$responseParser->getFilteredRecord()
			);

			$success = $responseParser->getMessages() === array();

			$log = $this->prepareLog(
				$responseParser->getMessages(),
				$matches,
				$responseParser->usesCache()
			);
		}

		return $this->doRenderHtml( $type, $id, $success, $text, $log );
	}

	private function doRenderHtml( $type, $id, $success, $text, $log ) {

		$htmlFormRenderer = $this->htmlFormRenderer;
		$messageBuilder = $this->htmlFormRenderer->getMessageBuilder();

		$types = array(
			'pubmed' => 'PMID',
			'pmc'  => 'PMCID',
			'doi'  => 'DOI',
			'oclc' => 'OCLC',
			'viaf' => 'VIAF',
			'ol'   => 'OLID',
		);

		$html = $htmlFormRenderer->setName( 'sci-metadata-search-form' )
			->withFieldset()
			->setMethod( 'get' )
			->addParagraph( $messageBuilder->getMessage( 'sci-metadata-search-intro' )->parse() )
			->addParagraph( $this->getTypeIdIntroText( $messageBuilder ) )
			->addHorizontalRule()
			->addOptionSelectList(
				'Type:',
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
			->addSubmitButton( $messageBuilder->getMessage( 'sci-metadata-search-form-submit' )->text() )
			->addNonBreakingSpace()
			->addCheckbox( 'Raw', 'format', 'raw' )
			->getForm();

			if ( $log !== '' ) {
				$htmlFormRenderer
					->setName( 'metadata-match' )
					->addHeader( 'h2', $messageBuilder->getMessage( 'sci-metadata-search-header-log' )->text() )
					->addParagraph( $log );
			}

			if ( $text !== '' && $success ) {
				$htmlFormRenderer
					->addHeader( 'h2', $messageBuilder->getMessage( 'sci-metadata-search-header-result' )->text() )
					->addParagraph( $text );
			}

			return $html . $htmlFormRenderer->getForm();
	}

	private function prepareLog( $messages, $matches, $usesCache ) {

		$messageBuilder = $this->htmlFormRenderer->getMessageBuilder();

		$log = array();

		foreach ( $messages as $m ) {

			if ( call_user_func_array( 'wfMessage', $m )->exists() ) {
				$m = call_user_func_array( 'wfMessage', $m )->parse();
			} else {
				$m = current( $m );
			}

			$log[]  = $m;
		}

		if ( $matches !== '' ) {
			$log[] = $messageBuilder->getMessage( 'sci-metadata-search-has-match', $matches )->text();
		}

		if ( $usesCache ) {
			$log[] = $messageBuilder->getMessage( 'sci-metadata-search-cached' )->text();
		}

		if ( $log === array() ) {
			return '';
		}

		$this->htmlColumnListRenderer->addContentsByNoIndex( $log );
		$this->htmlColumnListRenderer->setNumberOfColumns( 1 );

		return $this->htmlColumnListRenderer->getHtml();
	}

	private function getTypeIdIntroText( $messageBuilder ) {

		$explain = array();

		foreach ( array( 'doi', 'oclc', 'pubmed', 'ol', 'viaf' ) as $value ) {
			$explain[] = $messageBuilder->getMessage( 'sci-metadata-search-intro-'. $value )->parse();
		}

		$this->htmlColumnListRenderer->setColumnListClass( 'scite-metadata-search-types' );
		$this->htmlColumnListRenderer->addContentsByNoIndex( $explain );
		$this->htmlColumnListRenderer->setNumberOfColumns( 2 );

		return $this->htmlColumnListRenderer->getHtml();
	}

	private function tryToFindCitationResourceMatches( BibliographicFilteredRecord $bibliographicFilteredRecord ) {

		$html = '';

		foreach ( array( 'doi', 'oclc', 'viaf', 'olid', 'pubmed', 'pmc' ) as $type ) {
			$subjects = $this->citationResourceMatchFinder->findMatchForResourceIdentifierTypeToValue(
				$type,
				$bibliographicFilteredRecord->getSearchMatchSetValueFor( $type )
			);

			if ( $subjects !== array() ) {
				$html .= $type . ':' . implode( '|', $this->citationResourceMatchFinder->findCitationResourceLinks( $subjects ) );
			}
		}

		return $html;
	}

}
