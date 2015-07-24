<?php

namespace SCI;

use SMW\MediaWiki\MediaWikiNsContentReader;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class MediaWikiNsContentMapper {

	/**
	 * @var MediaWikiNsContentReader
	 */
	private $mediaWikiNsContentReader;

	/**
	 * @var array
	 */
	private static $identifierToPropertyMap = array();

	/**
	 * @var array
	 */
	private static $typeToTemplateMap = array();

	/**
	 * Only set during testing to circumvent the MessageCache
	 *
	 * @var boolean
	 */
	public static $skipMessageCache = false;

	/**
	 * @since 1.0
	 *
	 * @param MediaWikiNsContentReader $mediaWikiNsContentReader
	 */
	public function __construct( MediaWikiNsContentReader $mediaWikiNsContentReader ) {
		$this->mediaWikiNsContentReader = $mediaWikiNsContentReader;
	}

	/**
	 * @since 1.0
	 */
	public static function clear() {
		self::$typeToTemplateMap = array();
		self::$identifierToPropertyMap = array();
		self::$skipMessageCache = false;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function findPropertyForId( $id ) {

		$id = strtolower( trim( $id ) );

		if ( self::$identifierToPropertyMap === array() ) {

			// Fixed by definition due to usage of
			// pre-defined properties
			self::$identifierToPropertyMap = array(
				'viaf'          => SCI_PROP_VIAF,
				'doi'           => SCI_PROP_DOI,
				'oclc'          => SCI_PROP_OCLC,
				'olid'          => SCI_PROP_OLID,
				'pmcid'         => SCI_PROP_PMCID,
				'pmid'          => SCI_PROP_PMID,
				'reference'     => SCI_PROP_CITE_KEY,
				'citation text' => SCI_PROP_CITE_TEXT,
				'sortkey'       => '_SKEY'
			) + $this->parseContentFor( "sci-property-definition" );
		}

		return isset( self::$identifierToPropertyMap[$id] ) ? self::$identifierToPropertyMap[$id] : null;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function findTemplateForType( $type ) {

		$type = strtolower( trim( $type ) );

		if ( self::$typeToTemplateMap === array() ) {
			self::$typeToTemplateMap = $this->parseContentFor( "sci-template-definition" );
		}

		return isset( self::$typeToTemplateMap[$type] ) ? self::$typeToTemplateMap[$type] : null;
	}

	/**
	 * @param string $name
	 */
	private function parseContentFor( $name ) {

		if ( self::$skipMessageCache ) {
			$this->mediaWikiNsContentReader->skipMessageCache();
		}

		$contents = $this->mediaWikiNsContentReader->read( $name );

		if ( $contents === '' ) {
			return array();
		}

		$contentMap = array_map( 'trim', preg_split( "([\n][\s]?)", $contents ) );
		$list = array();

		// Check whether the first line is infact an explanation
		if ( strpos( $contentMap[0], '|' ) === false ) {
			array_shift( $contentMap );
		}

		foreach ( $contentMap as $map ) {

			if ( strpos( $map, '|' ) === false ) {
				continue;
			}

			list( $id, $value ) = explode( '|', $map, 2 );
			$list[str_replace( '_', ' ', strtolower( trim( $id ) ) )] = $value;
		}

		return $list;
	}

}
