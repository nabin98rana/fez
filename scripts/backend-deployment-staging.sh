#!/bin/bash
#
# This script is run by Codeship to send an SQS message to deploy the app to staging.
# It expects CI_COMMIT_ID, SQS_URL, NEWRELIC_LICENSE and the AWS credentials to be available in the env.
#

BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )/../" && pwd )
echo Deploying ${CI_COMMIT_ID}..

SQS_MESSAGE=$(<${BASE_DIR}/.docker/staging/aws-task-definition.json)
SQS_MESSAGE="${SQS_MESSAGE//\<COMMIT_HASH\>/${CI_COMMIT_ID}}"
SQS_MESSAGE="${SQS_MESSAGE//\<NEWRELIC_LICENSE\>/${NEWRELIC_LICENSE}}"
SQS_MESSAGE="${SQS_MESSAGE/\<WEBCRON_TOKEN\>/${WEBCRON_TOKEN}}"
SQS_MESSAGE_ATTRIBUTES='{"service": { "StringValue": "fezstaging", "DataType": "String" } }'

aws sqs send-message \
  --queue-url ${SQS_URL} \
  --message-body "${SQS_MESSAGE}" \
  --message-attributes "${SQS_MESSAGE_ATTRIBUTES}"

SQS_MESSAGE=$(<${BASE_DIR}/.docker/staging/aws-task-definition-bgp.json)
SQS_MESSAGE="${SQS_MESSAGE//\<COMMIT_HASH\>/${CI_COMMIT_ID}}"
SQS_MESSAGE="${SQS_MESSAGE//\<NEWRELIC_LICENSE\>/${NEWRELIC_LICENSE}}"
SQS_MESSAGE="${SQS_MESSAGE/\<WEBCRON_TOKEN\>/${WEBCRON_TOKEN}}"
SQS_MESSAGE_ATTRIBUTES='{"service": { "StringValue": "fezstagingbgp", "DataType": "String" } }'

aws sqs send-message \
  --queue-url ${SQS_URL} \
  --message-body "${SQS_MESSAGE}" \
  --message-attributes "${SQS_MESSAGE_ATTRIBUTES}"