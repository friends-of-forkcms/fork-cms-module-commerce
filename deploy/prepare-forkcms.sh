#!/usr/bin/env bash
set -Eeuo pipefail

# Prepare Fork CMS configuration
echo "Adding Fork CMS parameters.yml..."
cp app/config/parameters.yml.test app/config/parameters.yml
yq write --inplace app/config/parameters.yml 'parameters.[database.host]' '%env(DB_HOST)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.name]' '%env(DB_NAME)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.user]' '%env(DB_USER)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.password]' '%env(DB_PASSWORD)%'

# Prepare a god avatar. The god avatar is normally installed during the installation process.
echo "Restore the missing god avatar"
curl -sLo ./src/Frontend/Files/Users/avatars/source/god.png https://github.com/forkcms.png?size=128
curl -sLo ./src/Frontend/Files/Users/avatars/128x128/god.png https://github.com/forkcms.png?size=128
curl -sLo ./src/Frontend/Files/Users/avatars/64x64/god.png https://github.com/forkcms.png?size=64
curl -sLo ./src/Frontend/Files/Users/avatars/32x32/god.png https://github.com/forkcms.png?size=32
mysql --host=${DB_HOST} --user=${DB_USER} --password=${DB_PASSWORD} ${DB_NAME} -e 'UPDATE users_settings SET value = REPLACE(value, "god.jpg", "god.png") WHERE name = "avatar"'
