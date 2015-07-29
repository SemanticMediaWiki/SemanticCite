<?php

namespace SCI;

use SMW\Store;
use SMW\SemanticData;
use SMW\DIProperty;
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
class BrowsePropertyLookup {

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
	 * @param SemanticData $semanticData
	 */
	public function addReferenceBacklinks( SemanticData $semanticData, $requestOptions = null ) {

		$keys = $this->store->getSemanticData( $semanticData->getSubject() )->getPropertyValues(
			new DIProperty( '__sci_cite_key' )
		);

		// Not a resource that contains a citation key
		if ( $keys === null || $keys === array() ) {
			return null;
		}

		if ( isset( $requestOptions->limit ) && $requestOptions->limit > 0 ) {
			$this->limit = $requestOptions->limit;
		}

		$property = new DIProperty( '__sci_cite_reference' );

		foreach ( $this->findReferenceBacklinksFor( end( $keys ) ) as $subject ) {
			$semanticData->addPropertyObjectValue( $property, $subject );
		}
	}

	private function findReferenceBacklinksFor( $key ) {

		$property = new DIProperty( '__sci_cite_reference' );

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
