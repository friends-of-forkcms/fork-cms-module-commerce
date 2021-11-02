#!/bin/bash
set -e

# Create a second database _test to run integration tests
mysql -u "root" -p"$MYSQL_ROOT_PASSWORD" <<-EOSQL
CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE};
CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE}_test;
GRANT ALL PRIVILEGES ON ${MYSQL_DATABASE}_test.* TO ${MYSQL_USER}@'%';
FLUSH PRIVILEGES;
EOSQL
