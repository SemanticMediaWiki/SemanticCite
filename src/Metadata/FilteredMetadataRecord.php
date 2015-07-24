<?php

namespace SCI\Metadata;

use InvalidArgumentException;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class FilteredMetadataRecord {

	/**
	 * @var string
	 */
	private $citationResourceTitle = '';

	/**
	 * @var string
	 */
	private $sciteTransclusionHead = '';

	/**
	 * A loose set of possible key/values that can be used to search citation
	 * resources that match the condition of the key/value
	 *
	 * @var array
	 */
	private $searchMatchSet = array();

	/**
	 * @note Creates a pre-order so that during an export those identifier have
	 * the same position relative to each other.
	 *
	 * @var array
	 */
	private $recordFields = array(
		'reference' => array(),
		'type'      => array(),
		'title'     => '',
		'author'    => array(),
		'editor'    => array(),
		'journal'   => '',
		'publisher' => '',
		'pubdate'   => '',
		'year'      => '',
		'volume'    => '',
		'issue'     => '',
		'pages'     => '',
		'doi'       => '',
		'abstract'  => '',
		'subject'   => array(),
		'genre'     => array(),
		'isbn'      => array()
	);

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function getSearchMatchSetValueFor( $key ) {

		if ( isset( $this->searchMatchSet[$key] ) ) {
			return  $this->searchMatchSet[$key];
		}

		return null;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addSearchMatchSet( $key, $value ) {
		$this->searchMatchSet[$key] = $value;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function setCitationResourceTitle( $citationResourceTitle ) {
		$this->citationResourceTitle = 'CR:' . $citationResourceTitle;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function setSciteTransclusionHead( $sciteTransclusionHead ) {
		$this->sciteTransclusionHead = $sciteTransclusionHead;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getCitationResourceTitle() {
		return $this->citationResourceTitle;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		$this->recordFields[$key] = $value;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function append( $key, $value ) {

		if ( !$this->has( $key ) ) {
			$this->recordFields[$key] = array();
		}

		if ( is_array( $this->recordFields[$key] ) ) {
			$this->recordFields[$key][] = $value;
		} else{
			$this->recordFields[$key] = $this->recordFields[$key] . $value;
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function has( $key ) {
		return isset( $this->recordFields[$key] ) || array_key_exists( $key, $this->recordFields );
	}

	/**
	 * @since 1.0
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
	 * @since 1.0
	 *
	 * @return array
	 */
	public function getRecord() {
		return $this->recordFields;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function __toString() {

		$text = '';

		foreach ( $this->recordFields as $key => $value ) {

			if ( is_array( $value ) ) {
				$value = implode( ';', $value ) . ( count( $value ) > 1 ? '|+sep=;' : '' );
			}

			if ( $value === '' || $value === null ) {
				continue;
			}

			$text .= " |{$key}=" . $value . "\n";
		}

		return $text;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function asSciteTransclusion() {
		return '{{#scite:' . $this->sciteTransclusionHead ."\n" . $this .  "}}";;
	}

}
