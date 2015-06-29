<?php

if ( php_sapi_name() !== 'cli' ) {
	die( 'Not an entry point' );
}

if ( is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	print( "\nSemanticMediaWiki " . SMW_VERSION . " ({$GLOBALS['wgDBtype']}) test autoloader ...\n" );
} else {
	die( 'The SemanticMediaWiki test autoloader is not available' );
}

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SCI\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SCI\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
