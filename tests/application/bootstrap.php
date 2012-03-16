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


require_once(APP_INC_PATH . 'Zend/Loader/Autoloader.php');
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);


// @TODO: Do we need to set up tests database schema for Fez test?
