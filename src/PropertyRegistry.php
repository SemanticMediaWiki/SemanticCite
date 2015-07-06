<?php

namespace SCI;

use SMW\PropertyRegistry as CorePropertyRegistry;

// Globally defined predefined property labels
define( 'SCI_PROP_CITE_KEY', 'Citation key' );
define( 'SCI_PROP_CITE_REFERENCE', 'Citation reference' );
define( 'SCI_PROP_CITE_TEXT', 'Citation text' );
define( 'SCI_PROP_CITE', 'Citation resource' );
define( 'SCI_PROP_DOI', 'DOI' );
define( 'SCI_PROP_PMCID', 'PMCID' );

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyRegistry {

	const SCI_CITE_KEY = '__sci_cite_key';
	const SCI_CITE_REFERENCE = '__sci_cite_reference';
	const SCI_CITE_TEXT = '__sci_cite_text';
	const SCI_CITE = '__sci_cite';
	const SCI_DOI = '__sci_doi';
	const SCI_PMCID = '__sci_pmcid';

	/**
	 * @since 1.0
	 *
	 * @param CorePropertyRegistry $corePropertyRegistry
	 *
	 * @return boolean
	 */
	public function registerTo( CorePropertyRegistry $corePropertyRegistry ) {

		$propertyDefinitions = array(

			self::SCI_DOI => array(
				'label' => SCI_PROP_DOI,
				'type'  => '_sci_doi',
				'alias' => array( wfMessage( 'sci-property-alias-doi' )->text(), 'Doi', 'doi' ),
				'visbility' => true,
				'annotable' => true
			),

			self::SCI_PMCID => array(
				'label' => SCI_PROP_PMCID,
				'type'  => '_sci_pmcid',
				'alias' => array( wfMessage( 'sci-property-alias-pmcid' )->text(), 'Pmcid', 'pmcid' ),
				'visbility' => true,
				'annotable' => true
			),

			self::SCI_CITE_KEY => array(
				'label' => SCI_PROP_CITE_KEY,
				'type'  => '_txt',
				'alias' => array( wfMessage( 'sci-property-alias-cite-key' )->text() ),
				'visbility' => true,
				'annotable' => true
			),

			// Allow CiteRef to be an alias as it saves typing
			// [[CiteRef:: ... ]] instead of [[Citation reference:: ... ]]
			self::SCI_CITE_REFERENCE => array(
				'label' => SCI_PROP_CITE_REFERENCE,
				'type'  => '_sci_ref',
				'alias' => array( wfMessage( 'sci-property-alias-cite-reference' )->text(), 'CiteRef' ),
				'visbility' => true,
				'annotable' => true
			),

			self::SCI_CITE_TEXT => array(
				'label' => SCI_PROP_CITE_TEXT,
				'type'  => '_txt',
				'alias' => array( wfMessage( 'sci-property-alias-cite-text' )->text() ),
				'visbility' => true,
				'annotable' => true
			),

			self::SCI_CITE => array(
				'label' => SCI_PROP_CITE,
				'type'  => '__sob',
				'alias' => array( wfMessage( 'sci-property-alias-cite-resource' )->text() ),
				'visbility' => true,
				'annotable' => false
			)
		);

		foreach ( $propertyDefinitions as $propertyId => $definition ) {
			$this->addPropertyDefinitionFor( $corePropertyRegistry, $propertyId, $definition  );
		}

		return true;
	}

	private function addPropertyDefinitionFor( $corePropertyRegistry, $propertyId, $definition ) {

		$corePropertyRegistry->registerProperty(
			$propertyId,
			$definition['type'],
			$definition['label'],
			$definition['visbility'],
			$definition['annotable']
		);

		foreach ( $definition['alias'] as $alias ) {
			$corePropertyRegistry->registerPropertyAlias(
				$propertyId,
				$alias
			);
		}
	}

}