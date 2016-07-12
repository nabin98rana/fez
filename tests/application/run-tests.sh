#!/bin/bash

BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd ${BASE_DIR}

rm -f /etc/php.d/15-xdebug.ini
export BEHAT_PARAMS='{"extensions" : {"Behat\\MinkExtension" : {"selenium2" : { "wd_host" : "http://selenium:4444/wd/hub"}}}}'

#phpunit --no-configuration --log-junit "${WORKSPACE}/phpunit_results/phpunit_results.xml" --include-path ".:${WORKSPACE}/public/" ${WORKSPACE}/tests/application/Unit/ResearcherIdTests.php

#${BASE_DIR}/../behat/bin/behat --retry-scenario 3 --ansi --tags '~@broken' --format=pretty,html,junit --out=,../../build/tests/formattedresults.html,../../build/tests/

#../behat/vendor/behat/behat/bin/behat --tags '@jet&&~@cloned' --ansi --format=pretty,html,junit --out=,logs/formattedresults.html,logs/

../behat/vendor/behat/behat/bin/behat --tags '@jet&&~@cloned' --format pretty --colors

#Use the below example of how to run tests in the development environment - eg create test data
#../behat/vendor/behat/behat/bin/behat --tags '@jet&&~@cloned' --format pretty --colors features/02-CreateTestData.feature
#../behat/vendor/behat/behat/bin/behat --tags '@jet&&~@cloned' --format pretty --colors features/ThesisUpload.feature

#../behat/vendor/behat/behat/bin/behat --retry-scenario 3 --ansi --tags '@jet' --format=pretty,html,junit --out=,../../build/tests/formattedresults.html,../../build/tests/
