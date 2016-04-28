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
// | Authors: Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . 'class.background_process.php');

class BackgroundProcess_Staging_Db_Load extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_staging_db_load.php';
    $this->name = 'Links AMR check';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));
    $this->loadDb();
    $this->setState(BGP_FINISHED);
  }

  function loadDb() {
    if ($_SERVER['APPLICATION_ENV'] !== 'staging') {
      echo 'Not in staging..';
      exit;
    }
    set_time_limit(0);

    $log = FezLog::get();
    $db = DB_API::get();
    $path = '/tmp/staging';

    system("rm -Rf ${path}");
    mkdir($path);
    chdir($path);

    if (! system("AWS_ACCESS_KEY_ID=" .
      AWS_KEY. " AWS_SECRET_ACCESS_KEY=" .
      AWS_SECRET .
      " bash -c \"aws s3 cp s3://uql-fez-staging/fezstaging.tar.gz ${path}/fezstaging.tar.gz\"")
    ) {
      $log->err('Staging import failed: Unable to copy Fez staging DB from S3');
      exit;
    }

    system("cd ${path} && tar xzvf ${path}/fezstaging.tar.gz --no-same-owner --strip-components 1");

    $files = glob($path . "/*.sql");
    foreach ($files as $sql) {
      $sql = file_get_contents($sql);
      $db->query($sql);
    }

    $files = glob($path . "/*.txt");

    $dsn = "mysql:host=".APP_SQL_DBHOST.";dbname=".APP_SQL_DBNAME;
    $con = new PDO($dsn, APP_SQL_DBUSER, APP_SQL_DBPASS,
      array(
        PDO::MYSQL_ATTR_LOCAL_INFILE => 1,
      ));

    foreach ($files as $txt) {
      $tbl = basename($txt, '.txt');

      $sql = "LOAD DATA LOCAL INFILE '${path}/" . basename($txt) . "' TRUNCATE INTO TABLE ${tbl}" .
        " FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\r\\n'";
      $stmt = $con->prepare($sql);

      $stmt->execute();
    }

    $stmt = $con->prepare('DELETE FROM fez_user WHERE usr_username LIKE \'%\_test\'');
    $stmt->execute();
  }
}
