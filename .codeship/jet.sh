#!/bin/bash

echo Starting test run..

i=0
MYSQL_HEALTH_CMD="mysqladmin ping -hfezdb -ufez -pfez"
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

cd /var/app/current/tests/application && php init.php

echo Starting upgrading..
UPGRADE_RES=$(curl -s http://fez/upgrade/index.php?upgradeOnly=1 | grep succeeded)
if [ "${UPGRADE_RES}" == "" ]; then
  echo "failed to run upgrade scripts! :("
  exit 1
fi

echo Seeding SQL data..

CONTAINER_DB_SEED_DIR=/var/app/current/.docker/development/backend/db/seed
mysql -uroot -pdevelopment -hfezdb mysql < ${CONTAINER_DB_SEED_DIR}/installdb.sql
mysql -uroot -pdevelopment -hfezdb fez < ${CONTAINER_DB_SEED_DIR}/citation.sql
mysql -uroot -pdevelopment -hfezdb fez < ${CONTAINER_DB_SEED_DIR}/cvs.sql
mysql -uroot -pdevelopment -hfezdb fez < ${CONTAINER_DB_SEED_DIR}/development.sql
mysql -uroot -pdevelopment -hfezdb fez < ${CONTAINER_DB_SEED_DIR}/workflows.sql
mysql -uroot -pdevelopment -hfezdb fez < ${CONTAINER_DB_SEED_DIR}/xsd.sql
mysql -uroot -pdevelopment -hfezdb fez < ${CONTAINER_DB_SEED_DIR}/jetsetup.sql
mysql -uroot -pdevelopment -hfezdb fez < ${CONTAINER_DB_SEED_DIR}/disablesolr.sql

echo Running tests..

cd /var/app/current/
./tests/application/run-tests.sh
#docker -v
#docker-compose -v