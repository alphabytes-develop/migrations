<?php
$migrations = array_values(array_filter(scandir($them . $config['migrations']), function($entry) { return $entry !== '.' && $entry !== '..'; }));

/* Sort them because file names are strings and we need numerical ordering */
natsort($migrations);

/* Parse them into full paths for up/down */
$migrations = array_map(function($migration) use($them, $config) {
	return [
		'no' => intval($migration),
		'up' => $them . $config['migrations'] . '/' . $migration . '/up.sql',
		'dir' => $them . $config['migrations'] . '/' . $migration,
		'down' => $them . $config['migrations'] . '/' . $migration . '/down.sql'
	];
}, $migrations);


$last = 0;
foreach($migrations as &$migration) {
	if($migration['no'] !== ++$last) {
		echo "Missing migration: $last\n";
		exit(1);
	}

	if(!is_file($migration['up'])) {
		echo "Missing file: $migration[up]\n";
		exit(1);
	}

	if(!is_file($migration['down'])) {
		echo "Missing file: $migration[down]\n";
		exit(1);
	}

	$output = [];
	$return = 0;
	$migration['hash'] = exec("git log -n 1 --pretty=format:%H -- '$migration[dir]'", $output, $return);
	$migration['upSql'] = file_get_contents($migration['up']);
	$migration['downSql'] = file_get_contents($migration['down']);

	if($return !== 0) {
		echo "Could not determine git version of directory: '$migration[dir]'\n";
		exit(1);
	}
}