<?php

namespace SCI\Tests\FilteredMetadata;

use SCI\FilteredMetadata\BibliographicFilteredRecord;

/**
 * @covers \SCI\FilteredMetadata\BibliographicFilteredRecord
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since   1.0
 *
 * @author mwjames
 */
class BibliographicFilteredRecordTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\Onoi\Remi\FilteredRecord',
			new BibliographicFilteredRecord()
		);
	}

	public function testSearchMatchSet() {

		$instance = new BibliographicFilteredRecord();

		$instance->addSearchMatchSet( 'foo', 42 );

		$this->assertEquals(
			42,
			$instance->getSearchMatchSetValueFor( 'foo' )
		);
	}

	public function testTitleForPageCreation() {

		$instance = new BibliographicFilteredRecord();

		$instance->setTitleForPageCreation( 'foo' );

		$this->assertEquals(
			'CR:foo',
			$instance->getTitleForPageCreation()
		);
	}

	public function testSciteTransclusion() {

		$instance = new BibliographicFilteredRecord();

		$instance->setSciteTransclusionHead( 'foo' );

		$this->assertEquals(
			'{{#scite:foo' . "\n" . '}}',
			$instance->asSciteTransclusion()
		);
	}

}
