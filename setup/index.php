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

//if (isset($_GET)) {
//    $HTTP_POST_VARS = $_POST;
//    $HTTP_GET_VARS = $_GET;
//    $HTTP_SERVER_VARS = $_SERVER;
//    $HTTP_ENV_VARS = $_ENV;
//    $HTTP_POST_FILES = $_FILES;
//    // Seems like PHP 4.1.0 didn't implement the $_SESSION auto-global ...
//    if (isset($_SESSION)) {
//        $HTTP_SESSION_VARS = $_SESSION;
//    }
//    $HTTP_COOKIE_VARS = $_COOKIE;
//}

startSetup();       // Let's get this moveable feast underway!
exit;



/**
 * This is the main method that controls the flow of the setup process, and triggers the different stages.
 * 
 */
function startSetup() {

    ini_set("include_path", '.');
    include_once("../include/Smarty/Smarty.class.php");
    include_once("../include/class.default.data.php");

    $tpl = new Smarty();
    $tpl->template_dir = '../templates/en';
    $tpl->compile_dir = "../templates_c";
    $tpl->config_dir = '';

    $step = @$_GET["step"] ? @$_GET["step"] : @$_POST["step"];
    if ($step == "") { $step = 0; }
    $tpl->assign('step', $step);

    // Step-specific routines
    switch ($step) {
        case 0:
            $problems = prepareForSuperHappyFun();
            $tpl->assign('problems', $problems);
            break;
        case 1:
            if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) {
                $tpl->assign('default_path', "C:/apache/htdocs/your-fez-directory/");
            } else {
                $tpl->assign('default_path', "/usr/local/apache/htdocs/your-fez-directory/");            
            }
            break;
       case 2:
            // Test the values we've been given.
            $testResult = testBaseConfigValues();
            if ($testResult !== "") {
                $tpl->assign('test_result', $testResult);
                break;
            }
            // Write the values to the configuration file.
            $writeConfigResult = writeBaseConfigFile();
            if ($writeConfigResult !== "") {
                $tpl->assign('test_result', $writeConfigResult);
                break;
            }
            // Run all the SQL bits & pieces. Aight?
            $dbConfigResult = runDatabaseTasks();
            if ($dbConfigResult !== "") {
                $tpl->assign('sql_result', $dbConfigResult);
                break;
            }
            break;
    }

    $tpl->assign('rel_url', "../");
    $tpl->display('setup.tpl.html');
    exit;

}





/**
 * This method checks the existance / writability of different files required in the installation process.
 *
 */
function prepareForSuperHappyFun() {

    // If there's already a configuration file there, the site may already be setup.
    clearstatcache();
    if (file_exists('../config.inc.php') && filesize('../config.inc.php') > 0) {
        return "An existing 'config.inc.php' was found in Fez's root directory. Your site may already be configured. If you wish to proceed with this installation, delete the file and refresh this page.";
    }

    // If the file exists, check that it's writable. If it doesn't, create it.
    clearstatcache();
    if (file_exists('../config.inc.php')) {
        if (!is_writable('../config.inc.php')) {
            return "The file 'config.inc.php' in Fez's root directory needs to be writable by the web server user. Please correct this problem and refresh this page.";
        }
    } else {
        $fp = fopen('../config.inc.php', 'w');
        if ($fp === false) {
            return "Could not create the file 'config.inc.php'. The web server needs to be able to create this file in Fez's root directory. You can bypass this error message by creating the file yourself, and ensuring that it is writable by the web server. Please correct this problem and refresh this page.";
        }
        if (fwrite($fp, "") === false) {
            return "Could not write to 'config.inc.php'. The file should be writable by the user that the web server runs as. Please correct this problem and refresh this page.";
        }
        fclose($fp);
    }

    // Find out if we can read the configuration template.
    clearstatcache();
    if (!is_readable('../upgrade/config.inc.php.NEW')) {
        return "The file '/upgrade/config.inc.php.NEW' needs to be readable by the web server user. Please correct this problem and refresh this page.";
    }

    return "";

}





/**
 * This method tests the core variables for basic sanity. If things look reasonable, we will proceed to the next step.
 *
 */
