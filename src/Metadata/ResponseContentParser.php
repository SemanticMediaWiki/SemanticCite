<?php

namespace SCI\Metadata;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
abstract class ResponseContentParser {

	/**
	 * @var boolean
	 */
	protected $success = true;

	/**
	 * @var array
	 */
	protected $messages = array();

	/**
	 * @var FilteredMetadataRecord
	 */
	protected $filteredMetadataRecord;

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function isSuccess() {
		return $this->success;
	}

	/**
	 * @since 1.0
	 */
	public function addMessage( $message ) {

		if ( is_string( $message ) ) {
			return $this->messages[] = $message;
		}

		// Transform message key into a text representation
		if ( strpos( $message[0], 'sci-' ) !== false ) {
			return $this->messages[] = call_user_func_array( 'wfMessage', $message )->parse();
		}

		$this->messages = array_merge( $this->messages, $message );
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * @since 1.0
	 *
	 * @return FilteredMetadataRecord
	 */
	public function getFilteredMetadataRecord() {
		return $this->filteredMetadataRecord;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $id
	 */
	abstract public function doParseFor( $id );

	/**
	 * @since 1.0
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	abstract public function getRawResponse( $id );

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	abstract public function usedCache();

}
