#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Dumps Fez and Fedora databases to gzipped files, and loads the databases into staging. This script should be run on slave database servers only."
  echo
  echo "USAGE"
  echo "    mysql_dump.sh <MYSQL_DB_FEZ> <MYSQL_DB_FEDORA> <MYSQL_DUMP_DIR> <MYSQL_DB_FEZ_STAGING> <MYSQL_DB_FEDORA_STAGING>"
  echo
  echo "    MYSQL_DB_FEZ         = The Fez database."
  echo "    MYSQL_DB_FEDORA         = The Fedora database."
  echo "    MYSQL_DUMP_DIR          = The directory to dump the database files to."
  echo "    MYSQL_DB_FEZ_STAGING = The Fez staging database (limited to be on the same server currently)."
  echo "    MYSQL_DB_FEDORA_STAGING = The Fedora staging database (limited to be on the same server currently)."
  echo
  echo "    The script expects the MySQL username/password to be set as the environment variables MYSQL_USER / MYSQL_PASS respectively."
  echo
  exit
}

if [ "$#" -ne 5 ]; then
  usage
fi

MYSQL_DB_FEZ=$1
MYSQL_DB_FEDORA=$2
MYSQL_DUMP_DIR=$3
MYSQL_DB_FEZ_STAGING=$4
MYSQL_DB_FEDORA_STAGING=$5

if [ ! -d "${MYSQL_DUMP_DIR}" ]; then
  echo
  echo "ERROR"
  echo "    MYSQL_DUMP_DIR does not exist"
  usage
fi

cd ${MYSQL_DUMP_DIR}
rm -f *.gz

MYSQL_CMD="mysql -u${MYSQL_USER} -p${MYSQL_PASS}";
MYSQL_DUMP_CMD="mysqldump -u${MYSQL_USER} -p${MYSQL_PASS} --max_allowed_packet=2048M --quick --compress --opt --order-by-primary --single-transaction --force --skip-lock-tables"

${MYSQL_CMD} -e 'stop slave'
${MYSQL_DUMP_CMD} ${MYSQL_DB_FEDORA} > fedora3.sql
${MYSQL_DUMP_CMD} \
  --no-data ${MYSQL_DB_FEZ} \
  --tables fez_sessions fez_statistics_all fez_thomson_citations fez_scopus_citations \
  > fez.sql
${MYSQL_DUMP_CMD} \
  --ignore-table=${MYSQL_DB_FEZ}.fez_config \
  --ignore-table=${MYSQL_DB_FEZ}.fez_sessions \
  --ignore-table=${MYSQL_DB_FEZ}.fez_statistics_all \
  --ignore-table=${MYSQL_DB_FEZ}.fez_thomson_citations \
  --ignore-table=${MYSQL_DB_FEZ}.fez_scopus_citations \
  ${MYSQL_DB_FEZ} \
  >> fez.sql

if [ -f "fez.config.sql" ]; then
  cat fez.config.sql >> fez.sql
fi

${MYSQL_CMD} --compress ${MYSQL_DB_FEZ_STAGING} < fez.sql
${MYSQL_CMD} --compress ${MYSQL_DB_FEDORA_STAGING} < fedora3.sql

${MYSQL_CMD} -e 'start slave'

gzip fedora3.sql
gzip fez.sql
