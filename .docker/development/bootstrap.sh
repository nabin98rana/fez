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
mkdir -p ${DEV_BASE}/tmp/templates_c
mkdir -p ${DEV_BASE}/logs/backend/fpm
mkdir -p ${DEV_BASE}/logs/backend/nginx
mkdir -p ${DEV_BASE}/logs/fedora
mkdir -p ${DEV_BASE}/logs/fez
mkdir -p ${DEV_BASE}/logs/solr
