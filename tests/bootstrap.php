<?php

if ( php_sapi_name() !== 'cli' ) {
	die( 'Not an entry point' );
}

if ( !is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The Semantic MediaWiki test autoloader is not available' );
}

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SCI\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SCI\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );

print( "Semantic MediaWiki: " . SMW_VERSION . " ({$GLOBALS['smwgDefaultStore']}, {$GLOBALS['wgDBtype']})\n\n" );
