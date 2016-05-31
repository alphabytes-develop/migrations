<?php
function getDbMigrations($db) {
	$config = getConfig();

	return $db->query("
		SELECT * FROM `$config[table]` ORDER BY `version` ASC;
	")->fetchAll();
}
