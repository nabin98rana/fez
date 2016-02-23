#!/bin/bash

DOCKERHOST=$(/sbin/ip route|awk '/default/ { print $3 }')

echo "${DOCKERHOST}	dev-fez.library.uq.edu.au" >> /etc/hosts

# enabled the line below, and put your IP in, if you want to debug command line scripts or background processes
#sed -i "s|xdebug.remote_connect_back=1|xdebug.remote_connect_back=1\nxdebug.remote_host<YOUR IP HERE\nxdebug.idekey=\"fez\"|" /etc/php.d/15-xdebug.ini

if [ "${APP_ENVIRONMENT}" == "testing" ]; then
  rm -f /etc/php.d/15-xdebug.ini
fi

exec /usr/sbin/php-fpm --nodaemonize
