<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | eSpace - Digital Repository                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.config.inc.php 1.8 04/01/19 15:19:26-00:00 jpradomaia $
//
//ini_set('allow_url_fopen', 0);
ini_set("display_errors", 1);
error_reporting(1);
//error_reporting(E_WARNING);
set_time_limit(0);
// only needed for older PHP versions
/*if (!function_exists('is_a')) {
    function is_a($object, $class_name)
    {
        $class_name = strtolower($class_name);
        if (get_class($object) == $class_name) {
            return TRUE;
        } else {
            return is_subclass_of($object, $class_name);
        }
    }
}
*/
// definitions of path related variables
@define("APP_PATH", '/usr/local/apache/htdocs/dev-espace/');
@define("APP_INC_PATH", APP_PATH . "include/");
@define("APP_PEAR_PATH", APP_INC_PATH . "pear/");
@define("APP_TPL_PATH", APP_PATH . "templates/");
@define("APP_SMARTY_PATH", APP_INC_PATH . "Smarty/");
@define("APP_JPGRAPH_PATH", APP_INC_PATH . "jpgraph/");
if (stristr(PHP_OS, 'darwin')) {
    ini_set("include_path", ".:" . APP_PEAR_PATH);
} elseif (stristr(PHP_OS, 'win')) {
    ini_set("include_path", ".;" . APP_PEAR_PATH);
} else {
    ini_set("include_path", ".:" . APP_PEAR_PATH);
}

@define("APP_SETUP_PATH", APP_PATH);
@define("APP_SETUP_FILE", APP_SETUP_PATH . "setup.conf.php");

// FEDORA VARIABLES

//base fedora server domain 
// example:  fedora.nsdlib.org:8080/fedora
@define("APP_BASE_FEDORA_DOMAIN", "130.102.44.8:8080/fedora");

// Setup reusable Fedora API variables
@define("APP_FEDORA_USERNAME", "fedoraAdmin");
@define("APP_FEDORA_PWD", "fedoraAdmin");

// Should be ok in routine installations
@define("APP_FEDORA_ACCESS_API", "http://".APP_BASE_FEDORA_DOMAIN."/access/soap");
@define("APP_FEDORA_MANAGEMENT_API", "http://".APP_BASE_FEDORA_DOMAIN."/management/soap");

//fedora server search url
@define("APP_FEDORA_SEARCH_URL", "http://".APP_BASE_FEDORA_DOMAIN."/search");

//upload url
@define("APP_FEDORA_UPLOAD_URL", "http://".APP_FEDORA_USERNAME.":".APP_FEDORA_PWD."@".APP_BASE_FEDORA_DOMAIN."/management/upload");

// XSL directory
@define("APP_XSL_PATH", APP_PATH . "xsl/");

// definitions of SQL variables
@define("APP_SQL_DBTYPE", "mysql");
@define("APP_SQL_DBHOST", "db2.library.uq.edu.au");
@define("APP_SQL_DBNAME", "dev_espace");
@define("APP_SQL_DBUSER", "espace");
@define("APP_SQL_DBPASS", "3sp@c3r0x");

@define("APP_DEFAULT_DB", APP_SQL_DBNAME);
@define("APP_TABLE_PREFIX", "espace_");

@define("APP_ERROR_LOG", APP_PATH . "error_handler.log");

@define("APP_NAME", "eSpace");
@define("APP_SHORT_NAME", APP_NAME); // used in the subject of notification emails
@define("APP_URL", "http://www.library.uq.edu.au/escholarship/");
@define("APP_HOSTNAME", "dev-espace.library.uq.edu.au");
@define("APP_SITE_NAME", APP_NAME);
@define("APP_RELATIVE_URL", "/");
@define("APP_BASE_URL", "https://" . APP_HOSTNAME . APP_RELATIVE_URL);
@define("APP_SESSION", "espace");
@define("APP_SESSION_EXPIRE", time() + (60 * 60 * 8));
@define("APP_COLLECTION_COOKIE", "espace");
@define("APP_COLLECTION_COOKIE_EXPIRE", time() + (60 * 60 * 24));

@define("APP_VERSION", "0.0.1");

@define("APP_LIST_COOKIE", 'espace_list');
@define("APP_LIST_COOKIE_EXPIRE", time() + (60 * 60 * 24 * 30 * 48));
@define("APP_EMAIL_LIST_COOKIE", 'espace_email_list');
@define("APP_EMAIL_LIST_COOKIE_EXPIRE", time() + (60 * 60 * 24 * 30 * 48));
@define("APP_DEFAULT_PAGER_SIZE", 5);
@define("APP_DEFAULT_REFRESH_RATE", 5); // in minutes

// define colors used by eventum
@define("APP_CELL_COLOR", "#255282");
@define("APP_LIGHT_COLOR", "#DDDDDD");
@define("APP_MIDDLE_COLOR", "#CACACA");
@define("APP_DARK_COLOR", "#CACACA");
@define("APP_CYCLE_COLORS", "#DDDDDD,#CACACA");
@define("APP_INTERNAL_COLOR", APP_CELL_COLOR);

// define the user_id of system user
@define("APP_SYSTEM_USER_ID", 1);

@define("APP_BENCHMARK", true);
if (APP_BENCHMARK) {
    // always benchmark the scripts
    include_once("Benchmark/Timer.php");
    $bench = new Benchmark_Timer;
    $bench->start();
}

include_once(APP_INC_PATH . "class.misc.php");

if (isset($_GET)) {
    $HTTP_POST_VARS = $_POST;
    $HTTP_GET_VARS = $_GET;
    $HTTP_SERVER_VARS = $_SERVER;
    $HTTP_ENV_VARS = $_ENV;
    $HTTP_POST_FILES = $_FILES;
    // seems like PHP 4.1.0 didn't implement the $_SESSION auto-global...
    if (isset($_SESSION)) {
        $HTTP_SESSION_VARS = $_SESSION;
    }
    $HTTP_COOKIE_VARS = $_COOKIE;
}

// fix magic_quote_gpc'ed values (i wish i knew who is the person behind this)
$HTTP_GET_VARS =& Misc::dispelMagicQuotes($HTTP_GET_VARS);
$HTTP_POST_VARS =& Misc::dispelMagicQuotes($HTTP_POST_VARS);
$_GET =& Misc::dispelMagicQuotes($_GET);
$_POST =& Misc::dispelMagicQuotes($_POST);

// handle the language preferences now
@include_once(APP_INC_PATH . "class.language.php");
Language::setPreference();
?>
