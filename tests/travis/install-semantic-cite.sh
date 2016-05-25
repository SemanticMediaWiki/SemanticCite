#!/bin/bash
set -ex

BASE_PATH=$(pwd)
MW_INSTALL_PATH=$BASE_PATH/../mw

# Run Composer installation from the MW root directory
function installToMediaWikiRoot {
	echo -e "Running MW root composer install build on $TRAVIS_BRANCH \n"

	cd $MW_INSTALL_PATH

	if [ "$PHPUNIT" != "" ]
	then
		composer require 'phpunit/phpunit='$PHPUNIT --update-with-dependencies
	else
		composer require 'phpunit/phpunit=4.7.*' --update-with-dependencies
	fi

	if [ "$SCI" != "" ]
	then
		composer require 'mediawiki/semantic-scite='$SCI --update-with-dependencies
	else
		composer init --stability dev
		composer require mediawiki/semantic-cite "dev-master" --dev

		cd extensions
		cd SemanticCite

		if [ "$TRAVIS_PULL_REQUEST" != "false" ]
		then
			git fetch origin +refs/pull/"$TRAVIS_PULL_REQUEST"/merge:
			git checkout -qf FETCH_HEAD
		else
			git fetch origin "$TRAVIS_BRANCH"
			git checkout -qf FETCH_HEAD
		fi

		cd ../..
	fi

	# Rebuild the class map for added classes during git fetch
	composer dump-autoload
}

function updateConfiguration {

	cd $MW_INSTALL_PATH

	echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
	echo 'ini_set("display_errors", 1);' >> LocalSettings.php
	echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
	echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
	echo "putenv( 'MW_INSTALL_PATH=$(pwd)' );" >> LocalSettings.php

	php maintenance/update.php --quick
}

installToMediaWikiRoot
updateConfiguration
