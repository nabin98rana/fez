#!/bin/bash

BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd ${BASE_DIR}

export BEHAT_PARAMS='{"extensions" : {"Behat\\MinkExtension" : {"selenium2" : { "wd_host" : "http://selenium:4444/wd/hub"}}}}'

../behat/vendor/behat/behat/bin/behat -c behat-development.yml --tags '@seed' --format pretty --colors
