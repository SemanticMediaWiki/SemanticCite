<?php

namespace SCI\Metadata\Search;

use SMW\ApplicationFactory;
use SCI\CitationResourceMatchFinder;
use SMW\MediaWiki\Renderer\HtmlFormRenderer;
use SMW\MediaWiki\Renderer\HtmlColumnListRenderer;
use SCI\Metadata\HttpRequestProviderFactory;
use SCI\Metadata\FilteredMetadataRecord;

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
	 * @var HttpRequestProviderFactory
	 */
	private $httpRequestProviderFactory;

	/**
	 * @since 1.0
	 *
	 * @param HtmlFormRenderer $htmlFormRenderer
	 * @param HtmlColumnListRenderer $htmlColumnListRenderer
	 * @param CitationResourceMatchFinder $citationResourceMatchFinder
	 * @param HttpRequestProviderFactory $httpRequestProviderFactory
	 */
	public function __construct( HtmlFormRenderer $htmlFormRenderer, HtmlColumnListRenderer $htmlColumnListRenderer, CitationResourceMatchFinder $citationResourceMatchFinder, HttpRequestProviderFactory $httpRequestProviderFactory ) {
		$this->htmlFormRenderer = $htmlFormRenderer;
		$this->htmlColumnListRenderer = $htmlColumnListRenderer;
		$this->citationResourceMatchFinder = $citationResourceMatchFinder;
		$this->httpRequestProviderFactory = $httpRequestProviderFactory;
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

		$httpResponseContentParser = $this->httpRequestProviderFactory->newResponseContentParserForType(
			$type
		);

		$responseContentOutputRenderer = new ResponseContentOutputRenderer(
			$httpResponseContentParser
		);

		return $responseContentOutputRenderer->getRawResponse( $id );
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

			$httpResponseContentParser = $this->httpRequestProviderFactory->newResponseContentParserForType(
				$type
			);

			$responseContentOutputRenderer = new ResponseContentOutputRenderer(
				$httpResponseContentParser
			);

			$text = $responseContentOutputRenderer->renderTextFor( $id );

			$matches = $this->tryToFindCitationResourceMatches(
				$httpResponseContentParser->getFilteredMetadataRecord()
			);

			$success = $httpResponseContentParser->isSuccess();

			$log = $this->prepareLog(
				$success,
				$matches,
				$httpResponseContentParser->getMessages(),
				$httpResponseContentParser->usedCache()
			);
		}

		return $this->doRenderHtml( $type, $id, $success, $text, $log );
	}

	private function doRenderHtml( $type, $id, $success, $text, $log ) {

		$htmlFormRenderer = $this->htmlFormRenderer;
		$messageBuilder = $this->htmlFormRenderer->getMessageBuilder();

		$types = array(
			'pubmed' => 'PMID',
			'pmc' => 'PMCID',
			'doi' => 'DOI',
			'oclc' => 'OCLC',
			'viaf' => 'VIAF',
			'ol' => 'OL',
			'isbn' => 'ISBN'
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

	private function prepareLog( $success, $matches, $messages, $usedCache ) {

		$messageBuilder = $this->htmlFormRenderer->getMessageBuilder();

		$log = array();

		if ( $messages !== array() && !$success ) {
			$log += $messages;
		}

		if ( $matches !== '' ) {
			$log[] = $messageBuilder->getMessage( 'sci-metadata-search-has-match', $matches )->text();
		}

		if ( $usedCache ) {
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

	private function tryToFindCitationResourceMatches( FilteredMetadataRecord $filteredMetadataRecord ) {

		$subjects = array();

		foreach ( array( 'doi', 'oclc', 'viaf', 'olid', 'pubmed', 'pmc' ) as $type ) {
			$subjects += $this->citationResourceMatchFinder->findMatchForUidTypeOf(
				$type,
				$filteredMetadataRecord->getSearchMatchSetValueFor( $type )
			);
		}

		if ( $subjects === array() ) {
			return '';
		}

		return implode( ' | ', $this->citationResourceMatchFinder->findCitationResourceLinks( $subjects ) );
	}

}
