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

array_shift($argv);
$ARGS = $argv;
$tasks = (@$ARGS[0]) ? @$ARGS[0] : 'schema';

chdir(dirname(__FILE__));
InitSystem::runDatabaseTasks($tasks);


Class InitSystem
{

  /**
   * This method creates the database (if necessary), and sets up all tables & start-up data.
   * @param String $tasks
   * @return String
   */
  public static function runDatabaseTasks($tasks)
  {

    $host = 'fezdb';
    $database = 'fez';
    $user = 'fez';
    $pass = 'fez';

    $conn = new PDO('mysql:host=' . $host, $user, $pass);
    if (!$conn) {
      return "Could not connect to the specified database host with these credentials.";
    }

    // Connect to the specified database.
    if (!$conn->query('use ' . $database)) {
      // If we can't, attempt to create it.
      $dbCreateResult = InitSystem::attemptCreateDB($database, $conn);
      if ($dbCreateResult !== "") {
        return $dbCreateResult;
      } else {
        // Second attempt database connection with the supplied credentials.
        if (!$conn->query('use ' . $database)) {
          return "Could not connect to the newly created database with the nominated credentials.";
        }
      }
    }

    switch ($tasks) {
      case 'schema':
        $path = './../../public/setup/';
        InitSystem::parseMySQLdump($conn, $path . "schema.sql");
        InitSystem::parseMySQLdump($conn, $path . "data.sql");
        break;
      case 'seed':
        InitSystem::runSeed($conn);
        break;
      default:
        break;
    }

    return "Run complete";
  }

  /**
   * This method grabs an SQL dump file and runs whatever it finds inside. Thrills for the whole family!
   * @param PDO $conn
   * @param String $url
   * @param Bool $ignoreErrors
   * @return String
   */
  public static function parseMySQLdump($conn, $url, $ignoreErrors = false)
  {
    $file_content = file($url);
    $query = "";
    foreach ($file_content as $ln => $sql_line) {
      $sql_line = str_replace('%TABLE_PREFIX%', 'fez_', $sql_line);
      $sql_line = str_replace('%AWS_ACCESS_KEY_ID%', @$_SERVER['AWS_ACCESS_KEY_ID'], $sql_line);
      $sql_line = str_replace('%AWS_SECRET_ACCESS_KEY%', @$_SERVER['AWS_SECRET_ACCESS_KEY'], $sql_line);
      $sql_line = str_replace('%FEZ_S3_BUCKET%', @$_SERVER['FEZ_S3_BUCKET'], $sql_line);
      $sql_line = str_replace('%FEZ_S3_SRC_PREFIX%', @$_SERVER['FEZ_S3_SRC_PREFIX'], $sql_line);
      $sql_line = str_replace('%AWS_CLOUDFRONT_KEY_PAIR_ID%', @$_SERVER['AWS_CLOUDFRONT_KEY_PAIR_ID'], $sql_line);
      $sql_line = str_replace('%AWS_CLOUDFRONT_PRIVATE_KEY_FILE%', @$_SERVER['AWS_CLOUDFRONT_PRIVATE_KEY_FILE'], $sql_line);
      $sql_line = str_replace('%AWS_CLOUDFRONT_FILE_SERVE_URL%', @$_SERVER['AWS_CLOUDFRONT_FILE_SERVE_URL'], $sql_line);

      $tsl = trim($sql_line);
      if (($sql_line != "") && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != "#")) {
        $query .= $sql_line;
        if (preg_match("/;\\s*$/", $sql_line)) {
          $result = $conn->query($query);
          if (!$result && !$ignoreErrors) {
            return $conn->errorInfo();
          }
          $query = "";
        }
      }
    }

    return "";
  }

  public static function attemptCreateDB($dbName, $conn) {

    if (!$conn->query('CREATE DATABASE ' . $dbName)) {
      return InitSystem::getErrorMessage('create_db', $conn->errorInfo());
    }

    return "";

  }

  public static function getErrorMessage($type, $message)
  {
    if (empty($message)) {
      return '';
    } else {
      if (is_array($message)) {
        $message = implode(", ". $message);
      }
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

  /**
   * @param PDO $conn
   */
  public static function runSeed($conn)
  {
    $path = './../../.docker/development/backend/db/seed/';
//  parseMySQLdump($conn, $path . "installdb.sql");
    InitSystem::parseMySQLdump($conn, $path . "citation.sql");
    InitSystem::parseMySQLdump($conn, $path . "cvs.sql");
    InitSystem::parseMySQLdump($conn, $path . "development.sql");
    InitSystem::parseMySQLdump($conn, $path . "workflows.sql");
    InitSystem::parseMySQLdump($conn, $path . "xsd.sql");

    if ($_SERVER['APP_ENVIRONMENT'] === 'testing') {
      InitSystem::parseMySQLdump($conn, $path . "jetsetup.sql");
      InitSystem::parseMySQLdump($conn, $path . "disablesolr.sql");
    }

    // Finished unless AWS environment is configured
    if (
    !(
        array_key_exists('AWS_ACCESS_KEY_ID', $_SERVER) &&
        array_key_exists('AWS_SECRET_ACCESS_KEY', $_SERVER) &&
        array_key_exists('FEZ_S3_BUCKET', $_SERVER) &&
        array_key_exists('FEZ_S3_SRC_PREFIX', $_SERVER) &&
        (
            strpos($_SERVER['FEZ_S3_BUCKET'], 'uql-fez-dev') === 0 ||
            strpos($_SERVER['FEZ_S3_BUCKET'], 'uql-fez-testing') === 0
        )
    )
    ) {
      return;
    }

    // Dev bucket requires a prefix as it's shared amongst multiple developers
    if (
        $_SERVER['FEZ_S3_BUCKET'] === 'uql-fez-dev' &&
        empty($_SERVER['FEZ_S3_SRC_PREFIX'])
    ) {
      return;
    }

    InitSystem::parseMySQLdump($conn, $path . "aws.sql");
    include_once './../../public/config.inc.php';
    $aws = AWS::get();
    $prefixes = ['cache', 'data', 'mail', 'san_import', 'sitemap', 'solr_upload'];
    foreach ($prefixes as $p) {
      $aws->deleteMatchingObjects($p);
      $aws->putObject($p . '/');
    }

    include_once(APP_INC_PATH . "/../upgrade/fedoraBypassMigration/MigrateFromFedoraToDatabase.php");
    $migrate = new MigrateFromFedoraToDatabase();

    $db = DB_API::get();
    try {
      $db->exec("UPDATE " . APP_TABLE_PREFIX . "config " .
          " SET config_value = 'ON' " .
          " WHERE config_name='app_fedora_bypass'");
      $db->exec("UPDATE " . APP_TABLE_PREFIX . "config " .
          " SET config_value = 'UQ' " .
          " WHERE config_name='app_pid_namespace'");
      $db->exec("UPDATE " . APP_TABLE_PREFIX . "config " .
          " SET config_value = 'ON' " .
          " WHERE config_name='app_xsdmf_index_switch'");
      $db->exec("UPDATE " . APP_TABLE_PREFIX . "config " .
          " SET config_value = '' " .
          " WHERE config_name='app_fedora_username'");
      $db->exec("UPDATE " . APP_TABLE_PREFIX . "config " .
          " SET config_value = '' " .
          " WHERE config_name='app_fedora_pwd'");

      InitSystem::parseMySQLdump($conn, $path . "nofedora.sql");

    } catch (Exception $ex) {
      return;
    }
    $migrate = new MigrateFromFedoraToDatabase(false);
    $migrate->runMigration();
  }

}
