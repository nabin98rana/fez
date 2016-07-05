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

/**
 * Type of database connection. With Zend, a PDO connector is recommended, and
 * "pdo_mysql" is the default. Other options are "mysqli" or "pdo_pgsql"
 */
define("APP_SQL_DBTYPE", "pdo_mysql");

/**
 * Database host. Defaults to localhost.
 */
define("APP_SQL_DBHOST", "fezdb");

/**
 * Database name for the Fez database.
 */
define("APP_SQL_DBNAME", "fez");

/**
 * Database user.
 */
define("APP_SQL_DBUSER", "fez");

/**
 * Database password.
 */
define("APP_SQL_DBPASS", "fez");

/**
 * Table prefix - defaults to fez_
 */
define("APP_TABLE_PREFIX", "fez_");

/**
 * Path on the filesystem to the Fez code, which is the directory containing
 * this config file.
 */
define("APP_PATH", '/var/app/current/public/');



////////////////////////////////////////////////////////////////////////////////
//
// The following settings are optional and will be set to defaults.
//
////////////////////////////////////////////////////////////////////////////////

/**
 * To turn on the database profiler for debugging information, set this to true.
 */
# define("APP_DB_USE_PROFILER", false); // default: false

/**
 * Fez include path. If for some reason you have installed these files somewhere
 * other than the default, you can set the include path here.
 */
# define("APP_INC_PATH", APP_PATH . "include/");

/**
 * Fez PEAR include path. If you have PEAR components installed elsewhere, you
 * can set the path here.
 */
# define("APP_PEAR_PATH", APP_INC_PATH . "pear/");

/**
 * Fez Smarty path. If you have Smarty installed elsewhere, you can set the path
 * here.
 */
# define("APP_SMARTY_PATH", APP_INC_PATH . "Smarty/");

/**
 * Filesystem location for the Fez log files.
 */
# @define('APP_LOGGING_DESTINATION', APP_PATH . "logs/fez-" . date("Ymd") . ".log");

/**
 * Debug level for the Fez log files.
 */
# @define('APP_LOGGING_LEVEL', PEAR_LOG_DEBUG);



////////////////////////////////////////////////////////////////////////////////
//
// DO NOT REMOVE THE FOLLOWING LINES!
//
////////////////////////////////////////////////////////////////////////////////

include dirname(__FILE__) . "/init.php";
