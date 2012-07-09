<?php

chdir(dirname(__FILE__));

// Include tests' configuration file

include_once ('../config_test.inc.php');


// @TODO: uncomment config include to perform Unit Tests
// Include application's configuration file, which calls bootstrap for the application.
//include_once ('../../public/config.inc.php');

// We want to throw all errors
error_reporting(E_ALL ^ E_NOTICE);

// Set up include paths
set_include_path(
    '/usr/share/pear/'. PATH_SEPARATOR .
    APP_PATH. PATH_SEPARATOR .
    APP_INC_PATH. PATH_SEPARATOR .
    get_include_path()
);
//echo APP_PEAR_PATH; echo "hmm";
    //    APP_PATH. PATH_SEPARATOR . 'tests' . PATH_SEPARATOR . 'application' . PATH_SEPARATOR . 'Unit' . PATH_SEPARATOR .

//require_once(APP_INC_PATH . 'Zend/Loader/Autoloader.php');
//Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);


// @TODO: Do we need to set up tests database schema for Fez test?


define('APP_UNIT_TESTING', true);
// Where is config.inc?

//require_once('../../configs/config.inc.php');
//require_once(APP_PATH.'configs/config.inc.php');
require_once(APP_PATH.'/../configs/config.inc.php');
//require_once('../../public/init.php');
//define('APP_TEST_PATH', APP_PATH.'tests/');
define('APP_TEST_PATH', dirname(dirname(__FILE__)).'/');
//echo "HERE".APP_TEST_PATH."\n\n";
//require_once APP_PEAR_PATH.'PHPUnit/Framework.php';
require_once APP_TEST_PATH.'application/Unit/test_common.php';