function testBaseConfigValues() {

    // Extract the form values
    $path       = $_POST['app_path'];
    $relURL     = $_POST['app_relative_url'];
    $host       = $_POST['app_sql_dbhost'];
    $database   = $_POST['app_sql_dbname'];
    $user       = $_POST['app_sql_dbuser'];
    $pass       = $_POST['app_sql_dbpass'];

    // Make sure we're not obviously going to crash and burn.
    if ($path == "" || $host == "" || $database == "" || $user == "" || $pass == "" || $relURL == "") {
        return "You did not specify values for one or more variables.";
    }
    // Try plugging in the application path.
    clearstatcache();
    if (!file_exists($path)) {
        return "The specified path does not exist.";
    }
    // Attempt database connection with the supplied credentials.
    $conn = @mysql_connect($_POST['app_sql_dbhost'], $_POST['app_sql_dbuser'], $_POST['app_sql_dbpass']);
    if (!$conn) {
        return "Could not connect to the specified database host with these credentials.";
    }
    mysql_close($conn);

    // If we get to here, we're probably OK to proceed.
    return "";

}





/**
 * This method assembles the core configuration file, and writes it to disk.
 *
 */
function writeBaseConfigFile() {

    // Extract the form values
    $path       = $_POST['app_path'];
    $dbtype     = $_POST['app_sql_dbtype'];
    $host       = $_POST['app_sql_dbhost'];
    $database   = $_POST['app_sql_dbname'];
    $user       = $_POST['app_sql_dbuser'];
    $pass       = $_POST['app_sql_dbpass'];

    // Get the config file template
    clearstatcache();
    $filename = '../upgrade/config.inc.php.NEW';
    $handle = fopen($filename, "r");
    $contents = fread($handle, filesize($filename));
    fclose($handle);

    // Perform swap-outs for user-supplied values
    $contents = str_replace("DATABASE_TYPE_HERE", $dbtype, $contents);
    $contents = str_replace("HOST_NAME_HERE", $host, $contents);
    $contents = str_replace("DB_NAME_HERE", $database, $contents);
    $contents = str_replace("USER_HERE", $user, $contents);
    $contents = str_replace("PASS_HERE", $pass, $contents);
    $contents = str_replace("/usr/local/apache/htdocs/YOUR_PATH_HERE/", $path, $contents);

    // Write the file to where it needs to go
    clearstatcache();
    $fp = fopen('../config.inc.php', 'w');
    if ($fp === FALSE) {
        return "Could not open the file 'config.inc.php' for writing. The permissions on the file should be set as to allow the user that the web server runs as to open it. Please correct this problem and refresh this page.";
    }
    $res = fwrite($fp, $contents);
    if ($fp === FALSE) {
        return "Could not write the configuration information to 'config.inc.php'. The file should be writable by the user that the web server runs as. Please correct this problem and refresh this page.";
    }
    fclose($fp);

    return "";

}





/**
 * This method creates the database (if necessary), and sets up all tables & start-up data.
 *
 */
function runDatabaseTasks() {

    // Extract the form values
    $relURL     = $_POST['app_relative_url'];
    $host       = $_POST['app_sql_dbhost'];
    $database   = $_POST['app_sql_dbname'];
    $user       = $_POST['app_sql_dbuser'];
    $pass       = $_POST['app_sql_dbpass'];

    // Attempt database connection with the supplied credentials.
    $conn = @mysql_connect($host, $user, $pass);
    if (!$conn) {
        return "Could not connect to the specified database host with these credentials.";
    }

    // Connect to the specified database.
    if (!mysql_select_db($database)) {
        // If we can't, attempt to create it.
        $dbCreateResult = attemptCreateDB($database, $conn);
        if ($dbCreateResult !== "") {
            return $dbCreateResult;
        } else {
            // Second attempt database connection with the supplied credentials.
            if (!mysql_select_db($database)) {
                return "Could not connect to the newly created database with the nominated credentials.";
            }
        }
    }

    // Once we have a database, and can successfully connect to it ... execute the SQL schema dump.
    $attemptSQLparse = parseMySQLdump("schema.sql");
    if ($attemptSQLparse !== "") {
        return $attemptSQLparse;
    }

    // Provided this went off without a hitch, insert some minimal data.
    $attemptSQLparse = parseMySQLdump("data.sql");
    if ($attemptSQLparse !== "") {
        return $attemptSQLparse;
    }

    // Add some other crucial stuff to the config table that is reliant on form-based variables.
    $query = "INSERT INTO fez_config (`config_name`, `config_module`, `config_value`) values ('app_relative_url','core','" . $relURL . "');";
    mysql_query($query);

    // Build and write configuration stuff.
    if (!writeDefaultConfigValues()) {
        return "There was a problem writing the default configuration values to the config table.";
    }

    return "";

}




