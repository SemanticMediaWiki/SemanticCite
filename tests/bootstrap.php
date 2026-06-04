<?php

/**
 * PHPUnit test bootstrap and test class registry for the Semantic Cite
 * extension
 *
 * @license GPL-2.0-or-later
 * @author mwjames
 */

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !defined( 'SMW_PHPUNIT_AUTOLOADER_FILE' ) || !is_readable( SMW_PHPUNIT_AUTOLOADER_FILE ) ) {
	die( "\nThe Semantic MediaWiki test autoloader is not available" );
}

$extensionInfo = json_decode( file_get_contents( __DIR__ . '/../extension.json' ), true );

print sprintf( "\n%-20s%s\n", "Semantic Cite:", $extensionInfo['version'] ?? 'UNKNOWN' );

$autoloader = require SMW_PHPUNIT_AUTOLOADER_FILE;
$autoloader->addPsr4( 'SCI\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SCI\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );

unset( $autoloader );
