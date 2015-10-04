<?php

namespace SCI\FilteredMetadata;

use Onoi\Remi\FilteredRecord;
use InvalidArgumentException;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class BibliographicFilteredRecord extends FilteredRecord {

	/**
	 * @var string
	 */
	private $titleForPageCreation = '';

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
	 * @var array
	 */
	protected $recordFields = array(
		'reference' => array(),
		'type'      => array(),
		'title'     => '',
		'subtitle'  => '',
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
		'isbn'      => array(),
		'oclc'      => '',
		'viaf'      => array()
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
	public function setTitleForPageCreation( $titleForPageCreation ) {
		$this->titleForPageCreation = 'CR:' . $titleForPageCreation;
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getTitleForPageCreation() {
		return $this->titleForPageCreation;
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
