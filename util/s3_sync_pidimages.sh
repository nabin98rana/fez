#!/bin/bash

function usage {
  echo
  echo "DESCRIPTION"
  echo "    Syncs to S3 local pidimages files."
  echo
  echo "USAGE"
  echo "    s3_sync_pidimages.sh <APP_ENV>"
  echo
  echo "    APP_ENV        = The running environment (e.g. production)"
  echo
  exit
}

if [ "$#" -ne 1 ]; then
  usage
fi

APP_ENV=$1

aws s3 sync /espace_san/pidimages s3://uql-fez-${APP_ENV}-cache/pidimages --profile fez${APP_ENV}
