<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//

ini_set('allow_url_fopen', 0);
ini_set("display_errors", 0); // LKDB - tmp (was 1)
//error_reporting(1);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
set_time_limit(0);
date_default_timezone_set("Australia/Brisbane");

$firebug_ips = array(); //$firebug_ips = array('130.102.44.20', '130.102.44.12', '130.102.44.50', '130.102.44.1', '130.102.44.21', '130.102.44.54');

// Set defaults if not specified in config.inc.php
if (!defined("APP_INC_PATH")) {
    define("APP_INC_PATH", APP_PATH . "include/");
}
if (!defined("APP_PEAR_PATH")) {
  define("APP_PEAR_PATH", APP_INC_PATH . "pear/");
}
if (!defined("APP_SMARTY_PATH")) {
    define("APP_SMARTY_PATH", APP_INC_PATH . "Smarty/");
}
if(in_array($_SERVER['REMOTE_ADDR'], $firebug_ips)) {
	define("APP_DB_USE_PROFILER", true);
}
else if (!defined("APP_DB_USE_PROFILER")) {
    define("APP_DB_USE_PROFILER", false);
}

//set_include_path(APP_PEAR_PATH . PATH_SEPARATOR . APP_INC_PATH);

set_include_path(APP_PEAR_PATH . PATH_SEPARATOR . APP_INC_PATH.
    get_include_path());

// set up the Zend loader

require_once(APP_INC_PATH.'Zend/Loader/Autoloader.php');

//$autoloader = Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Fez');
//spl_autoload_register(array('Zend_Loader_Autoloader', 'autoload'));
//$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->pushAutoloader(array('ezcBase', 'autoload'), 'ezc');
// or its not! // below is now called in _autoload.php in a Fez specific SimpleSAMLPHP install fork
$autoloader->pushAutoloader('SimpleSAML_autoload', 'SimpleSAML');



include_once(APP_INC_PATH . 'class.log.php');
include_once(APP_INC_PATH . "class.cache.php");
include_once(APP_INC_PATH . "class.configuration.php");
include_once(APP_INC_PATH . "class.language.php");

$params = array(
  'host' => APP_SQL_DBHOST,
  'username' => APP_SQL_DBUSER,
  'password' => APP_SQL_DBPASS,
  'dbname'   => APP_SQL_DBNAME,
  'charset'  => 'utf8',
  'profiler' => array(
    'enabled' => APP_DB_USE_PROFILER,
    'class'   => 'Zend_Db_Profiler_Firebug'
  )
);

if ($_SERVER['APPLICATION_ENV'] === 'staging') {
  $params['driver_options'] = array(
    PDO::MYSQL_ATTR_LOCAL_INFILE => '1'
  );
}

if (defined("APP_SQL_DBPORT")) {
	$params['port'] = APP_SQL_DBPORT;
}

try {
    $db = Zend_Db::factory(APP_SQL_DBTYPE, $params);
    $db->getConnection();
    Zend_Db_Table_Abstract::setDefaultAdapter($db);
    Zend_Registry::set('db', $db);
}
catch (Exception $ex) {
    $error_type = "db";
    include_once(APP_PATH . "offline.php");
    exit;
}
Configuration::registerConf();

// if slave db config.inc.php consts are setup, use them for statistics reading to lessing the burden
if (defined("APP_SQL_SLAVE_DBHOST")) {
  $slave_params = array(
    'host' => APP_SQL_SLAVE_DBHOST,
    'username' => APP_SQL_SLAVE_DBUSER,
    'password' => APP_SQL_SLAVE_DBPASS,
    'dbname' => APP_SQL_SLAVE_DBNAME,
    'charset' => 'utf8',
    'profiler' => array(
      'enabled'     => APP_DB_USE_PROFILER,
      'class'     => 'Zend_Db_Profiler_Firebug'
    )
  );

  try {
    $db_slave = Zend_Db::factory(APP_SQL_DBTYPE, $slave_params);
    $db_slave->getConnection();
    Zend_Registry::set('db_slave', $db_slave);
  }
  catch (Exception $ex) {
    $error_type = "db_slave";
  }
}