/**
 * This method grabs an SQL dump file and runs whatever it finds inside. Thrills for the whole family!
 *
 */
function parseMySQLdump($url, $ignoreerrors = false) {
    $file_content = file($url);
    $query = "";
    foreach($file_content as $ln => $sql_line) {
        $sql_line = str_replace('%TABLE_PREFIX%', 'fez_', $sql_line);
        $tsl = trim($sql_line);
        if (($sql_line != "") && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != "#")) {
            $query .= $sql_line;
            if(preg_match("/;\s*$/", $sql_line)) {
                $result = mysql_query($query);
                if (!$result && !$ignoreerrors) {
                    return mysql_error();
                }
                $query = "";
            }
        }
    }

    return "";
}





/**
 * This method creates an empty database of the specified name. This is invoked if the requested DB name 
 * cannot be found. If we aren't able to succeed, return a message to the calling function.
 *
 */
function attemptCreateDB($dbName, $conn) {

    if (!mysql_query('CREATE DATABASE ' . $dbName, $conn)) {
        return getErrorMessage('create_db', mysql_error());
    }

    return "";

}



/**
 * writeDefaultConfigValues
 *
 * This method writes default values for most configuration variables into the config table.
 * 
 * Returns true if success
 * Returns false if problem
 */
function writeDefaultConfigValues() {
    
    $configPairs = Default_Data::getConfDefaults();
    foreach ($configPairs as $key => $value) {
        $query = "INSERT INTO fez_config (`config_name`, `config_module`, `config_value`) values ('" . $key . "','core','" . mysql_escape_string($value) . "');";
        mysql_query($query);
    }

    return true;

}













