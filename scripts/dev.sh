#!/bin/bash
#
# This script creates a new running dev environment from scratch.
# WARNING! It will remove any existing dev environment.
#
#   Usage: dev.sh
#

BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd ../ && pwd )
CONTAINER_BASE_DIR=/var/app/current
VIRTUAL_HOST=dev-fez.library.uq.edu.au
NET_IF=`netstat -rn | awk '/^0.0.0.0/ {thif=substr($0,74,10); print thif;} /^default.*UG/ {thif=substr($0,65,10); print thif;}'`
NET_IP=`ifconfig ${NET_IF} | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'`
export XDEBUG_REMOTE_HOST=${NET_IP}

function waitForServices() {
    MAX_LOOPS="20"
    i=0
    MYSQL_HEALTH_CMD="docker exec development_fezdb_1 mysqladmin ping -hlocalhost -ufez -pfez"
    HEALTH_MSG=$(${MYSQL_HEALTH_CMD} 2>&1)
    while ! [[ -n "${HEALTH_MSG}" && ${HEALTH_MSG} != *"failed"* && ${HEALTH_MSG} != *"denied"* ]]; do
      i=`expr ${i} + 1`
      if [ ${i} -ge ${MAX_LOOPS} ]; then
        echo "$(date) - MySQL still not reachable, giving up"
        exit 1
      fi
      echo "$(date) - waiting for MySQL..."
      sleep 1
      HEALTH_MSG=$(${MYSQL_HEALTH_CMD} 2>&1)
    done
}

cd ${BASE_DIR}/.docker/development

docker-compose stop
docker-compose rm -f -v

docker-compose up -d
waitForServices

if [ ! -f "../../public/config.inc.php" ]; then
    cp config.inc.php ../../public/
fi

echo Creating dev environment..
docker exec development_fezdevelopmentrunner_1 sh -c 'cd '"'${CONTAINER_BASE_DIR}/tests/application'"' && php init.php schema'
UPGRADE_RES=$(curl -s http://${VIRTUAL_HOST}:8080/upgrade/index.php?upgradeOnly=1 | grep succeeded)
if [ "${UPGRADE_RES}" == "" ]; then
  echo "failed to run upgrade scripts! :("
  exit 1
fi

docker exec development_fezdevelopmentrunner_1 sh -c 'export AWS_ACCESS_KEY_ID='"'${AWS_ACCESS_KEY_ID}'"' && export AWS_SECRET_ACCESS_KEY='"'${AWS_SECRET_ACCESS_KEY}'"' && export FEZ_S3_BUCKET='"'${FEZ_S3_BUCKET}'"' && export FEZ_S3_SRC_PREFIX='"'${FEZ_S3_SRC_PREFIX}'"' && export AWS_CLOUDFRONT_KEY_PAIR_ID='"'${AWS_CLOUDFRONT_KEY_PAIR_ID}'"' && export AWS_CLOUDFRONT_PRIVATE_KEY_FILE='"'${AWS_CLOUDFRONT_PRIVATE_KEY_FILE}'"' && export AWS_CLOUDFRONT_FILE_SERVE_URL='"'${AWS_CLOUDFRONT_FILE_SERVE_URL}'"' && cd '"'${CONTAINER_BASE_DIR}/tests/application'"' && php init.php seed'

# Optionally seed dev by running tests tagged with @seed
# docker exec development_fezdevelopmentrunner_1 sh -c 'cd '"'${CONTAINER_BASE_DIR}/tests/application'"' && ./seed-development.sh'

echo Done!
