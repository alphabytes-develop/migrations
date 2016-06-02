<?php
require_once(__DIR__ . '/setup/db.php');
require_once(__DIR__ . '/setup/config.php');
require_once(__DIR__ . '/setup/migrations.php');
require_once(__DIR__ . '/setup/db-migrations.php');

function query($db, $sql) {
	$stmt = $db->query($sql);

	/* PDO only throws on non-first query if we actually iterate over the rowset??? */
	while($stmt->nextRowset()) {}
}

/* Apply a single migration */
function apply($db, $migration, $dryrun = false) {
	echo "Applying $migration[no]@$migration[hash]\n";

	if($dryrun) {
		return;
	}

	/* Apply the migration */
	query($db, $migration['upSql']);

	/* Write it to the migrations table */
	$config = getConfig();
	$stmt = $db->prepare("
		INSERT INTO `$config[table]` (`version`, `hash`, `rollback`) VALUES (:version, :hash, :rollback)
	");

	$stmt->execute([
		'version' => $migration['no'],
		'hash' => $migration['hash'],
		'rollback' => $migration['downSql']
	]);
}

/* Rollback a single migration */
function rollback($db, $dbMigration, $dryrun = false) {
	echo "Rolling back $dbMigration[version]@$dbMigration[hash]\n";

	if($dryrun) {
		return;
	}

	/* Roll back the migration */
	if($dbMigration['rollback'] !== '') {
		query($db, $dbMigration['rollback']);
	}

	/* Remove it from the migrations table */
	$config = getConfig();
	$stmt = $db->prepare("
		DELETE FROM `$config[table]` WHERE `version` = :version
	");

	$stmt->execute([
		'version' => $dbMigration['version']
	]);
}

/* Get the last correct migration which was applied */
function findLastValidVersion($db) {
	$migrations = getMigrations();
	$dbMigrations = getDbMigrations($db);

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
function rollbackToVersion($db, $version, $dryrun = false) {
	$invertedMigrations = array_reverse(getDbMigrations($db));

	foreach($invertedMigrations as $dbMigration) {
		if($dbMigration['version'] > $version) {
			rollback($db, $dbMigration, $dryrun);
		}
	}
}

/* Get the db to the current directories state */
function applyAll($db, $dryrun = false) {
	$lastValid = findLastValidVersion($db);
	$migrations = getMigrations();

	/* Get to a clean state */
	rollbackToVersion($db, $lastValid, $dryrun);

	/* Apply any available newer versions */
	foreach($migrations as $migration) {
		if($migration['no'] > $lastValid) {
			apply($db, $migration, $dryrun);
		}
	}
}

/* Run a test against another database */
function test($database) {
	$config = getConfig();

	if($database === $config['database']) {
		echo "You cannot run a test against the same database that is set in your config!\n";
		exit(1);
	}

	$db = initiateDb($config['database']);
	$db->query("DROP DATABASE IF EXISTS `$database`");
	$db->query("CREATE DATABASE `$database`");

	$mockDb = initiateDb($database);
	$migrations = getMigrations();

	for($version = 0; $version < count($migrations); $version++) {
		apply($mockDb, $migrations[$version]);

		if(isset($migrations[$version + 1])) {
			apply($mockDb, $migrations[$version + 1]);
			rollbackToVersion($mockDb, $version + 1);
		}
	}

	for($version = count($migrations) - 1; $version >= 0; $version--) {
		rollbackToVersion($mockDb, $version);

		if($version > 0) {
			apply($mockDb, $migrations[$version]);
		}
	}

	$db->query("DROP DATABASE IF EXISTS `$database`");
}