/* // Old stuff below. Retaining for reference, as we're not completely done with re-writing this setup script yet.

function checkPermissions($file, $desc, $is_directory = FALSE)
{
    clearstatcache();
    if (!file_exists($file)) {
        if (!$is_directory) {
            // try to create the file ourselves then
            $fp = @fopen($file, 'w');
            if (!$fp) {
                return "$desc does not exist. Please create it and try again.";
            }
            @fclose($fp);
        } else {
            if (!@mkdir($file)) {
                return "$desc does not exist. Please create it and try again.";
            }
        }
    }
    clearstatcache();
    if (!is_writable($file)) {
        if (!stristr(PHP_OS, "win")) {
            // let's try to change the permissions ourselves
            @chmod($file, 0777);
            clearstatcache();
            if (!is_writable($file)) {
                return "$desc is not writable";
            }
        } else {
            return "$desc is not writable";
        }
    }
    if (stristr(PHP_OS, "win")) {
        // need to check whether we can really create files in this directory or not
        // since is_writable() is not trustworthy on windows platforms
        if (is_dir($file)) {
            $fp = @fopen($file . '/dummy.txt', 'w');
            if (!$fp) {
                return "$desc is not writable";
            }
            @fwrite($fp, 'test');
            @fclose($fp);
            // clean up after ourselves
            @unlink($file . '/dummy.txt');
        }
    }
    return "";
}

function checkRequirements()
{
    $errors = array();

    // check for GD support
    ob_start();
    phpinfo();
    $contents = ob_get_contents();
    ob_end_clean();
    if (!preg_match("/GD Support.*<\/td><td.*>enabled/U", $contents)) {
        $errors[] = "The GD extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly.";
    }
    if (!preg_match("/Tidy support.*<\/th><th.*>enabled/U", $contents)) {
        $errors[] = "The Tidy extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly.";
    }
    if (!preg_match("/[cC]URL support.*<\/td><td.*>enabled/i", $contents)) {
        $errors[] = "The CURL extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly.";
    }
    if (!preg_match("/DOM\/XML.*<\/td><td.*>enabled/U", $contents)) {
        $errors[] = "The DOM extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly.";
    }

    // check for MySQL support
    if (!function_exists('mysql_query')) {
        $errors[] = "The MySQL extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly.";
    }

    // check for the file_uploads php.ini directive
    if (ini_get('file_uploads') != "1") {
        $errors[] = "The 'file_uploads' directive needs to be enabled in your PHP.INI file in order for Fez to work properly.";
    }
    if (ini_get('allow_call_time_pass_reference') != "1") {
        $errors[] = "The 'allow_call_time_pass_reference' directive needs to be enabled in your PHP.INI file in order for Fez to work properly.";
    }
    $error = checkPermissions('../templates_c', "Directory 'templates_c'", TRUE);
    if (!empty($error)) {
        $errors[] = $error;
    }
    $error = checkPermissions('../config.inc.php', "File 'config.inc.php'");
    if (!empty($error)) {
        $errors[] = $error;
    }
    $error = checkPermissions('../error_handler.log', "File 'error_handler.log'");
    if (!empty($error)) {
        $errors[] = $error;
    }
    $error = checkPermissions('../setup.conf.php', "File 'setup.conf.php'");
    if (!empty($error)) {
        $errors[] = $error;
    }
    $error = checkPermissions('../include/private_key.php', "File 'include/private_key.php'");
    if (!empty($error)) {
        $errors[] = $error;
    }

    $html = '';
    if (count($errors) > 0) {
        $html = '<html>
<head>
<style type="text/css">
<!--
.default {
  font-family: Verdana, Arial, Helvetica, sans-serif;
  font-style: normal;
  font-weight: normal;
  font-size: 70%;
}
-->
</style>
</head>
<body>

<br /><br />

<table width="500" bgcolor="#003366" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td><img src="../images/icons/error.gif" hspace="2" vspace="2" border="0" align="left"></td>
          <td width="100%" class="default"><span style="font-weight: bold; font-size: 160%; color: red;">Configuration Error:</span></td>
        </tr>
        <tr>
          <td colspan="2" class="default">
            <br />
            <b>The following problems regarding file and/or directory permissions were found:</b>
            <br /><br />
            ' . implode("<br />", $errors) . '
            <br /><br />
            <b>Please provide the appropriate permissions to the user that the web server run as to write in the directories and files specified above.</b>
            <br /><br />
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</body>
</html>';
    }
    return $html;
}

$html = checkRequirements();
if (!empty($html)) {
    echo $html;
    exit;
}

//ini_set("include_path", '.');
//include_once("../include/Smarty/Smarty.class.php");
//
//$tpl = new Smarty();
//$tpl->template_dir = '../templates/en';
//$tpl->compile_dir = "../templates_c";
//$tpl->config_dir = '';

function replace_table_prefix($str)
{
    global $_POST;

    return str_replace('%TABLE_PREFIX%', $_POST['db_table_prefix'], $str);
}

function getErrorMessage($type, $message)
{
    if (empty($message)) {
        return '';
    } else {
        if (stristr($message, 'Unknown MySQL Server Host')) {
            return 'Could not connect to the MySQL database server with the provided information.';
        } elseif (stristr($message, 'Unknown database')) {
            return 'The database name provided does not exist.';
        } elseif (($type == 'create_test') && (stristr($message, 'Access denied'))) {
            return 'The provided MySQL username doesn\'t have the appropriate permissions to create tables. Please contact your local system administrator for further assistance.';
        } elseif (($type == 'drop_test') && (stristr($message, 'Access denied'))) {
            return 'The provided MySQL username doesn\'t have the appropriate permissions to drop tables. Please contact your local system administrator for further assistance.';
        }
        return $message;
    }
}

function getDatabaseList($conn)
{
    $db_list = mysql_list_dbs($conn);
    $dbs = array();
    while ($row = mysql_fetch_array($db_list)) {
        $dbs[] = $row['Database'];
    }
    return $dbs;
}

function getUserList($conn)
{
    @mysql_select_db('mysql');
    $res = @mysql_query('SELECT DISTINCT User from user');
    $users = array();
    // if the user cannot select from the mysql.user table, then return an empty list
    if (!$res) {
        return $users;
    }
    while ($row = mysql_fetch_row($res)) {
        $users[] = $row[0];
    }
    return $users;
}

function getTableList($conn)
{
    $res = mysql_query('SHOW TABLES', $conn);
    $tables = array();
    while ($row = mysql_fetch_row($res)) {
        $tables[] = $row[0];
    }
    return $tables;
}

function endsWith($haystack, $needle) 
{
    if (strrpos($haystack, $needle) == (strlen($haystack) - strlen($needle)) ) {
        return true;
    }
    return false;
}

function install()
{
    global $_POST;

    clearstatcache();
    // check if config.inc.php in the root directory is writable
    if (!is_writable('../config.inc.php')) {
        return "The file 'config.inc.php' in Fez's root directory needs to be writable by the web server user. Please correct this problem and try again.";
    }

    // gotta check and see if the provided installation path really exists...
    if (!file_exists($_POST['path'])) {
        return "The provided installation path could not be found. Please review your information and try again.";
    }

    if (!is_writable('../include/private_key.php')) {
        return "The file 'include/private_key.php' needs to be writable by the web server user. Please correct this problem and try again.";
    }
    $fp = fopen('../include/private_key.php', 'w');
    if ($fp === FALSE) {
        return "Could not open the file 'include/private_key.php' for writing. The permissions on the file should be set as to allow the user that the web server runs as to open it. Please correct this problem and try again.";
    }
    $res = fwrite($fp, $private_key);
    if ($fp === FALSE) {
        return "Could not write the configuration information to 'include/private_key.php'. The file should be writable by the user that the web server runs as. Please correct this problem and try again.";
    }
    fclose($fp);




    // check if we can connect
    $conn = @mysql_connect($_POST['db_hostname'], $_POST['db_username'], $_POST['db_password']);
    if (!$conn) {
        return getErrorMessage('connect', mysql_error());
    }
    $db_list = getDatabaseList($conn);
    $db_list = array_map('strtolower', $db_list);
    if (@$_POST['create_db'] == 'yes') {
        if (!in_array(strtolower($_POST['db_name']), $db_list)) {
            if (!mysql_query('CREATE DATABASE ' . $_POST['db_name'], $conn)) {
                return getErrorMessage('create_db', mysql_error());
            }
        }
    } else {
        if ((count($db_list) > 0) && (!in_array(strtolower($_POST['db_name']), $db_list))) {
            return "The provided database name could not be found. Review your information or specify that the database should be created in the form below.";
        }
    }
    // create the new user, if needed
    if (@$_POST["alternate_user"] == 'yes') {
        $user_list = getUserList($conn);
        if (count($user_list) > 0) {
            $user_list = array_map('strtolower', $user_list);
            if (@$_POST["create_user"] == 'yes') {
                if (!in_array(strtolower(@$_POST['fez_user']), $user_list)) {
                    if ($_POST['db_hostname'] == 'localhost') {
                        $stmt = "GRANT SELECT, UPDATE, DELETE, INSERT ON " . $_POST['db_name'] . ".* TO '" . $_POST["fez_user"] . "'@localhost IDENTIFIED BY '" . $_POST["fez_password"] . "'";
                    } else {
                        $stmt = "GRANT SELECT, UPDATE, DELETE, INSERT ON " . $_POST['db_name'] . ".* TO '" . $_POST["fez_user"] . "'@'%' IDENTIFIED BY '" . $_POST["fez_password"] . "'";
                    }
                    if (!mysql_query($stmt, $conn)) {
                        return getErrorMessage('create_user', mysql_error());
                    }
                }
            } else {
                if (!in_array(strtolower(@$_POST['fez_user']), $user_list)) {
                    return "The provided MySQL username could not be found. Review your information or specify that the username should be created in the form below.";
                }
            }
        }
    }
    // check if we can use the database
    if (!mysql_select_db($_POST['db_name'])) {
        return getErrorMessage('select_db', mysql_error());
    }
    // check the CREATE and DROP privileges by trying to create and drop a test table
    $table_list = getTableList($conn);
    $table_list = array_map('strtolower', $table_list);

    if (!in_array('fez_test', $table_list)) {
        if (!mysql_query('CREATE TABLE fez_test (test char(1))', $conn)) {
            return getErrorMessage('create_test', mysql_error());
        }
    }
    if (!mysql_query('DROP TABLE fez_test', $conn)) {
        return getErrorMessage('drop_test', mysql_error());
    }
	parse_mysql_dump("schema.sql");

    // substitute the appropriate values in config.inc.php!!!
    if (@$_POST['alternate_user'] == 'yes') {
        $_POST['db_username'] = $_POST['fez_user'];
        $_POST['db_password'] = $_POST['fez_password'];
    }
    $config_contents = implode("", file("config.inc.php-example"));
    if (@$_POST['ldap'] == 'yes') {
    	$config_contents = str_replace("%{LDAP_SWITCH}%", "ON", $config_contents);		
    } else {
    	$config_contents = str_replace("%{LDAP_SWITCH}%", "OFF", $config_contents);		
	}
	$config_contents = str_replace("%{LDAP_ORGANISATION}%", $_POST['ldap_org'], $config_contents);
	$config_contents = str_replace("%{LDAP_ROOT_DN}%", $_POST['ldap_root_dn'], $config_contents);
	$config_contents = str_replace("%{LDAP_PREFIX}%", $_POST['ldap_prefix'], $config_contents);
	$config_contents = str_replace("%{LDAP_SERVER}%", $_POST['ldap_server'], $config_contents);		
	$config_contents = str_replace("%{LDAP_PORT}%", $_POST['ldap_port'], $config_contents);		
    $config_contents = str_replace("%{APP_FEDORA_VERSION}%", $_POST['fedora_version'], $config_contents);    
    $config_contents = str_replace("%{APP_FEDORA_LOCATION}%", $_POST['fedora_location'], $config_contents);
    $config_contents = str_replace("%{APP_FEDORA_SSL_LOCATION}%", $_POST['fedora_ssl_location'], $config_contents);
    $config_contents = str_replace("%{APP_FEDORA_USERNAME}%", $_POST['fedora_username'], $config_contents);
    $config_contents = str_replace("%{APP_FEDORA_PWD}%", $_POST['fedora_password'], $config_contents);	
    $config_contents = str_replace("%{APP_ORG_NAME}%", $_POST['organisation'], $config_contents);
    $config_contents = str_replace("%{APP_SHORT_ORG_NAME}%", $_POST['short_org'], $config_contents);
    $config_contents = str_replace("%{APP_NAME}%", $_POST['app_name'], $config_contents);		
    $config_contents = str_replace("%{APP_ADMIN_EMAIL}%", $_POST['app_admin_email'], $config_contents);		    
    $config_contents = str_replace("%{APP_PID_NAMESPACE}%", $_POST['fedora_pid_namespace'], $config_contents);		    
    $app_path = trim($_POST['path']);
    if (!endsWith($app_path, '/')) {
        $app_path .= '/';
    }
    $config_contents = str_replace("%{APP_PATH}%", $app_path, $config_contents);
    $config_contents = str_replace("%{APP_SQL_DBHOST}%", $_POST['db_hostname'], $config_contents);
    $config_contents = str_replace("%{APP_SQL_DBNAME}%", $_POST['db_name'], $config_contents);
    $config_contents = str_replace("%{APP_SQL_DBUSER}%", $_POST['db_username'], $config_contents);
    $config_contents = str_replace("%{APP_SQL_DBPASS}%", $_POST['db_password'], $config_contents);
    $config_contents = str_replace("%{APP_TABLE_PREFIX}%", $_POST['db_table_prefix'], $config_contents);
    $config_contents = str_replace("%{APP_HOSTNAME}%", $_POST['hostname'], $config_contents);
    $config_contents = str_replace("%{FEDORA_DB_HOST}%", $_POST['fedora_db_hostname'], $config_contents);
    $config_contents = str_replace("%{FEDORA_DB_TYPE}%", $_POST['fedora_db_type'], $config_contents);
    $config_contents = str_replace("%{FEDORA_DB_DATABASE_NAME}%", $_POST['fedora_db_name'], $config_contents);
    $config_contents = str_replace("%{FEDORA_DB_USERNAME}%", $_POST['fedora_db_username'], $config_contents);
    $config_contents = str_replace("%{FEDORA_DB_PASSWD}%", $_POST['fedora_db_password'], $config_contents);
    $config_contents = str_replace("%{FEDORA_DB_PORT}%", $_POST['fedora_db_port'], $config_contents);
    
    $rel_url = trim($_POST['relative_url']);
    if (!endsWith($rel_url, '/')) {
        $rel_url .= '/';
    }
    $config_contents = str_replace("%{APP_RELATIVE_URL}%", $rel_url, $config_contents);
    if (@$_POST['is_ssl'] == 'yes') {
        $protocol_type = 'https://';
		$app_https = "ON";
    } else {
        $protocol_type = 'http://';
		$app_https = "OFF";		
    }
    $config_contents = str_replace("%{APP_HTTPS}%", $app_https, $config_contents);
    $config_contents = str_replace("%{PROTOCOL_TYPE}%", $protocol_type, $config_contents);
    $config_contents = str_replace("%{APP_FEDORA_SETUP}%", $_POST['fedora_setup'], $config_contents);	
	if (@$_POST['fedora_setup'] == 'sslall') { 
		$fedora_apim_protocol_type = 'https://';
		$fedora_apia_protocol_type = 'https://';				
	} else {
		if (@$_POST['fedora_setup'] == 'sslapim') { 
			$fedora_apim_protocol_type = 'https://';
		} else {
			$fedora_apim_protocol_type = 'http://';
		}
		$fedora_apia_protocol_type = 'http://';				
	}
    $config_contents = str_replace("%{APP_FEDORA_APIM_PROTOCOL_TYPE}%", $fedora_apim_protocol_type, $config_contents);
    $config_contents = str_replace("%{APP_FEDORA_APIA_PROTOCOL_TYPE}%", $fedora_apia_protocol_type, $config_contents);	

    $fp = fopen('../config.inc.php', 'w');
    if ($fp === FALSE) {
        return "Could not open the file 'config.inc.php' for writing. The permissions on the file should be set as to allow the user that the web server runs as to open it. Please correct this problem and try again.";
    }
    $res = fwrite($fp, $config_contents);
    if ($fp === FALSE) {
        return "Could not write the configuration information to 'config.inc.php'. The file should be writable by the user that the web server runs as. Please correct this problem and try again.";
    }
    fclose($fp);
    return 'success';
}

if (@$_POST["cat"] == 'install') {
    $res = install();
    $tpl->assign("result", $res);
}

// check if fez has possibly already been configured.
if (is_file('../config.inc.php')) {
	$str = file_get_contents('../config.inc.php');
    // if we can't find the placeholder for the DBHOST
    if (!strstr($str,'@define("APP_SQL_DBHOST", "%{APP_SQL_DBHOST}%");')) {
        // but we can find that it is defined to something
        if (strstr($str,'@define("APP_SQL_DBHOST"')) {
        	$tpl->assign('maybe_configured', true);
        }
    }
}

$full_url = dirname($_SERVER['PHP_SELF']);
$pieces = explode("/", $full_url);
$relative_url = array();
$relative_url[] = '';
foreach ($pieces as $piece) {
    if ((!empty($piece)) && ($piece != 'setup')) {
        $relative_url[] = $piece;
    }
}
$relative_url[] = '';
$relative_url = implode("/", $relative_url);

if (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') {
    $_SERVER['DOCUMENT_ROOT'] = substr($_SERVER['DOCUMENT_ROOT'], 0, -1);
}
$installation_path = $_SERVER['DOCUMENT_ROOT'] . $relative_url;

$tpl->assign("rel_url", $relative_url);
$tpl->assign("installation_path", $installation_path);
if (!empty($_SERVER['HTTPS'])) {
    $ssl_mode = 'enabled';
} else {
    $ssl_mode = 'disabled';
}
$tpl->assign('ssl_mode', $ssl_mode);
$tpl->assign('fedora_setup_options', array(
    'sslall' => 'ssl-authenticate-all',
    'sslapim' => 'ssl-authenticate-apim',
    'nosslall' => 'no-ssl-authenticate-all',
    'nosslapim' => 'no-ssl-authenticate-apim',
    ));

$tpl->display('setup.tpl.html');

*/

?>
