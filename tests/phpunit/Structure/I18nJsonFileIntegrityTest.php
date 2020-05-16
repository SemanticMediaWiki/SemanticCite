<?php

namespace SCI\Tests\Structure;

use SMW\Tests\Utils\UtilityFactory;
use SMW\Tests\PHPUnitCompat;

/**
 * @group semantic-cite
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class I18nJsonFileIntegrityTest extends \PHPUnit_Framework_TestCase {

	use PHPUnitCompat;

	/**
	 * @dataProvider i18nFileProvider
	 */
	public function testI18NJsonDecodeEncode( $file ) {

		$jsonFileReader = UtilityFactory::getInstance()->newJsonFileReader( $file );

		$this->assertInternalType(
			'integer',
			$jsonFileReader->getModificationTime()
		);

		$this->assertInternalType(
			'array',
			$jsonFileReader->read()
		);
	}

	public function i18nFileProvider() {

		$provider = [];
		$location = $GLOBALS['wgMessagesDirs']['SemanticCite'];

		$bulkFileProvider = UtilityFactory::getInstance()->newBulkFileProvider( $location );
		$bulkFileProvider->searchByFileExtension( 'json' );

		foreach ( $bulkFileProvider->getFiles() as $file ) {
			$provider[] = [ $file ];
		}

		return $provider;
	}

}
