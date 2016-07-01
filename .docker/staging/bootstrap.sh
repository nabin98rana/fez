#!/bin/bash

set -xe
BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd ../../ && pwd )

cd ${BASE_DIR}/.docker/staging

set +x
if [ "${WEBCRON_TOKEN}" != "" ]; then
  sed -i "s/WEBCRON_TOKEN/${WEBCRON_TOKEN}/" ${BASE_DIR}/.docker/staging/fez.cron
fi
if [ "${APP_ENVIRONMENT}" == "staging" ]; then
  aws s3 cp s3://uql/ecs/default/services/fezstaging/config.inc.php ${BASE_DIR}/public/config.inc.php
  aws s3 cp s3://uql/fez/fez_staging_cloudfront_private_key.pem ${BASE_DIR}/data/
  aws s3 cp ${BASE_DIR}/.docker/staging/fez.cron s3://uql/ecs/default/services/crond/cron.d/fezstaging
  aws s3 cp s3://uql-fez-staging/GeoIP.dat.gz /usr/share/GeoIP/GeoIP.dat.gz && /bin/gunzip -f /usr/share/GeoIP/GeoIP.dat.gz
  aws s3 cp s3://uql-fez-staging/GeoLiteCity.dat.gz /usr/share/GeoIP/GeoLiteCity.dat.gz && /bin/gunzip -f /usr/share/GeoIP/GeoLiteCity.dat.gz
  # Note this nginx ip restriction does NOT stop being getting through via cloudfront. CF is geoblocked to AUS and robots.txt will stop crawlers.
  cp ${BASE_DIR}/.docker/staging/fez-staging-allow.conf /etc/nginx/rules/fez-staging-allow.conf
  cp ${BASE_DIR}/.docker/staging/robots.txt ${BASE_DIR}/public/
  sed -i "s/server {/server {\n  include rules\/fez-staging-allow.conf;\n  deny all;\n/" /etc/nginx/conf.d/fez.conf
  sed -i "s/fastcgi_param  REDIRECT_STATUS    200;/fastcgi_param  REDIRECT_STATUS    200;\nfastcgi_buffer_size 5M;\nfastcgi_buffers 256 512k;\n/" /etc/nginx/fastcgi.conf
  chmod -R 777 ${BASE_DIR}/public/include/htmlpurifier/library/HTMLPurifier
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
