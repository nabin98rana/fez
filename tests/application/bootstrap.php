<?php

chdir(dirname(__FILE__));

// Include tests' configuration file
include_once ('../config_test.inc.php');

// Include application's configuration file, which calls bootstrap for the application.
include_once ('../../public/config.inc.php'); 

// We want to throw all errors 
error_reporting(E_ALL ^ E_NOTICE);

// Set up include paths
set_include_path(
    '/usr/share/pear/'. PATH_SEPARATOR .
    get_include_path()
);


// @TODO: Do we need to set up tests database schema for Fez test?
