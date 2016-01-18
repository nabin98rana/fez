#!/bin/bash

cd /var/app/current/

echo Starting test run..

# TODO: replace with MySQL check
sleep 30

echo Running tests..

# tests/application/run-tests.sh
docker -v
docker-compose -v
