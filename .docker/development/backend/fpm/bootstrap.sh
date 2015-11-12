#!/bin/bash

DOCKERHOST=$(/sbin/ip route|awk '/default/ { print $3 }')

echo "${DOCKERHOST}	dev-fez.library.uq.edu.au" >> /etc/hosts

exec /usr/sbin/php-fpm --nodaemonize
