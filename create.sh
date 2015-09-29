#!/bin/bash

echo 'CREATE TABLE IF NOT EXISTS `'$MYSQL_TABLE'` ('
echo '  `version` int(11) unsigned NOT NULL,'
echo '  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
echo '  PRIMARY KEY (`version`)'
echo ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'
