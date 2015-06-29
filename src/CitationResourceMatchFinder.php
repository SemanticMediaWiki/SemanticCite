<?php

namespace SCI;

use SMW\Query\Language\SomeProperty;
use SMW\Query\Language\ValueDescription;
use SMW\Query\PrintRequest;
use SMW\Store;
use SMW\DIProperty;
use SMWPropertyValue as PropertyValue;
use SMWQuery as Query;
use SMWDIBlob as DIBlob;

/**
 * @license GNU GPL v2+
 * @since 2.2
 *
 * @author mwjames
 */
class CitationResourceMatchFinder {

	/**
	 * @var Store
	 */
	private $store;

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
	 * @param string $citationReference
	 *
	 * @return QueryResult
	 */
	public function findMatchForCitationReference( $citationReference ) {

		$description = new SomeProperty(
			new DIProperty( PropertyRegistry::SCI_CITE_KEY ),
			new ValueDescription( new DIBlob( $citationReference ) )
		);

		$propertyValue = new PropertyValue( '__pro' );
		$propertyValue->setDataItem(
			new DIProperty( PropertyRegistry::SCI_CITE_TEXT )
		);

		$description->addPrintRequest(
			new PrintRequest( PrintRequest::PRINT_PROP, null, $propertyValue )
		);

		$query = new Query(
			$description,
			false,
			false
		);

		$query->querymode = Query::MODE_INSTANCES;
		$query->setLimit( 10 );

		return $this->store->getQueryResult( $query );
	}

}
