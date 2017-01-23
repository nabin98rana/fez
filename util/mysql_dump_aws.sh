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
rm -f fez${APP_ENV}.tar.gz

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
    -p${MYSQL_PASS}

mysqldump \
    --routines \
    --no-create-info \
    --no-data \
    --no-create-db \
    --skip-opt ${MYSQL_DB_FEZ} \
    > export/spandtriggers.sql

${MYSQL_CMD} -e 'start slave'

cp export/fez_datastream_info.sql export/fez_datastream_info_exported.sql
cp export/fez_datastream_info.txt export/fez_datastream_info_exported.txt
sed -i -- "s/fez_datastream_info/fez_datastream_info_exported/" export/fez_datastream_info_exported.sql
sed -i -- "s/fez_datastream_info/fez_datastream_info_exported/" export/fez_datastream_info_exported.txt

rm -f export/__*
rm -f export/*__shadow.txt
rm -f export/fez_background_process.txt
rm -f export/fez_background_process_pids.txt
rm -f export/fez_datastream_info.sql
rm -f export/fez_datastream_info.txt
rm -f export/fez_datastream_info__shadow.txt
rm -f export/fez_statistics_all.txt
rm -f export/fez_statistics_buffer.txt
rm -f export/fez_sessions.txt
rm -f export/fez_statistics_all.txt
rm -f export/fez_thomson_citations.txt
rm -f export/fez_thomson_citations_cache.txt
rm -f export/fez_scopus_citations.txt
rm -f export/fez_scopus_citations_cache.txt

cp ${APP_ENV}.fez.config.sql export/fez_config_extras.sql

if [[ "${APP_ENV}" == "production" ]]; then
    now=$( date +'%F %T' )
    for f in export/fez_record_search_key*.txt
    do
      s=".txt"
      r="__shadow.txt"
      shadow=${f/${s}/${r}}
      cp ${f} ${shadow}
      sed -i -- "s/$/,\"${now}\"/" ${shadow}
    done
fi

tar -zcvf fez${APP_ENV}.tar.gz export
rm -Rf export

bucket="uql-fez-${APP_ENV}-cache"
file="fez${APP_ENV}.tar.gz"
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

wget -O - "${FEZ_URL}/api/cron_register_bgp.php?file=bgp_db_load&class=BackgroundProcess_Db_Load&token=${WEBCRON_TOKEN}"
