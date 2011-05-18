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
ini_set("display_errors", 1); // LKDB - tmp (was 1)
error_reporting(1);
//error_reporting(E_ALL | E_STRICT); //Use this to show all errs. E_ERROR used by default because otherwise PHP4 style code in this code base causes Strict Standards warnings.
error_reporting(E_ERROR);
set_time_limit(0);
date_default_timezone_set("Australia/Brisbane");

// Set defaults if not specified in config.inc.phpƒ
if (!defined("APP_INC_PATH")) {
  define("APP_INC_PATH", APP_PATH . "include/");
}
if (!defined("APP_PEAR_PATH")) {
  define("APP_PEAR_PATH", APP_INC_PATH . "pear/");
}
if (!defined("APP_SMARTY_PATH")) {
  define("APP_SMARTY_PATH", APP_INC_PATH . "Smarty/");
}
if (!defined("APP_DB_USE_PROFILER")) {
  define("APP_DB_USE_PROFILER", false);
}

set_include_path(APP_PEAR_PATH . PATH_SEPARATOR . APP_INC_PATH);

// set up the Zend loader

require_once(APP_INC_PATH.'Zend/Loader/Autoloader.php');

$autoloader = Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
$autoloader->pushAutoloader(array('ezcBase', 'autoload'), 'ezc');
// or its not! // below is now called in _autoload.php in a Fez specific SimpleSAMLPHP install fork
$autoloader->pushAutoloader('SimpleSAML_autoload', 'SimpleSAML');



include_once(APP_INC_PATH . 'class.log.php');
include_once(APP_INC_PATH . "class.cache.php");
include_once(APP_INC_PATH . "class.configuration.php");
include_once(APP_INC_PATH . "class.language.php");
include_once(APP_INC_PATH . "class.session_db.php");

$params = array(
  'host' => APP_SQL_DBHOST,
  'username' => APP_SQL_DBUSER,
  'password' => APP_SQL_DBPASS,
  'dbname' => APP_SQL_DBNAME,
  'profiler' => array(
      'enabled'     => APP_DB_USE_PROFILER,
      'class'     => 'Zend_Db_Profiler_Firebug'
  )
);

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
  $file_log->setEventItem('visitorIp', $_SERVER['REMOTE_ADDR']);
  $file_writer = new Zend_Log_Writer_Stream($log_file);
  $file_writer->addFilter($level);
  $file_formatter = new Zend_Log_Formatter_Simple(
      '[ %timestamp% ] [ %priorityName% ] [ %visitorIp% ] : %message%' . PHP_EOL
  );
  $file_writer->setFormatter($file_formatter);
  $file_log->addWriter($file_writer);

  // Firebug logging
  $firebug_log = new Zend_Log();
  $firebug_writer = new Zend_Log_Writer_Firebug();
	$firebug_writer->addFilter(Zend_Log::DEBUG);
  $firebug_log->addWriter($firebug_writer);

  $log = new FezLog(array(
    array('log'=>$firebug_log, 'type' => 'firebug'),
    array('log'=>$file_log, 'type' => 'file')
  ), true, true);
} else {
  $null_writer = new Zend_Log_Writer_Null;
  $null_log = new Zend_Log($null_writer);
  $log = new FezLog(array(
  array('log'=>$null_log, 'type' => 'file')
  ), false);
}
 
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

