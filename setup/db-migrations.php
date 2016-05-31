<?php
$dbMigrations = $db->query("
	SELECT * FROM `$config[table]` ORDER BY `version` ASC;
")->fetchAll();