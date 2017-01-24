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
include_once(APP_INC_PATH . 'class.bgp_migrate_fedora.php');

class BackgroundProcess_Db_Load_Citations extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_db_load_citations.php';
    $this->name = 'DB load citations';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));
    $this->loadDb();
    $this->setState(BGP_FINISHED);
  }

  function loadDb() {
    $log = FezLog::get();

    $environment = $_SERVER['APP_ENVIRONMENT'];
    if ($environment !== 'production') {
      $log->err('DB load failed: Unknown environment - ' . $environment);
      return;
    }
    set_time_limit(0);

    $db = DB_API::get();
    $path = '/tmp/' . $environment;
    system("rm -Rf ${path}");
    mkdir($path);
    chdir($path);

    if (! system("AWS_ACCESS_KEY_ID=" .
      AWS_KEY. " AWS_SECRET_ACCESS_KEY=" .
      AWS_SECRET .
      " bash -c \"aws s3 cp s3://uql-fez-${environment}-cache/fez${environment}-citations.tar.gz ${path}/fez${environment}-citations.tar.gz\"")
    ) {
      $log->err('DB load failed: Unable to copy Fez DB from S3');
      exit;
    }

    system("cd ${path} && tar xzvf ${path}/fez${environment}-citations.tar.gz --no-same-owner --strip-components 1");

    $tables = [
      'fez_scopus_citations',
      'fez_scopus_citations_cache',
      'fez_thomson_citations',
      'fez_thomson_citations_cache'
    ];
    foreach ($tables as $tbl) {
      $db->query('DROP TABLE IF EXISTS ' . $tbl);
      $sql = file_get_contents($path . '/' . $tbl . '.sql');
      $db->query($sql);
    }

    $dsn = "mysql:host=".APP_SQL_DBHOST.";dbname=".APP_SQL_DBNAME;
    $con = new PDO($dsn, APP_SQL_DBUSER, APP_SQL_DBPASS,
      array(
        PDO::MYSQL_ATTR_LOCAL_INFILE => 1,
      ));

    foreach ($tables as $tbl) {
      $sql = "LOAD DATA LOCAL INFILE '${path}/" . $tbl . ".txt' INTO TABLE ${tbl}" .
        " FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n'";
      $stmt = $con->prepare($sql);
      $stmt->execute();
    }
  }
}
