<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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
//

set_time_limit(3600);

@DEFINE("APP_BENCHMARK", false);
@DEFINE("APP_CURRENT_LANG", "en");

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.sanity_checks.php");



function parse_mysql_dump($url, $ignoreerrors = false) {
    $log = FezLog::get();
    $db = DB_API::get();

    $file_content = file($url);
    //print_r($file_content);
    $query = "";
    foreach($file_content as $sql_line) {
        $sql_line = replace_table_prefix($sql_line);
        $tsl = trim($sql_line);
        if (($sql_line != "") && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != "#")) {
            $query .= $sql_line;
            if(preg_match("/;\s*$/", $sql_line)) {
                try {
                    $res = $db->query($query, array(), Zend_Db::FETCH_ASSOC);
                }
                catch(Exception $ex) {
                    $log->notice(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
                    $log->err($ex);
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
function getUpdateSqlList($lookin_reldir = 'upgrade/sql_scripts', $dbversion) {
    $upgrades = array();
    $path = APP_PATH . $lookin_reldir;
    
    if (file_exists($path) and filetype($path) == 'dir') {
        $dirhandle = opendir($path);
        while (false !== ($filename = readdir($dirhandle))) {
            $tokens = strtok($filename, '.');
            if (preg_match("/upgrade[0-9]{10}\.sql/", $filename)) {
                
                $sqlFileNum = substr($filename, 7, 10);
                if($sqlFileNum > $dbversion )
                {
                    $upgrades[] = $sqlFileNum;
                }
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
    $str = str_replace('%PID_NAMESPACE%', APP_PID_NAMESPACE, $str);
    return str_replace('%TABLE_PREFIX%', APP_TABLE_PREFIX, $str);
}

function get_data_model_version()
{
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "select config_value from " . APP_TABLE_PREFIX . "config " .
            "where config_name = 'datamodel_version' " .
            "and config_module = 'core' ";
    try {
        $res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
        catch(Exception $ex) {
        $log->notice(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
        $log->err($ex);
    }

    if (is_array($res)) {
    	return $res['config_value'];
    }
    return 0;
}

function set_data_model_version($dbversion)
{
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "update " . APP_TABLE_PREFIX . "config " .
            "set config_value = ". $dbversion. " " .
            "where config_name = 'datamodel_version' " .
            "and config_module = 'core' ";
    try {
        $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
        $log->notice(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
        $log->err($ex);
        return 0;
    }

    return 1;
}

function saveExistingConfigToDB()
{
    $defaultData['webserver_log_statistics']            = WEBSERVER_LOG_STATISTICS;
    $defaultData['webserver_log_dir']                   = WEBSERVER_LOG_DIR;
    $defaultData['webserver_log_file']                  = WEBSERVER_LOG_FILE;
    $defaultData['app_geoip_path']                      = APP_GEOIP_PATH;
    $defaultData['shib_switch']                         = SHIB_SWITCH;
    $defaultData['shib_direct_login']                   = SHIB_DIRECT_LOGIN;
    $defaultData['shib_federation_name']                = SHIB_FEDERATION_NAME;
    $defaultData['shib_federation']                     = SHIB_FEDERATION;
    $defaultData['shib_home_sp']                        = SHIB_HOME_SP;
    $defaultData['shib_home_idp']                       = SHIB_HOME_IDP;
    $defaultData['shib_wayf_metadata_location']         = SHIB_WAYF_METADATA_LOCATION;
    $defaultData['app_fedora_version']                  = APP_FEDORA_VERSION;
    $defaultData['app_fedora_username']                 = APP_FEDORA_USERNAME;
    $defaultData['app_fedora_pwd']                      = APP_FEDORA_PWD;
    $defaultData['fedora_db_host']                      = FEDORA_DB_HOST;
    $defaultData['fedora_db_type']                      = FEDORA_DB_TYPE;
    $defaultData['fedora_db_database_name']             = FEDORA_DB_DATABASE_NAME;
    $defaultData['fedora_db_username']                  = FEDORA_DB_USERNAME;
    $defaultData['fedora_db_passwd']                    = FEDORA_DB_PASSWD;
    $defaultData['app_shaded_bar']                      = APP_SHADED_BAR;
    $defaultData['app_cell_color']                      = APP_CELL_COLOR;
    $defaultData['app_value_color']                     = APP_VALUE_COLOR;
    $defaultData['app_light_color']                     = APP_LIGHT_COLOR;
    $defaultData['app_selected_color']                  = APP_SELECTED_COLOR;
    $defaultData['app_middle_color']                    = APP_MIDDLE_COLOR;
    $defaultData['app_dark_color']                      = APP_DARK_COLOR;
    $defaultData['app_heading_color']                   = APP_HEADING_COLOR;
    $defaultData['app_internal_color']                  = APP_INTERNAL_COLOR;
    $defaultData['app_fedora_setup']                    = APP_FEDORA_SETUP;
    $defaultData['app_fedora_location']                 = APP_FEDORA_LOCATION;
    $defaultData['app_fedora_ssl_location']             = APP_FEDORA_SSL_LOCATION;
    $defaultData['ldap_switch']                         = LDAP_SWITCH;
    $defaultData['ldap_organisation']                   = LDAP_ORGANISATION;
    $defaultData['ldap_root_dn']                        = LDAP_ROOT_DN;
    $defaultData['ldap_prefix']                         = LDAP_PREFIX;
    $defaultData['ldap_server']                         = LDAP_SERVER;
    $defaultData['ldap_port']                           = LDAP_PORT;
    $defaultData['eprints_oai']                         = EPRINTS_OAI;
    $defaultData['eprints_username']                    = EPRINTS_USERNAME;
    $defaultData['eprints_passwd']                      = EPRINTS_PASSWD;
    $defaultData['eprints_subject_authority']           = EPRINTS_SUBJECT_AUTHORITY;
    $defaultData['eprints_db_host']                     = EPRINTS_DB_HOST;
    $defaultData['eprints_db_type']                     = EPRINTS_DB_TYPE;
    $defaultData['eprints_db_database_name']            = EPRINTS_DB_DATABASE_NAME;
    $defaultData['eprints_db_username']                 = EPRINTS_DB_USERNAME;
    $defaultData['eprints_db_passwd']                   = EPRINTS_DB_PASSWD;
    $defaultData['eprints_import_users']                = EPRINTS_IMPORT_USERS;
    $defaultData['self_registration']                   = SELF_REGISTRATION;
    $defaultData['app_hostname']                        = APP_HOSTNAME;
    $defaultData['app_name']                            = APP_NAME;
    $defaultData['app_admin_email']                     = APP_ADMIN_EMAIL;
    $defaultData['app_org_name']                        = APP_ORG_NAME;
    $defaultData['app_short_org_name']                  = APP_SHORT_ORG_NAME;
    $defaultData['app_pid_namespace']                   = APP_PID_NAMESPACE;
    $defaultData['app_url']                             = APP_URL;
    $defaultData['app_relative_url']                    = APP_RELATIVE_URL;
    $defaultData['app_image_preview_max_width']         = APP_IMAGE_PREVIEW_MAX_WIDTH;
    $defaultData['app_image_preview_max_height']        = APP_IMAGE_PREVIEW_MAX_HEIGHT;
    $defaultData['app_https']                           = APP_HTTPS;
    $defaultData['app_debug_level']                     = APP_DEBUG_LEVEL;
    $defaultData['app_display_error_level']             = APP_DISPLAY_ERROR_LEVEL;
    $defaultData['app_display_errors_user']             = APP_DISPLAY_ERRORS_USER;
    $defaultData['app_error_log']                       = APP_ERROR_LOG;
    $defaultData['app_system_user_id']                  = APP_SYSTEM_USER_ID;
    $defaultData['app_email_system_from_address']       = APP_EMAIL_SYSTEM_FROM_ADDRESS;
    $defaultData['app_email_smtp']                      = APP_EMAIL_SMTP;
    $defaultData['app_watermark']                       = APP_WATERMARK;
    $defaultData['app_default_user_timezone']           = APP_DEFAULT_USER_TIMEZONE;
    $defaultData['app_san_import_dir']                  = APP_SAN_IMPORT_DIR;
    $defaultData['app_default_refresh_rate']            = APP_DEFAULT_REFRESH_RATE;
    $defaultData['app_temp_dir']                        = APP_TEMP_DIR;
    $defaultData['app_convert_cmd']                     = APP_CONVERT_CMD;
    $defaultData['app_composite_cmd']                   = APP_COMPOSITE_CMD;
    $defaultData['app_identify_cmd']                    = APP_IDENTIFY_CMD;
    $defaultData['app_jhove_dir']                       = APP_JHOVE_DIR;
    $defaultData['app_dot_exec']                        = APP_DOT_EXEC;
    $defaultData['app_php_exec']                        = APP_PHP_EXEC;
    $defaultData['app_pdftotext_exec']                  = APP_PDFTOTEXT_EXEC;
    $defaultData['app_sql_cache']                       = APP_SQL_CACHE;
    $defaultData['app_default_pager_size']              = APP_DEFAULT_PAGER_SIZE;
    $defaultData['app_cookie']                          = APP_COOKIE;
    $defaultData['app_https_curl_check_cert']           = APP_HTTPS_CURL_CHECK_CERT;
    $defaultData['batch_import_type']                   = BATCH_IMPORT_TYPE;
    $defaultData['app_link_prefix']                     = APP_LINK_PREFIX;

    // Hard-wired / modified variables
    $defaultData['app_version']                         = "2.1 RC3";

    $cycleColors = explode(",", APP_CYCLE_COLORS);
    $defaultData['app_cycle_color_one']                 = $cycleColors[0];
    $defaultData['app_cycle_color_two']                 = $cycleColors[1];

    $defaultData['app_image_web_max_width']             = 400;
    $defaultData['app_image_web_max_height']            = 300;
    $defaultData['app_thumbnail_width']                 = 40;
    $defaultData['app_thumbnail_height']                = 30;

    if (!defined('FEDORA_DB_PORT')) {
        $defaultData['fedora_db_port']                  = "3306";
    } else {
        $defaultData['fedora_db_port']                  = FEDORA_DB_PORT;
    }

    if (APP_REPORT_ERROR_FILE == false) {
        $defaultData['app_report_error_file']               = "false";
    } else {
        $defaultData['app_report_error_file']               = "true";
    }

    if (SHIB_SURVEY == false || SHIB_SURVEY == "OFF") {
        $defaultData['shib_survey']                         = "OFF";
    } else {
        $defaultData['shib_survey']                         = "ON";
    }

    if (APP_DISABLE_PASSWORD_CHECKING == false) {
        $defaultData['app_disable_password_checking']       = "false";
    } else {
        $defaultData['app_disable_password_checking']       = "true";
    }

    // Write everything we have to the config table
    foreach ($defaultData as $key => $value) {
        //echo $key . " ........................... " . $value . "<br />";       // LKDB
        $res = $GLOBALS["db_api"]->dbh->query("UPDATE " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "config SET config_value = '" . Misc::escapeString($value) . "' WHERE config_name = '" . $key . "' AND config_module = 'core'");
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return 0;
        }
    }

    return 1;
}
 
function upgrade($skip)
{
    clearstatcache();
    // check if config.inc.php in the root directory is writable
    /**
     *  if (!is_writable('../config.inc.php')) {
     *      return "The file 'config.inc.php' in Fez's root directory needs to be writable by the web server user. Please correct this problem and try again.";
     *  }
     */
    $path = APP_PATH .  'upgrade/sql_scripts';
    $dbversion = get_data_model_version();
    $sql_upgrades = getUpdateSqlList('upgrade/sql_scripts', $dbversion);
    $success = true;
    
    if ($dbversion == 0) {
        if (parse_mysql_dump("upgrade.sql")) {
            $success = $success && true;
            $dbversion = get_data_model_version();
        } else {
            $success = false;
        }
    }
    
    // go through the upgrades and execute any that are greater than the current version
 //   $sql_upgrade = $dbversion;
    foreach ($sql_upgrades as $sql_upgrade) {
        if (parse_mysql_dump($path."/upgrade".$sql_upgrade.".sql") || $skip) {
            $success = $success && set_data_model_version($sql_upgrade);
            if ($skip) { $skip = 0; }
        } else {
            $success = false;
			$failure_point = $sql_upgrade;
            break;
        }
    }

    if ($success != false) {
        return array($success, "Upgrade to database version $sql_upgrade succeeded.");
    } else {
        return array($success, "The upgrade failed (At upgrade '$failure_point') - check error_handler.log.");
    }
}

function runningDBconfig()
{
    // Determine if we are running off DB config. If not, return 0, so we know to 
    // display the appropriate upgrade instructions.
    if (defined('DATAMODEL_VERSION')) {
        return 1;
    } else {
        return 0;
    }
}

$step = @$_GET["step"] ? @$_GET["step"] : @$_POST["step"];
if (empty($step)) {
	$step = 1;
}

$tpl = new Template_API();
$tpl->setTemplate('upgrade.tpl.html');
$tpl->assign('setup', true);

$skip = 0;

switch ($step) {
    case 1:
        // do nothing
    break;
    case 2:
    if (!empty($_POST["upgrade"]) || !empty($_POST["skip"])) {
        if (!empty($_POST["skip"])) {
            $skip = 1;
        }
        list($res, $message) = upgrade($skip);
        $tpl->assign("result", $message);
        $tpl->assign("result_good", $res);
        $tpl->assign('display_config_changes', runningDBconfig());
        if (!$res) {
        	$step = 1;
        }
    }
    break;
    case 3:
        $sanity = SanityChecks::runAllChecks();
        $tpl->assign('sanity_results',$sanity);
    break;
    case 4:
        // Run the config upgrade thing.
        $result = saveExistingConfigToDB();
        $tpl->assign("upgrade_result", $result);
    break;
}

$tpl->assign('step', $step);


$tpl->displayTemplate();

?>
