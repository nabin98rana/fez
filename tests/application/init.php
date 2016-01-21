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

chdir(dirname(__FILE__));
runDatabaseTasks();

/**
 * This method creates the database (if necessary), and sets up all tables & start-up data.
 *
 */
function runDatabaseTasks() {

  $host       = 'fezdb';
  $database   = 'fez';
  $user       = 'fez';
  $pass       = 'fez';

//  $conn = @mysql_connect($host, $user, $pass);
  $conn = new PDO('mysql:host='.$host, $user, $pass);
  if (!$conn) {
    return "Could not connect to the specified database host with these credentials.";
  }

  // Connect to the specified database.
//  if (!mysql_select_db($database)) {
  if (!$conn->query('use '.$database)) {
    // If we can't, attempt to create it.
    $dbCreateResult = attemptCreateDB($database, $conn);
    if ($dbCreateResult !== "") {
      return $dbCreateResult;
    } else {
      // Second attempt database connection with the supplied credentials.
      if (!$conn->query('use '.$database)) {
        return "Could not connect to the newly created database with the nominated credentials.";
      }
    }
  }
  parseMySQLdump($conn, "./../../public/setup/schema.sql");
  parseMySQLdump($conn, "./../../public/setup/data.sql");
}

/**
 * This method grabs an SQL dump file and runs whatever it finds inside. Thrills for the whole family!
 *
 */
function parseMySQLdump($conn, $url, $ignoreerrors = false) {
  $file_content = file($url);
  $query = "";
  foreach($file_content as $ln => $sql_line) {
    $sql_line = str_replace('%TABLE_PREFIX%', 'fez_', $sql_line);
    $tsl = trim($sql_line);
    if (($sql_line != "") && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != "#")) {
      $query .= $sql_line;
      if(preg_match("/;\s*$/", $sql_line)) {
        $result = $conn->query($query);
        if (!$result && !$ignoreerrors) {
          return $conn->errorInfo();
        }
        $query = "";
      }
    }
  }

  return "";
}

