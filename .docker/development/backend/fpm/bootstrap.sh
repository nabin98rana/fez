#!/bin/bash

DOCKERHOST=$(/sbin/ip route|awk '/default/ { print $3 }')

echo "${DOCKERHOST}	dev-fez.library.uq.edu.au" >> /etc/hosts

BASE_DIR=/var/app/current

cd ${BASE_DIR}/.docker/development

#sed -i "s|xdebug.remote_enable=1|xdebug.remote_enable=0\nxdebug.remote_host=<your_ip_here>"

aws s3 cp s3://uql/fez/fez_staging_cloudfront_private_key.pem ${BASE_DIR}/data/
# for the mounts
aws s3 cp s3://uql/fez/espace.credentials ${BASE_DIR}/data/
aws s3 cp s3://uql/fez/libtools.credentials ${BASE_DIR}/data/
aws s3 cp s3://uql/fez/espace.fstab ${BASE_DIR}/data/
cat ${BASE_DIR}/data/espace.fstab >> /etc/fstab


if [ "${APP_ENVIRONMENT}" == "testing" ]; then
  rm -f /etc/php.d/15-xdebug.ini
fi

exec /usr/sbin/php-fpm --nodaemonize
