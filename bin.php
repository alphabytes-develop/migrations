#!/usr/bin/env php
<?php
require_once(__DIR__ . '/index.php');

function printInfo() {
	echo 'The following tasks are available:
	test <db>       - Tests the current migrations against the given database
	apply <dryrun>  - Applies all migrations so the database represents the current directory state
	status          - Prints the last applied migration
	rollback <n>    - Rolls back the last n migrations. n defaults to 1
';
}

if(count($argv) < 2) {
	printInfo();
	exit(1);
}

switch($argv[1]) {
	case 'test':
		if(count($argv) < 3) {
			printInfo();
			exit(1);
		}

		if($argv[2] === $config['database']) {
			echo "You cannot run a test against the same database that is set in your config!\n";
			exit(1);
		}

		$db->query("DROP DATABASE IF EXISTS `$argv[2]`");
		$db->query("CREATE DATABASE `$argv[2]`");

		$config['database'] = $argv[2];
		require(__DIR__ . '/setup/db.php');
		require(__DIR__ . '/setup/db-migrations.php');

		applyAll();
		require(__DIR__ . '/setup/db-migrations.php');
		rollbackToVersion(0);
		$db->query("DROP DATABASE IF EXISTS `$argv[2]`");
		break;
	case 'apply':
		applyAll(count($argv) === 2 ? false : true);
		break;
	case 'status':
		if(count($dbMigrations) > 0) {
			$migration = $dbMigrations[count($dbMigrations) - 1];
			echo "$migration[version]@$migration[hash]\n";
		} else {
			echo "No migrations applied yet!\n";
		}

		break;
	case 'rollback':
		$num = 1;
		if(count($argv) > 2) {
			$num = $argv[2];
		}

		rollbackToVersion(count($dbMigrations) - $num);
		break;
	default:
		printInfo();
		exit(1);
		break;
}