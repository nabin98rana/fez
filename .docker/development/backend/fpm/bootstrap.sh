#!/bin/bash

DOCKERHOST=$(/sbin/ip route|awk '/default/ { print $3 }')

echo "${DOCKERHOST}	dev-fez.library.uq.edu.au" >> /etc/hosts

# Turn on XDEBUG via DGBPProxy
cp -fv /var/app/current/.docker/development/backend/fpm/etc/php.d/15-xdebug.ini /etc/php.d/15-xdebug.ini


if [ "${APP_ENVIRONMENT}" == "testing" ]; then
  rm -f /etc/php.d/15-xdebug.ini
fi

exec /usr/sbin/php-fpm --nodaemonize
