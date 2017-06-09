<?php

namespace SCI\Specials\CitableMetadata;

use Onoi\Remi\ResponseParser;
use Html;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HtmlResponseParserRenderer {

	/**
	 * @var ResponseParser
	 */
	private $responseParser;

	/**
	 * @var boolean
	 */
	private $isReadOnly = false;

	/**
	 * @since 1.0
	 *
	 * @param ResponseParser $responseParser
	 */
	public function __construct( ResponseParser $responseParser ) {
		$this->responseParser = $responseParser;
	}

	/**
	 * @since 1.4
	 *
	 * @param boolean $isReadOnly
	 */
	public function isReadOnly( $isReadOnly ) {
		$this->isReadOnly = (bool)$isReadOnly;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getRawResponse( $id ) {

		if ( $id === '' ) {
			return '';
		}

		return $this->responseParser->getRawResponse( $id );
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function renderTextFor( $id ) {

		$html = '';

		$this->responseParser->doFilterResponseFor( $id );

		$html .= Html::rawElement(
			'div',
			[
				'id' => 'scite-status',
			],
			''
		);

		if ( $this->responseParser->getMessages() !== [] ) {
			return '';
		}

		$create = '';

		// Only display the create button for when the DB can be actually
		// accessed
		if ( $this->isReadOnly === false ) {
			$create = Html::element(
				'a',
				[
					'href' => '#',
					'class' => 'scite-create scite-action-button',
					'data-content-selector' => '#scite-record-content',
					'data-title' => $this->responseParser->getFilteredRecord()->getTitleForPageCreation()
				],
				wfMessage( 'sci-metadata-search-action-create' )->text()
			);
		}

		$html .= Html::rawElement(
			'div',
			[
				'class' => 'scite-metadata-search-action'
			],
			Html::element(
				'a',
				[
					'href' => '#',
					'class' => 'scite-highlight scite-action-button',
					'data-content-selector' => '#scite-record-content'
				],
				wfMessage( 'sci-metadata-search-action-highlight' )->text()
			) . '&nbsp;' . $create
		);

		// To display the #scite on the generated page
		$this->responseParser->getFilteredRecord()->set( '@show', 'true' );

		$html .= Html::rawElement(
			'div',
			[
				'id' => 'scite-record-content',
				'class' => 'scite-pre'
			],
			$this->responseParser->getFilteredRecord()->asSciteTransclusion()
		);

		$html .= Html::rawElement(
			'div',
			[
				'class' => 'visualClear'
			],
			''
		);

		return $html;
	}

}
