#!/bin/bash

set -xe
BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd ../../ && pwd )

cd ${BASE_DIR}/.docker/production

set +x
if [ "${WEBCRON_TOKEN}" != "" ]; then
  sed -i "s/WEBCRON_TOKEN/${WEBCRON_TOKEN}/" ${BASE_DIR}/.docker/production/fez.cron
fi

aws s3 cp s3://uql/ecs/default/services/fezproduction/config.inc.php ${BASE_DIR}/public/config.inc.php --only-show-errors
aws s3 cp s3://uql/fez/fez_production_cloudfront_private_key.pem ${BASE_DIR}/data/ --only-show-errors
aws s3 cp s3://uql-fez-production-cache/GeoIP.dat.gz /usr/share/GeoIP/GeoIP.dat.gz --only-show-errors && /bin/gunzip -f /usr/share/GeoIP/GeoIP.dat.gz
aws s3 cp s3://uql-fez-production-cache/GeoLiteCity.dat.gz /usr/share/GeoIP/GeoLiteCity.dat.gz --only-show-errors && /bin/gunzip -f /usr/share/GeoIP/GeoLiteCity.dat.gz
cp ${BASE_DIR}/.docker/production/robots.txt ${BASE_DIR}/public/
chmod -R 777 ${BASE_DIR}/public/include/htmlpurifier/library/HTMLPurifier

rm -f /etc/php.d/15-xdebug.ini

if [ "${NEWRELIC_LICENSE}" != "" ]; then
  #sed -i "s/#fastcgi_param PHP_VALUE \"newrelic.license=NEWRELIC_LICENSE/fastcgi_param PHP_VALUE \"newrelic.license=${NEWRELIC_LICENSE}/" /etc/nginx/conf.d/fez.conf
fi
set -x

if [ "${BGP_ID}" != "" ]; then
  php ${BASE_DIR}/public/misc/run_background_process.php ${BGP_ID}
else
  aws s3 cp ${BASE_DIR}/.docker/production/fez.cron s3://uql/ecs/default/services/crond/cron.d/fezproduction --only-show-errors
  exec /usr/sbin/php-fpm --nodaemonize
fi
