<?php

namespace SCI\Metadata;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class NullResponseContentParser extends ResponseContentParser {

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function usedCache() {
		return false;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function isSuccess() {
		return false;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getRawResponse( $doi ) {
		return null;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function doParseFor( $id ) {
		return null;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function getMessages() {
		return array();
	}

	/**
	 * @since 1.0
	 *
	 * @return null
	 */
	public function getFilteredMetadataRecord() {
		return null;
	}

}
