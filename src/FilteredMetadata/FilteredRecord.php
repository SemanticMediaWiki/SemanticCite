<?php

namespace SCI\FilteredMetadata;

use InvalidArgumentException;

/**
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class FilteredRecord {

	/**
	 * @var array
	 */
	protected $recordFields = [];

	/**
	 * @var array
	 */
	private $redactedFields = [];

	/**
	 * @since 0.1
	 *
	 * @param array $redactedFields
	 */
	public function setRedactedFields( array $redactedFields ) {
		$this->redactedFields = array_flip( $redactedFields );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		if ( isset( $this->redactedFields[$key] ) ) {
			return null;
		}

		$this->recordFields[$key] = $value;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function append( $key, $value ) {
		if ( isset( $this->redactedFields[$key] ) ) {
			return null;
		}

		if ( !$this->has( $key ) ) {
			$this->recordFields[$key] = [];
		}

		if ( is_array( $this->recordFields[$key] ) ) {
			$this->recordFields[$key][] = $value;
		} else {
			$this->recordFields[$key] = $this->recordFields[$key] . $value;
		}
	}

	/**
	 * @since 0.1
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return isset( $this->recordFields[$key] ) || array_key_exists( $key, $this->recordFields );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $key
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function get( $key ) {
		if ( $this->has( $key ) ) {
			return $this->recordFields[$key];
		}

		throw new InvalidArgumentException( "{$key} is an unregistered field" );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $key
	 */
	public function delete( $key ) {
		unset( $this->recordFields[$key] );
	}

	/**
	 * @since 0.1
	 *
	 * @return array
	 */
	public function getRecordFields() {
		return $this->recordFields;
	}

	/**
	 * @since 0.2
	 *
	 * @param int $flags
	 *
	 * @return string
	 */
	public function asJsonString( $flags = 0 ) {
		return json_encode( $this->recordFields, $flags );
	}

}
