#!/usr/bin/env bash
set -Eeuo pipefail

# Config
MYSQL_DATABASE=forkcms
MYSQL_USERNAME=forkcms
MYSQL_PASSWORD=forkcms

# MySQL
cat /etc/mysql/conf.d/mysql.cnf

whoami
cat /etc/passwd
# ls -al /var/lib/mysql/ /var/run/mysqld /var/log/mysql
which mysql
which mysqld

service mysql start
#mysqld_safe
ps aux
mysql -u root -e "create database ${MYSQL_DATABASE};"
mysql -u root -p=  ${MYSQL_DATABASE} < deploy/dump.sql
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_USERNAME}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';"
mysql -u root -e "FLUSH PRIVILEGES;"
mysql -uforkcms -pforkcms -h127.0.0.1 -e "SELECT 1;" # Test connection

# Prepare Fork CMS
echo "Adding Fork CMS config/parameters.yml..."
cp app/config/parameters.yml.test app/config/parameters.yml
yq write --inplace app/config/parameters.yml 'parameters.[database.host]' '127.0.0.1'
yq write --inplace app/config/parameters.yml 'parameters.[database.user]' "${MYSQL_USERNAME}"
yq write --inplace app/config/parameters.yml 'parameters.[database.password]' "${MYSQL_PASSWORD}"

# Apache
a2enmod rewrite
service apache2 start
tail -f /var/log/apache2/access.log
