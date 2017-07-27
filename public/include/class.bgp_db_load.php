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

class BackgroundProcess_Db_Load extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_db_load.php';
    $this->name = 'DB load';
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
      if ($environment !== 'staging') {
          $log->err('DB load failed: Unknown environment - ' . $environment);
          return;
      }
      set_time_limit(0);

      $db = DB_API::get();
      $path = '/tmp';
      chdir($path);

      if (! system("AWS_ACCESS_KEY_ID=" .
          AWS_KEY. " AWS_SECRET_ACCESS_KEY=" .
          AWS_SECRET .
          " bash -c \"aws s3 cp s3://uql-fez-${environment}-cache/prod.config.inc.php ${path}/prod.config.inc.php\"")
      ) {
          $log->err('DB config failed: Unable to copy Fez prod config from S3');
          return;
      }
      include_once($path . "/prod.config.inc.php");
      if (! system(
          "AWS_ACCESS_KEY_ID=" . AWS_KEY .
          " AWS_SECRET_ACCESS_KEY=" . AWS_SECRET .
          " bash -c \"aws s3 cp s3://uql-fez-${environment}-cache/fez_config_extras.sql ${path}/fez_config_extras.sql\"")
      ) {
          $log->err('DB load failed: Unable to copy Fez DB from S3');
          return;
      }

      $excludeTable = [
          APP_TABLE_PREFIX . 'jobs',
      ];

      $excludeData = [
          APP_TABLE_PREFIX . 'background_process',
          APP_TABLE_PREFIX . 'background_process_pids',
          APP_TABLE_PREFIX . 'statistics_all',
          APP_TABLE_PREFIX . 'statistics_buffer',
          APP_TABLE_PREFIX . 'sessions',
          APP_TABLE_PREFIX . 'statistics_all',
          APP_TABLE_PREFIX . 'thomson_citations',
          APP_TABLE_PREFIX . 'thomson_citations_cache',
          APP_TABLE_PREFIX . 'scopus_citations',
          APP_TABLE_PREFIX . 'scopus_citations_cache',
      ];
      try {
          $sql = "SHOW TABLES FROM " . DB_LOAD_PROD_SQL_DBNAME;
          $tables = $db->fetchCol($sql);
      }
      catch(Exception $ex) {
          $log->err('DB load failed: Unable to get tables names for Fez DB');
          return;
      }

      $count = 0;
      $totalCount = count($tables) - count($excludeTable);
      foreach ($tables as $table) {
          if (in_array($table, $excludeTable)) {
              continue;
          }
          $count++;
          echo "[$count/" . $totalCount . "] $table\n";
          $cmd = " mysqldump" .
              " -h" . DB_LOAD_PROD_SQL_DBHOST .
              " -u" . DB_LOAD_SQL_DBUSER .
              " -p" . DB_LOAD_SQL_DBPASS .
              " " . DB_LOAD_PROD_SQL_DBNAME .
              " " . $table .
              (in_array($table, $excludeData) ? " --no-data" : "") .
              " --add-drop-table" .
              " --routines=0" .
              " --triggers=0" .
              " --events=0" .
              " | mysql" .
              " -h" . APP_SQL_DBHOST .
              " -u" . DB_LOAD_SQL_DBUSER .
              " -p" . DB_LOAD_SQL_DBPASS .
              " " . APP_SQL_DBNAME;
          system($cmd);
          if ($table === APP_TABLE_PREFIX . 'config') {
              $sql = file_get_contents($path . '/fez_config_extras.sql');
              $db->query($sql);
          }
      }

      system(
          " mysqldump" .
          " -h" . DB_LOAD_PROD_SQL_DBHOST .
          " -u" . DB_LOAD_SQL_DBUSER .
          " -p" . DB_LOAD_SQL_DBPASS .
          " " . DB_LOAD_PROD_SQL_DBNAME .
          " --routines" .
          " --no-create-info" .
          " --no-create-db" .
          " --no-data" .
          " --skip-opt" .
          " | mysql" .
          " -h" . APP_SQL_DBHOST .
          " -u" . DB_LOAD_SQL_DBUSER .
          " -p" . DB_LOAD_SQL_DBPASS .
          " " . APP_SQL_DBNAME
      );

      @unlink($path . '/fez_config_extras.sql');
      @unlink($path . '/prod.config.inc.php');
  }
}
