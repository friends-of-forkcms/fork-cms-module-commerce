#!/usr/bin/env bash
set -Eeuo pipefail

echo "Importing fresh Fork CMS database..."
mysql --host=${DB_HOST} --user=${DB_USER} --password=${DB_PASSWORD} ${DB_NAME} < deploy/fresh-forkcms-install.sql

# Prepare Fork CMS parameters.yml
echo "Adding Fork CMS parameters.yml..."
cp app/config/parameters.yml.test app/config/parameters.yml
yq write --inplace app/config/parameters.yml 'parameters.[database.host]' '%env(DB_HOST)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.name]' '%env(DB_NAME)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.user]' '%env(DB_USER)%'
yq write --inplace app/config/parameters.yml 'parameters.[database.password]' '%env(DB_PASSWORD)%'
yq write --inplace app/config/parameters.yml 'parameters.[session.cookie_secure]' 'true'
yq write --inplace app/config/parameters.yml 'parameters.[site.domain]' '%env(SITE_DOMAIN)%'
yq write --inplace app/config/parameters.yml 'parameters.[site.protocol]' 'https'

# Prepare a god avatar. The god avatar is normally installed during the installation process.
echo "Restore the missing god avatar"
curl -sLo ./src/Frontend/Files/Users/avatars/source/god.png https://github.com/forkcms.png?size=128
curl -sLo ./src/Frontend/Files/Users/avatars/128x128/god.png https://github.com/forkcms.png?size=128
curl -sLo ./src/Frontend/Files/Users/avatars/64x64/god.png https://github.com/forkcms.png?size=64
curl -sLo ./src/Frontend/Files/Users/avatars/32x32/god.png https://github.com/forkcms.png?size=32
mysql --host=${DB_HOST} --user=${DB_USER} --password=${DB_PASSWORD} ${DB_NAME} -e 'UPDATE users_settings SET value = REPLACE(value, "god.jpg", "god.png") WHERE name = "avatar"'

# Install the module's dependencies
echo "Installing module composer dependencies..."
composer require --no-scripts \
    'php:^7.4' \
    'tetranz/select2entity-bundle:v2.10.1' \
    'knplabs/knp-snappy-bundle:v1.6.0' \
    'h4cc/wkhtmltopdf-amd64:^0.12.4' \
    'gedmo/doctrine-extensions:^3.0' \
    'jeroendesloovere/sitemap-bundle:^2.0' \
    'moneyphp/money:v3.3.1'
composer require --no-scripts --dev 'doctrine/doctrine-fixtures-bundle:^3.4' 'zenstruck/foundry:^1.8'

# Install the sitemap module which is a module dependency at the moment
curl -sL https://github.com/friends-of-forkcms/fork-cms-module-sitemaps/archive/master.tar.gz | tar xz --strip-components 1
bin/console forkcms:install:module Sitemaps

# Install the Commerce module
bin/console forkcms:install:module Commerce

# Setup the CMS for our demo (install demo theme, add widgets, ...)
mysql --host=${DB_HOST} --user=${DB_USER} --password=${DB_PASSWORD} ${DB_NAME} < deploy/prepare-forkcms-db.sql

# Apply a patch to add our bundles to AppKernel and configure the config.yml
patch -p1 < deploy/prepare-forkcms-php.patch

# Generate fixtures data
bin/console doctrine:fixtures:load --append --group=module-commerce

# Generate thumbnails cache from LiipImagineBundle
# Disabled because too slow on preview env
# bin/console liip:imagine:cache:resolve src/Frontend/Files/MediaLibrary/**/* || true

# After modules were installed, we need to make sure the Apache user has ownership of the var directory.
chown -R www-data:www-data /var/www/html/var/

# Final cache clear
bin/console forkcms:cache:clear
