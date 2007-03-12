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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//


include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH.'class.sanity_checks.php');


function parse_mysql_dump($url, $ignoreerrors = false) {
    $file_content = file($url);
    //print_r($file_content);
    $query = "";
    foreach($file_content as $sql_line) {
        $sql_line = replace_table_prefix($sql_line);
        $tsl = trim($sql_line);
        if (($sql_line != "") && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != "#")) {
            $query .= $sql_line;
            if(preg_match("/;\s*$/", $sql_line)) {
                $res = $GLOBALS["db_api"]->dbh->query($query);
                if (PEAR::isError($res)) {
                    Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                    return false;
                }
                $query = "";
            }
        }
    }
    return true;
}


/**
 * Find all the files in a (optional) relative path that match the
 * pattern "upgradeYYYYMMDDxx.sql" and return an array of date strings, in
 * alphabetical order.
 * 
 * @param string $lookin_reldir The relative path to look for upgrade scripts.
 *                              Defaults to "upgrade/local"
 * @return array                A sorted array of sql upgrade filenames.
 */
function getUpdateSqlList($lookin_reldir = 'upgrade/sql_scripts') {
    $upgrades = array();
    $path = APP_PATH . $lookin_reldir;

    if (file_exists($path) and filetype($path) == 'dir') {
        $dirhandle = opendir($path);
        while (false !== ($filename = readdir($dirhandle))) {
            $tokens = strtok($filename, '.');
            if (preg_match("/upgrade[0-9]{10}\.sql/", $filename)) {
                $upgrades[] = substr($filename, 7, 10);
            }
        }
        closedir($dirhandle);
    }
    if (!empty($upgrades)) {
        asort($upgrades);
    }
    return $upgrades;
}

function replace_table_prefix($str)
{
    return str_replace('%TABLE_PREFIX%', APP_TABLE_PREFIX, $str);
}

function get_data_model_version()
{
    $stmt = "select config_value from " . APP_TABLE_PREFIX . "config " .
            "where config_name = 'datamodel_version' " .
            "and config_module = 'core' ";
    $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
    if (PEAR::isError($res)) {
        return 0;
    }
    if (is_array($res)) {
    	return $res['config_value'];
    } else {
    	return 0;
    }
}

function set_data_model_version($dbversion)
{
    $stmt = "update " . APP_TABLE_PREFIX . "config " .
            "set config_value = ". $dbversion. " " .
            "where config_name = 'datamodel_version' " .
            "and config_module = 'core' ";
    $res = $GLOBALS["db_api"]->dbh->query($stmt);
    if (PEAR::isError($res)) {
        return 0;
    } else {
        return 1;
    }
}
 
function upgrade()
{
    clearstatcache();
    // check if config.inc.php in the root directory is writable
    /**
     *  if (!is_writable('../config.inc.php')) {
     *      return "The file 'config.inc.php' in Fez's root directory needs to be writable by the web server user. Please correct this problem and try again.";
     *  }
     */
    $path = APP_PATH .  'upgrade/sql_scripts';
    $sql_upgrades = getUpdateSqlList();
    $success = true;
    $dbversion = get_data_model_version();
    if ($dbversion == 0) {
        if (parse_mysql_dump("upgrade.sql")) {
            $success = $success && true;
            $dbversion = get_data_model_version();
        } else {
            $success = false;
        }
    }
    
    // go through the upgrades and execute any that are greater than the current version
    $sql_upgrade = $dbversion;
    foreach ($sql_upgrades as $sql_upgrade) {
    	if ($sql_upgrade > $dbversion) {
            if (parse_mysql_dump("$path/upgrade{$sql_upgrade}.sql")) {
                $success = $success && true;
            } else {
                $success = false;
            }
        }
    }
    if ($success && set_data_model_version($sql_upgrade)) {
        return array($success, "Upgrade to database version $sql_upgrade succeeded.");
    } else {
        return array($success, 'The upgrade failed - check error_handler.log');
    }
}

$step = Misc::GETorPOST('step');
if (empty($step)) {
	$step = 1;
}

$tpl = new Template_API();
$tpl->setTemplate('upgrade.tpl.html');

switch ($step) {
    case 1:
        // do nothing
    break;
    case 2:
    if (!empty($_POST["upgrade"])) {
        list($res, $message) = upgrade();
        $tpl->assign("result", $message);
        $tpl->assign("result_good", $res);
        if (!$res) {
        	$step = 1;
        }
    }
    break;
    case 3:
        $sanity = SanityChecks::runAllChecks();
        $tpl->assign('sanity_results',$sanity);
    break;
}

$tpl->assign('step', $step);


$tpl->displayTemplate();

?>