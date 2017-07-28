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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>        |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . 'class.db_api.php');
include_once(APP_INC_PATH . 'class.fulltext_queue.php');
include_once(APP_INC_PATH . 'class.background_process.php');

class BackgroundProcess_Reindex_Recent_Pids_Into_Solr extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_reindex_recent_pids_into_solr.php';
    $this->name = 'Runs solr rebuild of any pids changed within a period';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $log = FezLog::get();
    $db = DB_API::get();

    $date = date('Y-m', strtotime('-2 days'));

    $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "fulltext_queue (ftq_pid, ftq_op) SELECT rek_pid, 'I' FROM fez_record_search_key
             LEFT JOIN " . APP_TABLE_PREFIX . "fulltext_queue ON rek_pid = ftq_pid
             WHERE ftq_pid IS NULL AND rek_updated_date > " . $db->quote($date) . " ORDER BY rek_updated_date ASC ";  #fulltext_queue is last in, first out
    try {
      $db->exec($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
    }

    FulltextQueue::triggerUpdate();

    $this->setState(BGP_FINISHED);
  }
}
