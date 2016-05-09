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
include_once(APP_INC_PATH . 'class.scopus.php');
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . 'class.scopus_queue.php');

class BackgroundProcess_Insert_Scopus_Id_Using_Doi_Search extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_insert_scopus_id_using_doi_search.php';
    $this->name = 'Insert Scopus ID using DOI search';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $this->insertScopusId();

    $this->setState(BGP_FINISHED);
  }

  function insertScopusId() {

    $db = DB_API::get();
    $log = FezLog::get();

    $max = 100; 	// Max number of primary key IDs to send with each service request call
    $sleep = 1; 	// Number of seconds to wait for between successive service calls

    $stmt = "SELECT rek_pid, rek_doi FROM " . APP_TABLE_PREFIX . "record_search_key
          LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_scopus_id
          ON rek_scopus_id_pid = rek_pid
          INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_doi
          ON rek_doi_pid = rek_pid
          WHERE rek_scopus_id_pid IS NULL";

    try {
      $res = $db->fetchAll($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return;
    }

    $input_keys_all = array();
    foreach($res as $row) {
      $input_keys_all[$row['rek_pid']] = array('doi' => $row['rek_doi']);
    }

    $sq = ScopusQueue::get();
    for($i=0; $i< ((int)(count($res)/$max) +1); $i++) {
      $input_keys = array_slice($input_keys_all, $i*$max, 100);
      if(count($input_keys) > 0 ) {
        $result = Scopus::getCitedByCount($input_keys);
        foreach($result as $pid => $link_data) {
          if (!empty($link_data['eid'])) {
            // Update record with new Scopus ID
            $record = new RecordObject($pid);
            $search_keys = array("Scopus ID");
            $values = array($link_data['eid']);
            $record->addSearchKeyValueList(
              $search_keys, $values, true, ' was added based on Scopus Service data given current doi'
            );
            $sq->add($link_data['eid']);
          }
        }
        sleep($sleep); // Wait before using the service again
      }
    }

  }
}
