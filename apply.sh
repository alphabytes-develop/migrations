#!/bin/bash
echo 'INSERT INTO `'$MYSQL_TABLE'` (`version`) VALUES ('$1');'
