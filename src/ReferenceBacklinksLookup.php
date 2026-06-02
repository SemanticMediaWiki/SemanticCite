<?php

namespace SCI;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;
use SMW\Query\Language\SomeProperty;
use SMW\Query\Language\ValueDescription;
use SMW\Query\Query;
use SMW\Store;

/**
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class ReferenceBacklinksLookup {

	/**
	 * @var Store
	 */
	private $store;

	/**
	 * @var int
	 */
	private $limit = 20;

	/**
	 * @var int
	 */
	private $offset = 0;

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 */
	public function __construct( Store $store ) {
		$this->store = $store;
	}

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 */
	public function setStore( Store $store ) {
		$this->store = $store;
	}

	/**
	 * @since 1.0
	 *
	 * @param mixed|null $requestOptions
	 */
	public function setRequestOptions( $requestOptions = null ) {
		if ( $requestOptions === null ) {
			return;
		}

		$this->limit = $requestOptions->limit;
		$this->offset = $requestOptions->offset;
	}

	/**
	 * @since 1.0
	 *
	 * @param Property $property
	 * @param WikiPage $subject
	 * @param string &$html
	 */
	public function getSpecialPropertySearchFurtherLink( Property $property, WikiPage $subject, &$html ) {
		if ( $property->getKey() !== PropertyRegistry::SCI_CITE_REFERENCE || ( $citationKey = $this->tryToFindCitationKeyFor( $subject ) ) === null ) {
			return true;
		}

		$html .= Html::element(
			'a',
			[
				'href' => SpecialPage::getSafeTitleFor( 'SearchByProperty' )->getLocalURL( [
					'property' => $property->getLabel(),
					'value' => $citationKey->getString()
				] )
			],
			wfMessage( 'smw_browse_more' )->text()
		);

		return false;
	}

	/**
	 * Adds backlinks information to the SemanticData which is targeted towards
	 * the Special:Browse inproperties
	 *
	 * @since 1.0
	 *
	 * @param SemanticData $semanticData
	 */
	public function addReferenceBacklinksTo( SemanticData $semanticData ) {
		$key = $this->tryToFindCitationKeyFor( $semanticData->getSubject() );

		$property = new Property(
			PropertyRegistry::SCI_CITE_REFERENCE
		);

		foreach ( $this->findReferenceBacklinksFor( $key ) as $subject ) {
			$semanticData->addPropertyObjectValue( $property, $subject );
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param WikiPage $subject
	 *
	 * @return Blob|null
	 */
	public function tryToFindCitationKeyFor( WikiPage $subject ) {
		$keys = $this->store->getSemanticData( $subject )->getPropertyValues(
			new Property( PropertyRegistry::SCI_CITE_KEY )
		);

		// Not a resource that contains a citation key
		if ( $keys === null || $keys === [] ) {
			return null;
		}

		return end( $keys );
	}

	/**
	 * @since 1.0
	 *
	 * @param Blob|null
	 *
	 * @return WikiPage[]
	 */
	public function findReferenceBacklinksFor( $key = null ) {
		if ( $key === null ) {
			return [];
		}

		$property = new Property( PropertyRegistry::SCI_CITE_REFERENCE );

		$description = new ValueDescription(
			$key,
			$property
		);

		$someProperty = new SomeProperty(
			$property,
			$description
		);

		$query = new Query( $someProperty );
		$query->setLimit( $this->limit );
		$query->setOffset( $this->offset );

		if ( defined( Query::class . '::PROC_CONTEXT' ) ) {
			$query->setOption( Query::PROC_CONTEXT, 'SCI.ReferenceBacklinksLookup' );
		}

		return $this->store->getQueryResult( $query )->getResults();
	}

}
