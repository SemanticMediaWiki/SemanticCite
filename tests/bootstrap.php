<?php

/**
 * PHPUnit test bootstrap and test class registry for the Semantic Cite
 * extension
 *
 * @license GNU GPL v2+
 * @author mwjames
 */

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !is_readable( __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The Semantic MediaWiki test autoloader is required to run the tests.' );
}

if ( !class_exists( 'SemanticCite' ) || ( $version = SemanticCite::getVersion() ) === null ) {
	die( "\nSemantic Cite is not available, please check your Composer or LocalSettings.\n" );
}

print sprintf( "\n%-20s%s\n", "Semantic Cite:", SCI_VERSION );

$autoloader = require  __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php';
$autoloader->addPsr4( 'SCI\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SCI\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
unset( $autoloader );
