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
define( 'SCI_PROP_PMID', 'PMID' );
define( 'SCI_PROP_OCLC', 'OCLC' );
define( 'SCI_PROP_VIAF', 'VIAF' );
define( 'SCI_PROP_OLID', 'OLID' );

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
	const SCI_PMID = '__sci_pmid';
	const SCI_OCLC = '__sci_oclc';
	const SCI_VIAF = '__sci_viaf';
	const SCI_OLID = '__sci_olid';

	/**
	 * @since 1.0
	 *
	 * @param CorePropertyRegistry $corePropertyRegistry
	 *
	 * @return boolean
	 */
	public function registerTo( CorePropertyRegistry $corePropertyRegistry ) {

		$propertyDefinitions = array(

			self::SCI_OLID => array(
				'label' => SCI_PROP_OLID,
				'type'  => '_sci_olid',
				'alias' => array( wfMessage( 'sci-property-alias-olid' )->text(), 'olid', 'Olid' ),
				'visibility' => true,
				'annotableByUser' => true
			),

			self::SCI_VIAF => array(
				'label' => SCI_PROP_VIAF,
				'type'  => '_sci_viaf',
				'alias' => array( wfMessage( 'sci-property-alias-viaf' )->text(), 'viaf', 'Viaf' ),
				'visibility' => true,
				'annotableByUser' => true
			),

			self::SCI_OCLC => array(
				'label' => SCI_PROP_OCLC,
				'type'  => '_sci_oclc',
				'alias' => array( wfMessage( 'sci-property-alias-oclc' )->text(), 'oclc', 'Oclc' ),
				'visibility' => true,
				'annotableByUser' => true
			),

			self::SCI_DOI => array(
				'label' => SCI_PROP_DOI,
				'type'  => '_sci_doi',
				'alias' => array( wfMessage( 'sci-property-alias-doi' )->text(), 'Doi', 'doi' ),
				'visibility' => true,
				'annotableByUser' => true
			),

			self::SCI_PMCID => array(
				'label' => SCI_PROP_PMCID,
				'type'  => '_sci_pmcid',
				'alias' => array( wfMessage( 'sci-property-alias-pmcid' )->text(), 'Pmcid', 'pmcid' ),
				'visibility' => true,
				'annotableByUser' => true
			),

			self::SCI_PMID => array(
				'label' => SCI_PROP_PMID,
				'type'  => '_sci_pmid',
				'alias' => array( wfMessage( 'sci-property-alias-pmid' )->text(), 'Pmid', 'pmid' ),
				'visibility' => true,
				'annotableByUser' => true
			),

			self::SCI_CITE_KEY => array(
				'label' => SCI_PROP_CITE_KEY,
				'type'  => '_txt',
				'alias' => array( wfMessage( 'sci-property-alias-citation-key' )->text() ),
				'visibility' => true,
				'annotableByUser' => true
			),

			// Allow CiteRef to be an alias as it saves typing
			// [[CiteRef:: ... ]] instead of [[Citation reference:: ... ]]
			self::SCI_CITE_REFERENCE => array(
				'label' => SCI_PROP_CITE_REFERENCE,
				'type'  => '_sci_ref',
				'alias' => array( wfMessage( 'sci-property-alias-citation-reference' )->text(), 'CiteRef' ),
				'visibility' => true,
				'annotableByUser' => true
			),

			self::SCI_CITE_TEXT => array(
				'label' => SCI_PROP_CITE_TEXT,
				'type'  => '_txt',
				'alias' => array( wfMessage( 'sci-property-alias-citation-text' )->text() ),
				'visibility' => true,
				'annotableByUser' => true
			),

			self::SCI_CITE => array(
				'label' => SCI_PROP_CITE,
				'type'  => '__sob',
				'alias' => array( wfMessage( 'sci-property-alias-citation-resource' )->text() ),
				'visibility' => true,
				'annotableByUser' => false
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
			$definition['visibility'],
			$definition['annotableByUser']
		);

		foreach ( $definition['alias'] as $alias ) {
			$corePropertyRegistry->registerPropertyAlias(
				$propertyId,
				$alias
			);
		}
	}

}
