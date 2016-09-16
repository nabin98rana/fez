#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Syncs to S3 local fedora data files."
  echo
  echo "USAGE"
  echo "    s3_sync_fedoradata.sh <APP_ENV>"
  echo
  echo "    APP_ENV        = The running environment (e.g. production)"
  echo
  exit
}

if [ "$#" -ne 1 ]; then
  usage
fi

APP_ENV=$1

for i in {01..31}; do
  aws s3 sync 2016/08${i} s3://uql-fez-${APP_ENV}-san/migration/2016/08${i} --sse AES256
done
