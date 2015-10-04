<?php

namespace SCI\DataValues;

use RuntimeException;
use SMW\PropertyRegistry;
use SMW\DIProperty;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ResourceIdentifierFactory {

	/**
	 * @since 1.0
	 *
	 * @param string $type
	 *
	 * @return ResourceIdentifierStringValue
	 * @throws RuntimeException
	 */
	public function newResourceIdentifierStringValueForType( $type ) {

		$propertyRegistry = PropertyRegistry::getInstance();
		$property = null;

		switch ( strtoupper( $type ) ) {
			case 'OCLC':
			case 'VIAF':
			case 'DOI':
			case 'OLID':
				$property = $propertyRegistry->findPropertyIdByLabel( $type );
				break;
			case 'PUBMED':
			case 'PMID':
				$property = $propertyRegistry->findPropertyIdByLabel( 'PMID' );
				break;
			case 'PMC':
			case 'PMCID':
				$property = $propertyRegistry->findPropertyIdByLabel( 'PMCID' );
				break;
		}

		$typeId = $propertyRegistry->getPropertyTypeId( $property );

		if ( $typeId === null || $typeId === '' ) {
			throw new RuntimeException( "{$type} is an unmatched type for ResourceIdentifierStringValue" );
		}

		$resourceIdentifierStringValue = new ResourceIdentifierStringValue( $typeId );
		$resourceIdentifierStringValue->setProperty( new DIProperty( $property ) );

		return $resourceIdentifierStringValue;
	}

}
