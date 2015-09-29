#!/bin/bash
echo 'SELECT version FROM '$MYSQL_TABLE' ORDER BY time DESC LIMIT 1;'
