#!/usr/bin/env bash
set -Eeuo pipefail

while ! mysqladmin ping --host "$DB_HOST" --port "$DB_PORT" --silent; do
    echo "Waiting for mysql db to be up and running..."
    sleep 1
done

# Prepare Fork CMS
bash ./deploy/prepare-forkcms.sh

# Start up Apache webserver
echo "Starting apache..."
apache2-foreground
