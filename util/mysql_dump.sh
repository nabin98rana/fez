#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Dumps eSpace and Fedora databases to gzipped files. This script should be run on slave database servers only."
  echo
  echo "USAGE"
  echo "    mysql_dump.sh <MYSQL_DB_ESPACE> <MYSQL_DB_FEDORA> <MYSQL_DUMP_DIR>"
  echo
  echo "    MYSQL_DB_ESPACE = The eSpace database."
  echo "    MYSQL_DB_FEDORA = The Fedora database."
  echo "    MYSQL_DUMP_DIR  = The directory to dump the database files to."
  echo
  echo "    The script expects the MySQL username/password to be set as the environment variables MYSQL_USER / MYSQL_PASS respectively."
  echo
  exit
}

if [ "$#" -ne 3 ]; then
  usage
fi

MYSQL_DB_ESPACE=$1
MYSQL_DB_FEDORA=$2
MYSQL_DUMP_DIR=$3

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
  --no-data ${MYSQL_DB_ESPACE} \
  --tables fez_sessions fez_statistics_all fez_thomson_citations fez_scopus_citations \
  > fez.sql
${MYSQL_DUMP_CMD} \
  --ignore-table=${MYSQL_DB_ESPACE}.fez_config \
  --ignore-table=${MYSQL_DB_ESPACE}.fez_sessions \
  --ignore-table=${MYSQL_DB_ESPACE}.fez_statistics_all \
  --ignore-table=${MYSQL_DB_ESPACE}.fez_thomson_citations \
  --ignore-table=${MYSQL_DB_ESPACE}.fez_scopus_citations \
  ${MYSQL_DB_ESPACE} \
  >> fez.sql
${MYSQL_CMD} -e 'start slave'

if [ -f "fez.config.sql" ]; then
  cat fez.config.sql >> fez.sql
fi

gzip fedora3.sql
gzip fez.sql
