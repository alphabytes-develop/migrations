<?php
$them = getcwd() . '/migrations/';
$config = require($them . '/config.php');

require(__DIR__ . '/setup/db.php');
require(__DIR__ . '/setup/migrations.php');
require(__DIR__ . '/setup/db-migrations.php');

/* Get the last correct migration which was applied */
function findLastValidVersion() {
	global $migrations;
	global $dbMigrations;

	$last = 0;
	foreach($migrations as $version => $migration) {
		if(!isset($dbMigrations[$version])) {
			/* We found one which is not in the db */
			return $last;
		}

		if($dbMigrations[$version]['version'] !== (string) $migration['no']) {
			/* Something weird happened. Do not use this */
			return $last;
		}

		if($dbMigrations[$version]['hash'] !== $migration['hash']) {
			/* The hashes do not match, migration was modified */
			return $last;
		}

		$last++;
	}

	return $last;
}

/* Remove all migrations until we reach a specific version */
function rollbackToVersion($version, $dryrun = false) {
	global $dbMigrations;

	$invertedMigrations = array_reverse($dbMigrations);

	foreach($invertedMigrations as $dbMigration) {
		if($dbMigration['version'] > $version) {
			rollback($dbMigration, $dryrun);
		}
	}
}

/* Rollback a single migration */
function rollback($dbMigration, $dryrun = false) {
	global $db;
	global $config;

	echo "Rolling back $dbMigration[version]@$dbMigration[hash]\n";

	if($dryrun) {
		return;
	}

	/* Roll back the migration */
	$db->query($dbMigration['rollback']);

	/* Remove it from the migrations table */
	$stmt = $db->prepare("
		DELETE FROM `$config[table]` WHERE `version` = :version
	");

	$stmt->execute([
		'version' => $dbMigration['version']
	]);
}

/* Apply a single migration */
function apply($migration, $dryrun = false) {
	global $db;
	global $config;

	echo "Applying $migration[no]@$migration[hash]\n";

	if($dryrun) {
		return;
	}

	/* Apply the migration */
	$db->query($migration['upSql']);

	/* Write it to the migrations table */
	$stmt = $db->prepare("
		INSERT INTO `$config[table]` (`version`, `hash`, `rollback`) VALUES (:version, :hash, :rollback)
	");

	$stmt->execute([
		'version' => $migration['no'],
		'hash' => $migration['hash'],
		'rollback' => $migration['downSql']
	]);
}

/* Get the db to the current directories state */
function applyAll($dryrun = false) {
	global $migrations;

	$lastValid = findLastValidVersion();

	/* Get to a clean state */
	rollbackToVersion($lastValid, $dryrun);

	/* Apply any available newer versions */
	foreach($migrations as $migration) {
		if($migration['no'] > $lastValid) {
			apply($migration, $dryrun);
		}
	}
}