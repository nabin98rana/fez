#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Loads a dumped Fez database into staging."
  echo
  echo "USAGE"
  echo "    mysql_staging.sh <MYSQL_DUMP_DIR> <MYSQL_DB_FEZ_STAGING> <MYSQL_HOST_FEZ_STAGING>"
  echo
  echo "    MYSQL_DUMP_DIR         = The directory to dump the database files to."
  echo "    MYSQL_DB_FEZ_STAGING   = The Fez staging database name."
  echo "    MYSQL_HOST_FEZ_STAGING = The Fez staging database host."
  echo
  echo "    The script expects the MySQL username/password to be set as the environment variables MYSQL_USER / MYSQL_PASS respectively."
  echo
  exit
}

if [ "$#" -ne 3 ]; then
  usage
fi

MYSQL_DUMP_DIR=$1
MYSQL_DB_FEZ_STAGING=$2
MYSQL_HOST_FEZ_STAGING=$3

if [ ! -d "${MYSQL_DUMP_DIR}" ]; then
  echo
  echo "ERROR"
  echo "    Dump directory does not exist"
  usage
fi

cd ${MYSQL_DUMP_DIR}

if [ ! -f "fez.sql.gz" ]; then
  echo
  echo "ERROR"
  echo "    Dump file does not exist"
  usage
fi

cp fez.sql.gz fezstaging.sql.gz
gunzip fezstaging.sql.gz

if [ -f "staging.fez.config.sql" ]; then
  cat staging.fez.config.sql >> fezstaging.sql
fi

MYSQL_CMD="mysql -u${MYSQL_USER} -p${MYSQL_PASS}";

${MYSQL_CMD} -h${MYSQL_HOST_FEZ_STAGING} --compress ${MYSQL_DB_FEZ_STAGING} < "fezstaging.sql"

rm -f "fezstaging.sql"
