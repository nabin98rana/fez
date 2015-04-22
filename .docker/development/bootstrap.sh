#!/bin/bash

set -xe

echo -e "\n--- Bootstrapping ---"
cd /var/app/current/.docker/development

echo '127.0.0.1   dev-fez.library.uq.edu.au' >> /etc/hosts

rm -Rf /etc/nginx/conf.d
cp -R etc/nginx/conf.d /etc/nginx/
cp etc/nginx/espace_rewrite_rules.conf /etc/nginx/

rm -Rf /etc/php-fpm.d
cp -R etc/php-fpm.d /etc/