// if cache db config.inc.php consts are setup, use them for fez_fulltext_cache table read/write instead of the main db, to lessen load/spread storage
if (defined("APP_SQL_CACHE_DBHOST")) {
  $cache_params = array(
    'host' => APP_SQL_CACHE_DBHOST,
    'username' => APP_SQL_CACHE_DBUSER,
    'password' => APP_SQL_CACHE_DBPASS,
    'dbname' => APP_SQL_CACHE_DBNAME,
    'charset' => 'utf8',
    'profiler' => array(
      'enabled'     => APP_DB_USE_PROFILER,
      'class'     => 'Zend_Db_Profiler_Firebug'
    )
  );

  try {
    $db_cache = Zend_Db::factory(APP_SQL_CACHE_DBTYPE, $cache_params);
    $db_cache->getConnection();
    Zend_Registry::set('db_cache', $db_cache);
  }
  catch (Exception $ex) {
    $error_type = "db_cache";
  }
}

if (APP_LOGGING_ENABLED == "true") {
	
	$log_file = APP_LOG_LOCATION;
  if (preg_match('/%([^%]*)%/i', APP_LOG_LOCATION, $matches)) {
    if (count($matches) == 2) {
			$to_replace = $matches[0];
			$format = $matches[1];
			$date = @date($format);
      if ($date) {
				$log_file = str_replace($to_replace, $date, APP_LOG_LOCATION);
			}
		}
	}
	
	$level = intval(APP_LOG_LEVEL);
  if ( (!$level) || $level > 7) {
		$level = 0; // Zend_log::EMERG
	}
	$file_log = new Zend_Log();
	$file_log->setEventItem('timestamp', date('m-d-Y H:i:s', time()));
  if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
	$file_log->setEventItem('visitorIp', $_SERVER['REMOTE_ADDR']);
  }
	$file_writer = new Zend_Log_Writer_Stream($log_file);	
	$file_writer->addFilter($level);
  $file_formatter = new Zend_Log_Formatter_Simple(
      '[ %timestamp% ] [ %priorityName% ] [ %visitorIp% ] : %message%' . PHP_EOL
  );
	$file_writer->setFormatter($file_formatter);
	$file_log->addWriter($file_writer);
	
	if(in_array($_SERVER['REMOTE_ADDR'], $firebug_ips)) {
		// Firebug logging
		$firebug_log = new Zend_Log();
		$firebug_writer = new Zend_Log_Writer_Firebug();
		$firebug_writer->addFilter(Zend_Log::DEBUG);
		$firebug_log->addWriter($firebug_writer);
		
		$log = new FezLog(array(
				array('log'=>$firebug_log, 'type' => 'firebug'),
				//array('log'=>$file_log, 'type' => 'file')
				), true, true);
	}
	else {	
		$log = new FezLog(array(
	                    //array('log'=>$firebug_log, 'type' => 'firebug'),
	                    array('log'=>$file_log, 'type' => 'file')
	                    ), false);
	}
}
else {
	
	$null_writer = new Zend_Log_Writer_Null;
	$null_log = new Zend_Log($null_writer);
	$log = new FezLog(array(
                    array('log'=>$null_log, 'type' => 'file')
                    ), false);
}


if (defined('AWS_ENABLED') && AWS_ENABLED == 'true') {
  include_once(APP_INC_PATH . "class.aws.php");
  $aws = new AWS();
  Zend_Registry::set('aws', $aws);
}

//@define('APP_HOSTNAME','fezdemo.library.uq.edu.au');
//@define('APP_RELATIVE_URL','/uqckorte/staging/');
@define('EPRINTS_DB_TYPE', 'pdo_mysql');
@define('APP_BASIC_AUTH_IP', "202.158.222.208; 202.158.222.209; 202.158.222.240");
@define('APP_LOGGING_DESTINATION', APP_PATH . "logs/fez-" . date("Ymd") . ".log");

$sess = new Fez_Session_Manager();
Zend_Session::setOptions(
  array(
    'gc_probability' => 1,
    'gc_divisor' => 5000
  )
);
Zend_Session::setSaveHandler($sess);
register_shutdown_function('session_write_close');

$fedoraConnectivity = 1;
$fparams = array(
            'host' => FEDORA_DB_HOST,
            'username' => FEDORA_DB_USERNAME,
            'password' => FEDORA_DB_PASSWD,
            'dbname' => FEDORA_DB_DATABASE_NAME,
            'port' => FEDORA_DB_PORT,
            'profiler' => array(
                'enabled'     => APP_DB_USE_PROFILER,
                'class'     => 'Zend_Db_Profiler_Firebug'
)
);

