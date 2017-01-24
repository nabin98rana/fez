#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Dumps Fez database citation tables and copies the compressed export to S3. This script should be run on slave database servers only."
  echo
  echo "USAGE"
  echo "    mysql_dump_aws_citations.sh <APP_ENV> <MYSQL_DUMP_DIR> <MYSQL_DB_FEZ> <FEZ_URL>"
  echo
  echo "    APP_ENV        = The running environment (e.g. production)"
  echo "    MYSQL_DUMP_DIR = The directory to dump the database files to."
  echo "    MYSQL_DB_FEZ   = The Fez database."
  echo "    FEZ_URL        = The Fez URL."
  echo
  echo "    The script expects the MySQL username/password to be set as the environment variables MYSQL_USER / MYSQL_PASS respectively,"
  echo "    and S3_KEY / S3_SECRET which provide access to the S3 bucket to store the dumped file."
  echo
  exit
}

if [ "$#" -ne 4 ]; then
  usage
fi

APP_ENV=$1
MYSQL_DUMP_DIR=$2
MYSQL_DB_FEZ=$3
FEZ_URL=$4

if [ ! -d "${MYSQL_DUMP_DIR}" ]; then
  echo
  echo "ERROR"
  echo "    MYSQL_DUMP_DIR does not exist"
  usage
fi

cd ${MYSQL_DUMP_DIR}
rm -f fez${APP_ENV}-citations.tar.gz

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
    --lines-terminated-by '\n' \
    ${MYSQL_DB_FEZ} \
    --single-transaction \
    --order-by-primary \
    --add-drop-table \
    --routines=0 \
    --triggers=0 \
    --events=0 \
    -u${MYSQL_USER} \
    -p${MYSQL_PASS} \
    fez_scopus_citations \
    fez_scopus_citations_cache \
    fez_thomson_citations \
    fez_thomson_citations_cache

${MYSQL_CMD} -e 'start slave'

tar -zcvf fez${APP_ENV}-citations.tar.gz export
rm -Rf export

bucket="uql-fez-${APP_ENV}-cache"
file="fez${APP_ENV}-citations.tar.gz"
resource="/${bucket}/${file}"
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
    https://${bucket}.s3.amazonaws.com/${file}

wget -O - "${FEZ_URL}/api/cron_register_bgp.php?file=bgp_db_load_citations&class=BackgroundProcess_Db_Load_Citations&token=${WEBCRON_TOKEN}"
