#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Dumps Fez database and copies the compressed export to S3. This script should be run on slave database servers only."
  echo
  echo "USAGE"
  echo "    mysql_dump_aws.sh <MYSQL_DUMP_DIR> <MYSQL_DB_FEZ>"
  echo
  echo "    MYSQL_DUMP_DIR     = The directory to dump the database files to."
  echo "    MYSQL_DB_FEZ       = The Fez database."
  echo
  echo "    The script expects the MySQL username/password to be set as the environment variables MYSQL_USER / MYSQL_PASS respectively."
  echo
  exit
}

if [ "$#" -ne 2 ]; then
  usage
fi

MYSQL_DUMP_DIR=$1
MYSQL_DB_FEZ=$2

if [ ! -d "${MYSQL_DUMP_DIR}" ]; then
  echo
  echo "ERROR"
  echo "    MYSQL_DUMP_DIR does not exist"
  usage
fi

cd ${MYSQL_DUMP_DIR}
rm -f *.gz

if [ ! -d "${MYSQL_DUMP_DIR}/export" ]; then
    mkdir ${MYSQL_DUMP_DIR}/export
    chown root:mysql ${MYSQL_DUMP_DIR}/export
fi

MYSQL_CMD="mysql -u${MYSQL_USER} -p${MYSQL_PASS}"
${MYSQL_CMD} -e 'stop slave'
mysqldump \
    --tab=${MYSQL_DUMP_DIR}/export \
    --fields-terminated-by ',' \
    --fields-enclosed-by '"' \
    --lines-terminated-by 0x0d0a \
    ${MYSQL_DB_FEZ} \
    --single-transaction \
    --order-by-primary \
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
    > ${MYSQL_DUMP_DIR}/export/spandtriggers.sql

${MYSQL_CMD} -e 'start slave'

rm -f ${MYSQL_DUMP_DIR}/export/__*
rm -f ${MYSQL_DUMP_DIR}/export/fez_statistics_all.txt
rm -f ${MYSQL_DUMP_DIR}/export/fez_sessions.txt
rm -f ${MYSQL_DUMP_DIR}/export/fez_statistics_all.txt
rm -f ${MYSQL_DUMP_DIR}/export/fez_thomson_citations.txt
rm -f ${MYSQL_DUMP_DIR}/export/fez_thomson_citations_cache.txt
rm -f ${MYSQL_DUMP_DIR}/export/fez_scopus_citations.txt
rm -f ${MYSQL_DUMP_DIR}/export/fez_scopus_citations_cache.txt

cp ${MYSQL_DUMP_DIR}/staging.fez.config.sql ${MYSQL_DUMP_DIR}/export/config.sql

tar -zcvf fezstaging.tar.gz ${MYSQL_DUMP_DIR}/export
#aws s3 cp fezstaging.tar.gz s3://uql/fez/fezstaging.tar.gz
