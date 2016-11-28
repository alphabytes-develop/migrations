<?php
function initiateDb($database) {
	$config = getConfig();

	$db = new PDO(
		"mysql:host=$config[host];dbname=$database;charset=UTF8",
		$config['user'],
		$config['password'],
		[
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		]
	);

	/* Create the migration table if missing */
	$db->query("
	CREATE TABLE IF NOT EXISTS `$config[table]` (
	  `version` int(11) unsigned NOT NULL,
	  `hash` varchar(64) NOT NULL,
	  `rollback` TEXT NOT NULL,
	  PRIMARY KEY (`version`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
	);

	return $db;
}