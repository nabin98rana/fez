#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Dumps Fez database and copies the compressed export to S3. This script should be run on slave database servers only."
  echo
  echo "USAGE"
  echo "    mysql_dump_aws.sh <APP_ENV> <MYSQL_DUMP_DIR> <MYSQL_DB_FEZ> <FEZ_URL>"
  echo
  echo "    APP_ENV        = The running environment (e.g. production)"
  echo "    MYSQL_DUMP_DIR = The directory to dump the database files to."
  echo "    MYSQL_DB_FEZ   = The Fez database."
  echo
  echo "    The script expects the MySQL username/password to be set as the environment variables MYSQL_USER / MYSQL_PASS respectively,"
  echo "    and S3_KEY / S3_SECRET which provide access to the S3 bucket to store the dumped file."
  echo
  exit
}

if [ "$#" -ne 3 ]; then
  usage
fi

APP_ENV=$1
MYSQL_DUMP_DIR=$2
MYSQL_DB_FEZ=$3

if [ ! -d "${MYSQL_DUMP_DIR}" ]; then
  echo
  echo "ERROR: MYSQL_DUMP_DIR does not exist"
  usage
fi

cd ${MYSQL_DUMP_DIR}

if [ -d "export" ]; then
    rm -Rf export
fi

mkdir export
chown root:mysql export
chmod 775 export

MYSQL_CMD="mysql -u${MYSQL_USER} -p${MYSQL_PASS}"
mysqldump \
    --tab=${MYSQL_DUMP_DIR}/export \
    --fields-terminated-by ',' \
    --fields-enclosed-by '"' \
    --lines-terminated-by '\n' \
    ${MYSQL_DB_FEZ} \
    --single-transaction \
    --order-by-primary \
    --add-drop-table \
    --routines=0 \
    --triggers=0 \
    --events=0 \
    -u${MYSQL_USER} \
    -p${MYSQL_PASS}

mysqldump \
    --routines \
    --no-create-info \
    --no-data \
    --no-create-db \
    --skip-opt ${MYSQL_DB_FEZ} \
    > export/spandtriggers.sql

rm -f export/__*
rm -f export/fez_background_process.txt
rm -f export/fez_background_process_pids.txt
rm -f export/fez_statistics_all.txt
rm -f export/fez_statistics_buffer.txt
rm -f export/fez_sessions.txt
rm -f export/fez_statistics_all.txt
rm -f export/fez_thomson_citations.txt
rm -f export/fez_thomson_citations_cache.txt
rm -f export/fez_scopus_citations.txt
rm -f export/fez_scopus_citations_cache.txt
