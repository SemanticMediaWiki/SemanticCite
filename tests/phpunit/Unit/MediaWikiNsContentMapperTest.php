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
class MediaWikiNsContentMapperTest extends \PHPUnit\Framework\TestCase {

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

		$provider[] = [
			'viaf',
			SCI_PROP_VIAF
		];

		$provider[] = [
			'doi',
			SCI_PROP_DOI
		];

		$provider[] = [
			'oclc',
			SCI_PROP_OCLC
		];

		$provider[] = [
			'olid',
			SCI_PROP_OLID
		];

		$provider[] = [
			'pmcid',
			SCI_PROP_PMCID
		];

		$provider[] = [
			'pmid',
			SCI_PROP_PMID
		];

		$provider[] = [
			'reference',
			SCI_PROP_CITE_KEY
		];

		$provider[] = [
			'citation text',
			SCI_PROP_CITE_TEXT
		];

		$provider[] = [
			'sortkey',
			'_SKEY'
		];

		return $provider;
	}

}
