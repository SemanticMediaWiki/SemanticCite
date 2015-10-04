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
use SMW\DataValueFactory;
use SCI\DataValues\ResourceIdentifierFactory;

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
	 * @var DataValueFactory
	 */
	private $dataValueFactory;

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 */
	public function __construct( Store $store ) {
		$this->store = $store;
		$this->dataValueFactory = DataValueFactory::getInstance();
	}

	/**
	 * @since 1.0
	 *
	 * @param array $subjects
	 * @param string $linkClass
	 *
	 * @return array
	 */
	public function findCitationResourceLinks( array $subjects, $linkClass = '' ) {

		$citationResourceLinks = array();

		foreach ( $subjects as $subject ) {

			$dataValue = $this->dataValueFactory->newDataItemValue(
				$subject,
				null
			);

			$browselink = \SMWInfolink::newBrowsingLink(
				'&#8593;', // ↑; $reference,
				$dataValue->getWikiValue(),
				$linkClass
			);

			$citationResourceLinks[] = $browselink->getHTML();
		}

		return $citationResourceLinks;
	}

	/**
	 * Find match for [[OCLC::SomeOclcKey]]
	 *
	 * @since 1.0
	 *
	 * @param string $type
	 * @param string|null $id
	 *
	 * @return array
	 */
	public function findMatchForResourceIdentifierTypeToValue( $type, $id = null ) {

		if ( $id === null || $id === '' ) {
			return array();
		}

		$resourceIdentifierFactory = new ResourceIdentifierFactory();

		$resourceIdentifierStringValue = $resourceIdentifierFactory->newResourceIdentifierStringValueForType( $type );
		$resourceIdentifierStringValue->setUserValue( $id );
		$id = $resourceIdentifierStringValue->getWikiValue();

		$description = new SomeProperty(
			$resourceIdentifierStringValue->getProperty(),
			new ValueDescription( new DIBlob( $id ) )
		);

		$query = new Query(
			$description,
			false,
			false
		);

		$query->querymode = Query::MODE_INSTANCES;
		$query->setLimit( 10 );

		return $this->store->getQueryResult( $query )->getResults();
	}

	/**
	 * @since 1.0
	 *
	 * @param string $citationReference
	 *
	 * @return array
	 */
	public function findCitationTextFor( $citationReference ) {

		$text = '';
		$subjects = array();

		$queryResult = $this->findMatchForCitationReference(
			$citationReference
		);

		if ( !$queryResult instanceof \SMWQueryResult ) {
			return array( $subjects, $text );
		}

		while ( $resultArray = $queryResult->getNext() ) {
			foreach ( $resultArray as $result ) {

				// Collect all subjects for the same reference because it can happen
				// that the same reference key is used for different citation
				// resources therefore only return one (the last) valid citation
				// text but return all subjects to make it easier to find them later
				$subjects[] = $result->getResultSubject();

				while ( ( $dataValue = $result->getNextDataValue() ) !== false ) {
					$text = $dataValue->getShortWikiText();
				}
			}
		}

		return array( $subjects, $text );
	}

	/**
	 * Find match for [[Citation key::SomeKey]]|?Citation text
	 *
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
