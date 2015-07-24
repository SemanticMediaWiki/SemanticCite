<?php

namespace SCI\Metadata\Search;

use SCI\Metadata\ResponseContentParser;
use Html;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ResponseContentOutputRenderer {

	/**
	 * @var ResponseContentParser
	 */
	private $responseContentParser;

	/**
	 * @since 1.0
	 *
	 * @param ResponseContentParser $responseContentParser
	 */
	public function __construct( ResponseContentParser $responseContentParser ) {
		$this->responseContentParser = $responseContentParser;
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

		return $this->responseContentParser->getRawResponse( $id );
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function renderTextFor( $id ) {

		$html = '';

		$this->responseContentParser->doParseFor( $id );

		if ( $this->responseContentParser->getMessages() !== array() ) {

			$messages = '';

			foreach ( $this->responseContentParser->getMessages() as $m ) {
				$messages .= Html::element( 'li', array(), $m );
			}

			$html .= Html::rawElement(
				'ul',
				array(),
				$messages
			);

			$html .= Html::rawElement(
				'div',
				array(
					'id' => 'scite-status',
				),
				''
			);
		}

		if ( !$this->responseContentParser->isSuccess() ) {
			return $html;
		}

		$html .= Html::rawElement(
			'div',
			array(
				'id' => 'scite-record-content',
				'class' => 'scite-pre'
			),
			$this->responseContentParser->getFilteredMetadataRecord()->asSciteTransclusion()
		);

		$html .= Html::rawElement(
			'div',
			array(
				'class' => 'scite-metadata-search-action'
			),
			Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'scite-highlight',
					'data-content-selector' => '#scite-record-content'
				),
				'Select text'
			) . ' | ' .
			Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'scite-create',
					'data-content-selector' => '#scite-record-content',
					'data-title' =>  $this->responseContentParser->getFilteredMetadataRecord()->getCitationResourceTitle()
				),
				'Create'
			)
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
