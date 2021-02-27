#!/usr/bin/env bash
set -Eeuo pipefail

# Prepare DB data
while ! /usr/local/bin/mysqladmin ping -h"$DB_HOST" --silent; do
    echo "Waiting for mysql db..."
    sleep 1
done
echo "Importing demo SQL database..."
mysql --host=${DB_HOST} --user=${DB_USER} --password=${DB_PASSWORD} ${DB_NAME} < deploy/dump.sql

# Prepare Fork CMS
echo "Adding Fork CMS parameters.yml..."
cp app/config/parameters.yml.test app/config/parameters.yml
yq write --inplace app/config/parameters.yml 'parameters.[database.host]' '%env(DB_HOST)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.name]' '%env(DB_NAME)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.user]' '%env(DB_USER)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.password]' '%env(DB_PASSWORD)%'

# Start up Apache webserver
echo "Starting apache..."
apache2-foreground
