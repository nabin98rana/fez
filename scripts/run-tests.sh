#!/bin/bash
#
# This script runs the behat tests
#
#   Usage: run-tests.sh
#

BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd ../ && pwd )
CONTAINER_BASE_DIR=/var/app/current
VIRTUAL_HOST=dev-fez.library.uq.edu.au

function waitForServices() {
    MAX_LOOPS=20
    i=0
    while true; do
      i=`expr ${i} + 1`
      if [[ ${i} -ge ${MAX_LOOPS} ]]; then
        echo "$(date) - Selenium still not reachable, giving up"
        exit 1
      fi
      SELENIUM_OK=$(curl -s http://${VIRTUAL_HOST}:4444/selenium-server/driver/?cmd=getLogMessages | grep "OK")
      if [[ "${SELENIUM_OK}" != "" ]]; then
        break
      fi
      echo "$(date) - waiting for Selenium..."
      sleep 1
    done

    i=0
    MYSQL_HEALTH_CMD="docker exec testing_fezdb_1 mysqladmin ping -hlocalhost -ufez -pfez"
    HEALTH_MSG=$(${MYSQL_HEALTH_CMD} 2>&1)
    while ! [[ -n "${HEALTH_MSG}" && ${HEALTH_MSG} != *"failed"* && ${HEALTH_MSG} != *"denied"* ]]; do
      i=`expr ${i} + 1`
      if [[ ${i} -ge ${MAX_LOOPS} ]]; then
        echo "$(date) - MySQL still not reachable, giving up"
        exit 1
      fi
      echo "$(date) - waiting for MySQL..."
      sleep 1
      HEALTH_MSG=$(${MYSQL_HEALTH_CMD} 2>&1)
    done
}

cd ${BASE_DIR}/.docker/testing

docker-compose up -d
waitForServices

if [[ ! -f "../../public/config.inc.php" ]]; then
    cp config.inc.php ../../public/
fi

echo Running tests..

docker exec testing_feztestrunner_1 sh -c 'cd '"'${CONTAINER_BASE_DIR}/tests/application'"' && php init.php schema'
UPGRADE_RES=$(curl -s http://${VIRTUAL_HOST}:9080/upgrade/index.php?upgradeOnly=1 | grep succeeded)
if [[ "${UPGRADE_RES}" == "" ]]; then
  echo "failed to run upgrade scripts! :("
  exit 1
fi

docker exec testing_feztestrunner_1 sh -c 'export AWS_ACCESS_KEY_ID='"'${AWS_ACCESS_KEY_ID}'"' && export AWS_SECRET_ACCESS_KEY='"'${AWS_SECRET_ACCESS_KEY}'"' && export FEZ_S3_CACHE_BUCKET='"'${FEZ_S3_CACHE_BUCKET}'"' && export FEZ_S3_BUCKET='"'${FEZ_S3_BUCKET}'"' && export FEZ_S3_SRC_PREFIX='"'${FEZ_S3_SRC_PREFIX}'"' && cd '"'${CONTAINER_BASE_DIR}/tests/application'"' && php init.php seed'

# Setup AWS
docker exec testing_feztestrunner_1 sh -c 'export AWS_ACCESS_KEY_ID='"'${AWS_ACCESS_KEY_ID}'"' && export AWS_SECRET_ACCESS_KEY='"'${AWS_SECRET_ACCESS_KEY}'"' && export FEZ_S3_CACHE_BUCKET='"'${FEZ_S3_CACHE_BUCKET}'"' && export FEZ_S3_BUCKET='"'${FEZ_S3_BUCKET}'"' && export FEZ_S3_SRC_PREFIX='"'${FEZ_S3_SRC_PREFIX}'"' && export AWS_CLOUDFRONT_KEY_PAIR_ID='"'${AWS_CLOUDFRONT_KEY_PAIR_ID}'"' && export AWS_CLOUDFRONT_PRIVATE_KEY_FILE='"'${AWS_CLOUDFRONT_PRIVATE_KEY_FILE}'"' && export AWS_CLOUDFRONT_FILE_SERVE_URL='"'${AWS_CLOUDFRONT_FILE_SERVE_URL}'"' && cd '"'${CONTAINER_BASE_DIR}/tests/application'"' && php init.php setupaws'

# Run a Fedora Bypass migration if prefix is set
docker exec testing_feztestrunner_1 sh -c 'export AWS_ACCESS_KEY_ID='"'${AWS_ACCESS_KEY_ID}'"' && export AWS_SECRET_ACCESS_KEY='"'${AWS_SECRET_ACCESS_KEY}'"' && export FEZ_S3_CACHE_BUCKET='"'${FEZ_S3_CACHE_BUCKET}'"' && export FEZ_S3_BUCKET='"'${FEZ_S3_BUCKET}'"' && export FEZ_S3_SRC_PREFIX='"'${FEZ_S3_SRC_PREFIX}'"' && export AWS_CLOUDFRONT_KEY_PAIR_ID='"'${AWS_CLOUDFRONT_KEY_PAIR_ID}'"' && export AWS_CLOUDFRONT_PRIVATE_KEY_FILE='"'${AWS_CLOUDFRONT_PRIVATE_KEY_FILE}'"' && export AWS_CLOUDFRONT_FILE_SERVE_URL='"'${AWS_CLOUDFRONT_FILE_SERVE_URL}'"' && cd '"'${CONTAINER_BASE_DIR}/tests/application'"' && php init.php migrate'

docker exec testing_feztestrunner_1 sh -c ''"'${CONTAINER_BASE_DIR}/tests/application/run-tests.sh'"' '"'${1}'"''
docker-compose stop
docker-compose rm -f -v
