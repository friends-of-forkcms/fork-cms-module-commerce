#!/usr/bin/env bash
set -Eeuo pipefail

# Prepare DB data
while ! /usr/local/bin/mysqladmin ping -h"$DB_HOST" --silent; do
    echo "Waiting for mysql db to be up and running..."
    sleep 1
done
echo "Importing demo SQL database..."
mysql --host=${DB_HOST} --user=${DB_USER} --password=${DB_PASSWORD} ${DB_NAME} < deploy/dump.sql

# Prepare Fork CMS
bash ./deploy/prepare-forkcms.sh

# Start up Apache webserver
echo "Starting apache..."
apache2-foreground
