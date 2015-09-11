<?php

namespace SCI;

use SMW\Store;
use SMW\SemanticData;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\Query\Language\SomeProperty;
use SMW\Query\Language\ThingDescription;
use SMW\Query\Language\ValueDescription;
use SMWQuery as Query;

/**
 * @license GNU GPL v2+
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
	 * @var integer
	 */
	private $limit = 20;

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
	 * @param integer $limit
	 */
	public function setLimit( $limit ) {
		$this->limit = $limit;
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

		$key = $this->findCitationKeyFor( $semanticData->getSubject() );

		$property = new DIProperty(
			PropertyRegistry::SCI_CITE_REFERENCE
		);

		foreach ( $this->findReferenceBacklinksFor( $key ) as $subject ) {
			$semanticData->addPropertyObjectValue( $property, $subject );
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage $subject
	 *
	 * @return DIBlob|null
	 */
	public function findCitationKeyFor( DIWikiPage $subject ) {

		$keys = $this->store->getSemanticData( $subject )->getPropertyValues(
			new DIProperty( PropertyRegistry::SCI_CITE_KEY )
		);

		// Not a resource that contains a citation key
		if ( $keys === null || $keys === array() ) {
			return null;
		}

		return end( $keys );
	}

	/**
	 * @since 1.0
	 *
	 * @param DIBlob|null
	 *
	 * @return DIWikiPage[]
	 */
	public function findReferenceBacklinksFor( $key = null ) {

		if ( $key === null ) {
			return array();
		}

		$property = new DIProperty( PropertyRegistry::SCI_CITE_REFERENCE );

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

		return $this->store->getQueryResult( $query )->getResults();
	}

}