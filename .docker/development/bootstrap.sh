#!/bin/bash

set -xe

DEV_BASE=/var/app/current/.docker/development

echo -e "\n--- Bootstrapping ---"
cd ${DEV_BASE}

echo '127.0.0.1   dev-fez.library.uq.edu.au' >> /etc/hosts

rm -Rf /etc/nginx/conf.d
cp -R etc/nginx/conf.d /etc/nginx/
cp etc/nginx/espace_rewrite_rules.conf /etc/nginx/

rm -Rf /etc/php-fpm.d
cp -R etc/php-fpm.d /etc/

# Directory for Fedora direct data
mkdir -p /espace/data

# Create the tmp and logs directories
mkdir -p ${DEV_BASE}/tmp/cache
mkdir -p ${DEV_BASE}/tmp/solr_upload
mkdir -p ${DEV_BASE}/tmp/templates_c
mkdir -p ${DEV_BASE}/tmp/xdebug
mkdir -p ${DEV_BASE}/logs/backend/fpm
mkdir -p ${DEV_BASE}/logs/backend/nginx
mkdir -p ${DEV_BASE}/logs/fedora_tomcat
mkdir -p ${DEV_BASE}/logs/fedora
mkdir -p ${DEV_BASE}/logs/fez
mkdir -p ${DEV_BASE}/logs/solr

cd ${DEV_BASE}/../../public/
if [ ! -h  solr_upload ]; then
  ln -s ../.docker/development/tmp/solr_upload .
fi
cd ${DEV_BASE}

yum install -y php56u-pecl-xdebug

cat >> /etc/php.d/15-xdebug.ini  << EOF
xdebug.remote_autostart=1
xdebug.remote_connect_back=1
xdebug.remote_enable=1
xdebug.remote_port=9000
EOF

# These modules are causing errors, and aren't required
rm -f /etc/php.d/20-mssql.ini
rm -f /etc/php.d/30-pdo_dblib.ini

sed -i "s/memory_limit = 128M/memory_limit = 800M/" /etc/php.ini