if(APP_FEDORA_BYPASS != "ON")
{
	try {
    $fdb = Zend_Db::factory(FEDORA_DB_TYPE, $fparams);
    $fdb->getConnection();
    Zend_Registry::set('fedora_db', $fdb);
	}
	catch (Exception $ex) {
	  // We don't want to bail out if Fedora details haven't been entered yet.
	  // This is perfectly legitimate for a brand new installation! However,
	  // we need to at least make the user aware of the situation.
    $fedoraConnectivity = 0;
	}
}

if (isset($_GET)) {
    $HTTP_POST_VARS = $_POST;
    $HTTP_GET_VARS = $_GET;
    $HTTP_SERVER_VARS = $_SERVER;
    $HTTP_ENV_VARS = $_ENV;
    $HTTP_POST_FILES = $_FILES;
    // Seems like PHP 4.1.0 didn't implement the $_SESSION auto-global ...
    if (isset($_SESSION)) {
        $HTTP_SESSION_VARS = $_SESSION;
    }
    //$HTTP_SESSION_VARS = $_SESSION;
    $HTTP_COOKIE_VARS = $_COOKIE;
}

// Set this string to true if you have given php.ini at least 256M to use.
// It will improve performance by storing some Fedora and SQL queries in
// PHP memory rather than fetch them twice or more during a single page load.
// This gets hardset to false globally for indexing and reindexing in
// background processes as this won't scale over 500M with over 10,000 objects
// otherwise (you will get PHP fatal memory error for example).
$GLOBALS['app_cache'] = true;

try {
  $frontendOptions = array('lifetime' => NULL, // cache lifetime is forever
	   'automatic_serialization' => true
	);
	$backendOptions = array(
		'cache_dir' => APP_FILECACHE_DIR // Directory where to put the cache files
	);
	// getting a Zend_Cache_Core object
  $cache = Zend_Cache::factory(
      'Core',
								 'File',
								 $frontendOptions,
      $backendOptions
  );
	Zend_Registry::set('cache', $cache);
}
catch (Exception $ex) {
    // No app caching
}

// Fix magic_quote_gpc'ed values
$_GET =& Misc::dispelMagicQuotes($_GET);
$_POST =& Misc::dispelMagicQuotes($_POST);

// Handle the language preferences
Language::setPreference();

define('HTTP_METHOD', $_SERVER['REQUEST_METHOD']);

//------------------------------------------------------------
// APP_API
//
// * basic_auth webservice over https
// * allows us to do a number of the same things done via
//   browser

$app_api = false;
$app_api_json = false;
// Basic auth credentials:
$app_api_username = NULL;
$app_api_password = NULL;
$format = NULL;

// What triggers the API?
// 1) Look at the content type of the request.
// 2) If not that, look if the $format parameter is passed explicitly.
//    eg when someone tests GET in the browser...

$ctype = $_SERVER['CONTENT_TYPE'];
$check1 = ( ($ctype == 'application/xml') || ($ctype == 'application/json') );
if (!$check1) {
    $check2 = (Misc::sanity_check($_REQUEST['format'], 'string') !== false);
    $format = $_REQUEST['format'];
} else {
    switch ($ctype)
    {
    case 'application/xml':
        $format = 'xml';
        break;
    case 'application/json':
        $format = 'json';
        break;
    default:
        throw new Exception('Internal error.');
    }
}

if ($check1 || $check2) {
    if ($format == 'xml' || $format == 'json') {
        $app_api_username = $_SERVER['PHP_AUTH_USER'];
        $app_api_password = $_SERVER['PHP_AUTH_PW'];
        if ($format == 'json') {
            $header = "Content-Type: application/json";
            $app_api = 'json';
            $app_api_json = 'json';
        } else {
            $header = "Content-Type: application/xml";
            $app_api = 'xml';
        }
        header($header);
    }
}

define('APP_API', $app_api);
// Json is tricker to do because of the way fez is written.
define('APP_API_JSON',$app_api_json);
// Note: these are the crendentials we received but they are not
// necessarily valid:

define('APP_API_USERNAME', $app_api_username);
define('APP_API_PASSWORD', $app_api_password);

if (APP_API) {
    // Don't display things like warnings and errors as these will
    // disrupt the format (xml or json).
    // http://stackoverflow.com/questions/9729000/setting-display-errors-0-and-log-errors-1-without-php-ini
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
}
