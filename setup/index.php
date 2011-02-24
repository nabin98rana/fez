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

startSetup();       // Let's get this moveable feast underway!
exit;



/**
 * This is the main method that controls the flow of the setup process, and triggers the different stages.
 * 
 */
function startSetup() {

    $rootpath = dirname(dirname(__FILE__));
    ini_set("include_path", '.');
    include_once "$rootpath/include/Smarty/Smarty.class.php";
    include_once "$rootpath/include/class.default.data.php";

    $tpl = new Smarty();
    $tpl->template_dir = "$rootpath/templates/en";
    $templates_c = "$rootpath/templates_c";
    $tpl->compile_dir = $templates_c;
    $tpl->config_dir = '';

    // check for the existence of templates_c and try to create it, otherwise smarty won't display.
    if (!file_exists($templates_c) and ! @mkdir($templates_c)) {
        $tpl->compile_dir = "/tmp";
        $tpl->assign("problems", "Could not find or create the Smarty template compilation directory:<br>\n<pre>$templates_c</pre><br>\nThis directory needs to be writeable by the webserver. Please correct this problem and refresh this page.");
        $tpl->display('setup.tpl.html');
        exit;
    }
    if (!is_writeable($templates_c)) {
        $tpl->compile_dir = "/tmp";
        $tpl->assign("problems", "The Smarty template compilation directory needs to be writeable by the webserver:<br>\n<pre>$templates_c</pre><br>\n Please correct this problem and refresh this page.");
        $tpl->display('setup.tpl.html');
        exit;
    }

    $tpl->assign('setup', true);

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
			$app_path = dirname(dirname(__FILE__)) . "/";
			if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) {
				$app_path = preg_replace('/\\\/i', '/', $app_path);
			}
			$tpl->assign('default_path', $app_path);
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

    $rootpath = dirname(dirname(__FILE__));

    // If there's already a configuration file there, the site may already be setup.
    clearstatcache();
    if (file_exists("$rootpath/config.inc.php") && filesize("$rootpath/config.inc.php") > 0) {
        return "An existing 'config.inc.php' was found in Fez's root directory. Your site may already be configured. If you wish to proceed with this installation, delete the file and refresh this page.";
    }

    // If the file exists, check that it's writable. If it doesn't, create it.
    clearstatcache();
    if (file_exists("$rootpath/config.inc.php")) {
        if (!is_writable("$rootpath/config.inc.php")) {
            return "The file 'config.inc.php' in Fez's root directory needs to be writable by the web server user. Please correct this problem and refresh this page.";
        }
    } else {
        $fp = @fopen("$rootpath/config.inc.php", 'w');
        if ($fp === false) {
            return "Could not create the file 'config.inc.php'. The web server needs to be able to create this file in Fez's root directory. You can bypass this error message by creating the file yourself, and ensuring that it is writable by the web server. Please correct this problem and refresh this page.";
        }
        if (@fwrite($fp, "") === false) {
            return "Could not write to 'config.inc.php'. The file should be writable by the user that the web server runs as. Please correct this problem and refresh this page.";
        }
        fclose($fp);
    }

    // Find out if we can read the configuration template.
    clearstatcache();
    if (!is_readable("$rootpath/upgrade/config.inc.php.NEW")) {
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
    $dbtype     = $_POST['app_sql_dbtype'];
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
    if ($dbtype == 'pdo_mysql' and !function_exists(mysql_connect)) {
        return "The MySQL PHP extension is not installed. Please correct the problem and refresh this page.";
    }
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

    $rootpath = dirname(dirname(__FILE__));

    // Extract the form values
    $path       = $_POST['app_path'];
	$rel_url    = $_POST['app_relative_url'];
    $dbtype     = $_POST['app_sql_dbtype'];
    $host       = $_POST['app_sql_dbhost'];
    $database   = $_POST['app_sql_dbname'];
    $user       = $_POST['app_sql_dbuser'];
    $pass       = $_POST['app_sql_dbpass'];

    // Get the config file template
    clearstatcache();
    $filename = "$rootpath/upgrade/config.inc.php.NEW";
    $handle = fopen($filename, "r");
    $contents = fread($handle, filesize($filename));
    fclose($handle);

    // Perform swap-outs for user-supplied values
    $contents = str_replace("DATABASE_TYPE_HERE", $dbtype, $contents);
    $contents = str_replace("HOST_NAME_HERE", $host, $contents);
    $contents = str_replace("DB_NAME_HERE", $database, $contents);
    $contents = str_replace("USER_HERE", $user, $contents);
    $contents = str_replace("PASS_HERE", $pass, $contents);
	$contents = str_replace("APP_PATH_HERE", $path, $contents);
	$contents = str_replace("REL_URL_HERE", $rel_url, $contents);

    // Write the file to where it needs to go
    clearstatcache();
    $fp = @fopen("$rootpath/config.inc.php", 'w');
    if ($fp === FALSE) {
        return "Could not open the file 'config.inc.php' for writing. The permissions on the file should be set as to allow the user that the web server runs as to open it. Please correct this problem and refresh this page.";
    }
    $res = @fwrite($fp, $contents);
    if ($res === FALSE) {
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

?>
