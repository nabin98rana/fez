#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Dumps Fez database and copies the compressed export to S3."
  echo
  echo "USAGE"
  echo "    mysql_dump_aws.sh"
  echo
  echo "    The script expects the following to be set as the environment variables:"
  echo
  echo "    MYSQL_DUMP_DIR    = The directory to dump the database files to."
  echo "    MYSQL_DB_HOST_FEZ = The Fez database host"
  echo "    MYSQL_DB_FEZ      = The Fez database name."
  echo "    MYSQL_USER        = The Fez database user."
  echo "    MYSQL_PASS        = The Fez database password."
  echo
  exit
}

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

mysqldump \
    --tab=${MYSQL_DUMP_DIR}/export \
    --fields-terminated-by ',' \
    --fields-enclosed-by '"' \
    --lines-terminated-by '\n' \
    ${MYSQL_NAME} \
    --single-transaction \
    --order-by-primary \
    --add-drop-table \
    --routines=0 \
    --triggers=0 \
    --events=0 \
    -h${MYSQL_HOST} \
    -u${MYSQL_USER} \
    -p${MYSQL_PASS}

mysqldump \
    --routines \
    --no-create-info \
    --no-data \
    --no-create-db \
    --skip-opt ${MYSQL_NAME} \
    -h${MYSQL_HOST} \
    -u${MYSQL_USER} \
    -p${MYSQL_PASS} \
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
