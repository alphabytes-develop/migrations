<?php
function getConfig() {
	$them = getcwd() . '/migrations/';
	$file = $them . 'config.php';

	if(!is_file($file)) {
		echo "'$file' not found\n";
		exit(1);
	}

	$config = require($file);
	$config['migrations'] = $them;

	return $config;
}