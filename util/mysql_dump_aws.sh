#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Dumps Fez database and copies the compressed export to S3. This script should be run on slave database servers only."
  echo
  echo "USAGE"
  echo "    mysql_dump_aws.sh <MYSQL_DUMP_DIR> <MYSQL_DB_FEZ> <FEZ_STAGING_SITE>"
  echo
  echo "    MYSQL_DUMP_DIR     = The directory to dump the database files to."
  echo "    MYSQL_DB_FEZ       = The Fez database."
  echo
  echo "    The script expects the MySQL username/password to be set as the environment variables MYSQL_USER / MYSQL_PASS respectively,"
  echo "    and S3_KEY / S3_SECRET which provide access to the S3 bucket to store the dumped file."
  echo
  exit
}

if [ "$#" -ne 3 ]; then
  usage
fi

MYSQL_DUMP_DIR=$1
MYSQL_DB_FEZ=$2
FEZ_STAGING_SITE=$3

if [ ! -d "${MYSQL_DUMP_DIR}" ]; then
  echo
  echo "ERROR"
  echo "    MYSQL_DUMP_DIR does not exist"
  usage
fi

cd ${MYSQL_DUMP_DIR}
rm -f fezstaging.tar.gz

if [ -d "export" ]; then
    rm -Rf export
fi

mkdir export
chown root:mysql export
chmod 775 export

MYSQL_CMD="mysql -u${MYSQL_USER} -p${MYSQL_PASS}"
${MYSQL_CMD} -e 'stop slave'
mysqldump \
    --tab=${MYSQL_DUMP_DIR}/export \
    --fields-terminated-by ',' \
    --fields-enclosed-by '"' \
    --lines-terminated-by '\r\n' \
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
    > export/spandtriggers.sql

${MYSQL_CMD} -e 'start slave'

rm -f export/__*
rm -f export/fez_config.sql
rm -f export/fez_config.txt
rm -f export/fez_statistics_all.txt
rm -f export/fez_statistics_buffer.txt
rm -f export/fez_sessions.txt
rm -f export/fez_statistics_all.txt
rm -f export/fez_thomson_citations.txt
rm -f export/fez_thomson_citations_cache.txt
rm -f export/fez_scopus_citations.txt
rm -f export/fez_scopus_citations_cache.txt

cp staging.fez.config.sql export/fez_config.sql

tar -zcvf fezstaging.tar.gz export
rm -Rf export

bucket="uql"
file="fezstaging.tar.gz"
resource="/${bucket}/fez/${file}"
contentType="application/x-compressed-tar"
dateValue=`date -R`
stringToSign="PUT\n\n${contentType}\n${dateValue}\n${resource}"
s3Key=${S3_KEY}
s3Secret=${S3_SECRET}
signature=`echo -en ${stringToSign} | openssl sha1 -hmac ${s3Secret} -binary | base64`
curl -X PUT -T "${file}" \
    -H "Host: ${bucket}.s3.amazonaws.com" \
    -H "Date: ${dateValue}" \
    -H "Content-Type: ${contentType}" \
    -H "Authorization: AWS ${s3Key}:${signature}" \
    https://${bucket}.s3.amazonaws.com/fez/${file}

curl ${FEZ_STAGING_SITE}/misc/staging.php
