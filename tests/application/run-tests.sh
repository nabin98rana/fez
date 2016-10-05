#!/bin/bash

BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd ${BASE_DIR}

rm -f /etc/php.d/15-xdebug.ini
export BEHAT_PARAMS='{"extensions" : {"Behat\\MinkExtension" : {"selenium2" : { "wd_host" : "http://selenium:4444/wd/hub"}}}}'

BEHAT_TAGS="@jet&&~@cloned&&${1}"
# Smoke tests on production
if [[ "${CI_BRANCH}" = "production" ]]; then
  BEHAT_TAGS="@smoke&&${BEHAT_TAGS}"
fi
../behat/vendor/behat/behat/bin/behat --tags "${BEHAT_TAGS}" --format pretty --colors --stop-on-failure
