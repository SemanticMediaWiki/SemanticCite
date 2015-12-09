<?php

namespace SCI\DataValues;

use SMWRecordValue as RecordValue;
use SMW\DIProperty;
use SCI\PropertyRegistry;

/**
 * @license GNU GPL v2+
 * @since 1.1
 *
 * @author mwjames
 */
class CitationFrequencyValue extends RecordValue {

	/**
	 * @param string $typeid
	 */
	public function __construct( $typeid = '' ) {
		parent::__construct( '_sci_rec' );
	}

	public function getPropertyDataItems() {
		return array(
			new DIProperty( PropertyRegistry::SCI_CITE_KEY ),
			new DIProperty( PropertyRegistry::SCI_CITE_COUNT )
		);
	}

}
