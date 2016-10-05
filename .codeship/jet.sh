#!/bin/bash

echo Starting test run..
CONTAINER_BASE_DIR=/var/app/current

# Run Fedora bypass in production branch only
if [[ ${CI_BRANCH} != "" && ${CI_BRANCH} == "master" ]]; then
  FEZ_S3_BUCKET=
  FEZ_S3_SRC_PREFIX=
else
  FEZ_S3_SRC_PREFIX=${CI_BRANCH}
fi
exit

i=0
MAX_LOOPS=100
MYSQL_HEALTH_CMD="mysqladmin ping -hfezdb -ufez -pfez"
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

cd ${CONTAINER_BASE_DIR}/.docker/testing
if [[ ! -f "../../public/config.inc.php" ]]; then
    cp config.inc.php ../../public/
fi

cd ${CONTAINER_BASE_DIR}/tests/application

echo Creating schema..
php init.php schema

echo Starting upgrading..
UPGRADE_RES=$(curl -s http://fez/upgrade/index.php?upgradeOnly=1 | grep succeeded)
if [[ "${UPGRADE_RES}" == "" ]]; then
  echo "failed to run upgrade scripts! :("
  exit 1
fi

echo Seeding SQL data..
php init.php seed
php init.php setupaws
php init.php migrate

echo Running tests.. $1
./run-tests.sh $1
