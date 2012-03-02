<?php

chdir(dirname(__FILE__));



// Setting up configuration.

/* Config include from Libstats
$config = new Zend_Config_Ini(dirname(__FILE__) . '/../../application/configs/application.ini', 'testing');
Zend_Registry::set('config', $config);
*/

// @TODO: 
// Create more graceful method to include config files and/or configuration for test, 
// probably share the same config file with the actual web application
// utilise Zend_Registry like Libstats

// Use standalone tests' config file
//include_once ('../config_test.inc.php'); 

// Use application's config file
// $_SERVER['DOCUMENT_ROOT'] will be nice, but dooh can't use it on command line tests
include_once ('../../public/config.inc.php'); 



// We want all errors 
error_reporting(E_ALL ^ E_NOTICE);

// Set up include paths
set_include_path(
    '/usr/share/pear/'. PATH_SEPARATOR .
    get_include_path()
);


// setup the application logger
// @TODO: uncomment when we are ready to do logging
//$log = new Zend_Log(new Zend_Log_Writer_Null());
//Zend_Registry::set('log', $log);


// Fez: We already have database connection set on config.inc.php
// Libstats: connect to the database
//$params = array(
//    'host'     => $config->database->hostname,
//    'username' => $config->database->username,
//    'password' => $config->database->password,
//    'dbname'   => $config->database->name
//);
//
//$db = Zend_Db::factory($config->database->type, $params);
//$db->getConnection();
//Zend_Registry::set('db', $db);


// @TODO: Do we need to set  up dataabase schema for Fez / eSpace test?
// Setup the db schema
//$sql = file_get_contents('create.sql');
//$db->query($sql);
//// Insert some test data
//$sql = file_get_contents('insert.sql');
//$db->query($sql);