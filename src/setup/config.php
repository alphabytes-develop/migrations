<?php
/* Have to cache this so we can use require_once -.- */
$cachedMigrationConfig = null;

function getConfig() {
	global $cachedMigrationConfig;

	if(is_null($cachedMigrationConfig)) {
		$them = getcwd() . '/migrations/';
		$file = $them . 'config.php';

		if(!is_file($file)) {
			echo "'$file' not found\n";
			exit(1);
		}

        $cachedMigrationConfig = require_once($file);
        $cachedMigrationConfig['migrations'] = $them;
	}

    return $cachedMigrationConfig;
}