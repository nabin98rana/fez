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
docker exec development_fezdevelopmentrunner_1 sh -c 'cd '"'${CONTAINER_BASE_DIR}/tests/application'"' && php init.php'
UPGRADE_RES=$(curl -s http://${VIRTUAL_HOST}:8080/upgrade/index.php?upgradeOnly=1 | grep succeeded)
if [ "${UPGRADE_RES}" == "" ]; then
  exit 1
fi

CONTAINER_DB_SEED_DIR=${CONTAINER_BASE_DIR}/.docker/development/backend/db/seed
docker exec development_fezdb_1 sh -c 'mysql -uroot -pdevelopment -hlocalhost mysql < '"'${CONTAINER_DB_SEED_DIR}/installdb.sql'"''
docker exec development_fezdb_1 sh -c 'mysql -uroot -pdevelopment -hlocalhost fez < '"'${CONTAINER_DB_SEED_DIR}/citation.sql'"''
docker exec development_fezdb_1 sh -c 'mysql -uroot -pdevelopment -hlocalhost fez < '"'${CONTAINER_DB_SEED_DIR}/cvs.sql'"''
docker exec development_fezdb_1 sh -c 'mysql -uroot -pdevelopment -hlocalhost fez < '"'${CONTAINER_DB_SEED_DIR}/development.sql'"''
docker exec development_fezdb_1 sh -c 'mysql -uroot -pdevelopment -hlocalhost fez < '"'${CONTAINER_DB_SEED_DIR}/workflows.sql'"''
docker exec development_fezdb_1 sh -c 'mysql -uroot -pdevelopment -hlocalhost fez < '"'${CONTAINER_DB_SEED_DIR}/xsd.sql'"''

echo Done!
