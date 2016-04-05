#!/bin/bash

set -xe
BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd ../../ && pwd )

cd ${BASE_DIR}/.docker/production

set +x
if [ "${WEBCRON_TOKEN}" != "" ]; then
  sed -i "s/WEBCRON_TOKEN/${WEBCRON_TOKEN}/" ${BASE_DIR}/.docker/production/fez.cron
fi
if [ "${APP_ENVIRONMENT}" == "production" ]; then
  aws s3 cp s3://uql/ecs/default/services/fez/config.php ${BASE_DIR}/public/config.php
  aws s3 cp ${BASE_DIR}/.docker/production/fez.cron s3://uql/ecs/default/services/crond/cron.d/fez
else
  cp ${BASE_DIR}/.docker/testing/config.inc.php /var/app/current/public/config.inc.php
fi

rm -f /etc/php.d/15-xdebug.ini

if [ "${NEWRELIC_LICENSE}" != "" ]; then
  sed -i "s/NEWRELIC_LICENSE/${NEWRELIC_LICENSE}/" /etc/nginx/conf.d/fez.conf
fi
set -x

if [ "${BGP_ID}" != "" ]; then
  php ${BASE_DIR}/public/misc/run_background_process.php ${BGP_ID}
else
  exec /usr/sbin/php-fpm --nodaemonize
fi
