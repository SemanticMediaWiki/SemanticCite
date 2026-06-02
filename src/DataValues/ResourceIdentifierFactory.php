<?php

namespace SCI\DataValues;

use RuntimeException;
use SMW\DataItems\Property;
use SMW\DataValueFactory;
use SMW\PropertyRegistry;

/**
 * @license GPL-2.0-or-later
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

		$typeId = $propertyRegistry->getPropertyValueTypeById( $property );

		if ( $typeId === null || $typeId === '' ) {
			throw new RuntimeException( "{$type} is an unmatched type for ResourceIdentifierStringValue" );
		}

		$resourceIdentifierStringValue = DataValueFactory::getInstance()->newDataValueByType( $typeId );
		$resourceIdentifierStringValue->setProperty( new Property( $property ) );

		return $resourceIdentifierStringValue;
	}

}
