<?php

namespace SCI\Tests;

use SCI\MediaWikiNsContentMapper;

/**
 * @covers \SCI\MediaWikiNsContentMapper
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class MediaWikiNsContentMapperTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$mediaWikiNsContentReader = $this->getMockBuilder( '\SMW\MediaWiki\MediaWikiNsContentReader' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SCI\MediaWikiNsContentMapper',
			new MediaWikiNsContentMapper( $mediaWikiNsContentReader )
		);
	}

	/**
	 * @dataProvider fixedPropertyIdProvider
	 */
	public function testFindPropertyForId( $id, $expected ) {

		$mediaWikiNsContentReader = $this->getMockBuilder( '\SMW\MediaWiki\MediaWikiNsContentReader' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new MediaWikiNsContentMapper( $mediaWikiNsContentReader );

		$this->assertEquals(
			$expected,
			$instance->findPropertyForId(  $id )
		);
	}

	public function fixedPropertyIdProvider() {

		$provider[] = array(
			'viaf',
			SCI_PROP_VIAF
		);

		$provider[] = array(
			'doi',
			SCI_PROP_DOI
		);

		$provider[] = array(
			'oclc',
			SCI_PROP_OCLC
		);

		$provider[] = array(
			'olid',
			SCI_PROP_OLID
		);

		$provider[] = array(
			'pmcid',
			SCI_PROP_PMCID
		);

		$provider[] = array(
			'pmid',
			SCI_PROP_PMID
		);

		$provider[] = array(
			'reference',
			SCI_PROP_CITE_KEY
		);

		$provider[] = array(
			'citation text',
			SCI_PROP_CITE_TEXT
		);

		$provider[] = array(
			'sortkey',
			'_SKEY'
		);

		return $provider;
	}

}
