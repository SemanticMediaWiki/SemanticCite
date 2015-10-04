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
	 * @since 1.0
	 *
	 * @param ResponseParser $responseParser
	 */
	public function __construct( ResponseParser $responseParser ) {
		$this->responseParser = $responseParser;
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
			array(
				'id' => 'scite-status',
			),
			''
		);

		if ( $this->responseParser->getMessages() !== array() ) {
			return '';
		}

		$html .= Html::rawElement(
			'div',
			array(
				'class' => 'scite-metadata-search-action'
			),
			Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'scite-highlight scite-action-button',
					'data-content-selector' => '#scite-record-content'
				),
				wfMessage( 'sci-metadata-search-action-highlight' )->text()
			) . '&nbsp;' .
			Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'scite-create scite-action-button',
					'data-content-selector' => '#scite-record-content',
					'data-title' =>  $this->responseParser->getFilteredRecord()->getTitleForPageCreation()
				),
				wfMessage( 'sci-metadata-search-action-create' )->text()
			)
		);

		$html .= Html::rawElement(
			'div',
			array(
				'id' => 'scite-record-content',
				'class' => 'scite-pre'
			),
			$this->responseParser->getFilteredRecord()->asSciteTransclusion()
		);

		$html .= Html::rawElement(
			'div',
			array(
				'class' => 'visualClear'
			),
			''
		);

		return $html;
	}

}
