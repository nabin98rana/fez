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
include_once(APP_INC_PATH . "class.fulltext_queue.php");

class BackgroundProcess_Update_Oa_Pids_With_Ulrichs extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_update_oa_pids_with_ulrichs.php';
    $this->name = 'Check links';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $this->updatePids();

    $this->setState(BGP_FINISHED);
  }

  function updatePids() {

    $db = DB_API::get();
    $log = FezLog::get();

    $query = "SELECT rek_issn_pid AS pid, rek_doi AS doi  FROM " . APP_TABLE_PREFIX . "record_search_key_issn
      INNER JOIN " . APP_TABLE_PREFIX . "ulrichs
      ON ulr_issn = rek_issn
      LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_oa_status
      ON rek_oa_status_pid = rek_issn_pid
      LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_doi
      ON rek_issn_pid = rek_doi_pid
      WHERE ulr_open_access = 'true' AND rek_oa_status IS NULL
      GROUP BY rek_issn_pid";

    try {
      $result = $db->fetchAll($query);
    } catch (Exception $ex) {

      $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
      return;
    }

    foreach ($result as $pidDetails) {
      $pid = $pidDetails['pid'];
      $doi = $pidDetails['doi'];
      $record = new RecordObject($pid);
      $open = $this->hasDatastreamOpen($pid);

      $history = "";
      if($open && empty($doi)) {
        $history = 'Ulrichs not added added, based on an open access attachment being present and no doi - OA Status = File (Publisher version)';
        $record->addSearchKeyValueList(array("OA Status"), array('453695'), true, $history);
      } else if (!empty($doi)) {
        $history = 'Ulrichs info added - OA Status = DOI, OA Embargo Days = 0';
        $record->addSearchKeyValueList(array("OA Status"), array('453693'), true, $history);
        $record->addSearchKeyValueList(array("OA Embargo Days"), array('0'), true, $history);
      } else {
        $history = 'Ulrichs info added - OA Status = Link (no DOI) based on no doi present and nothing open access, OA Embargo Days = 0';
        $record->addSearchKeyValueList(array("OA Status"), array('453694'), true, $history);
        $record->addSearchKeyValueList(array("OA Embargo Days"), array('0'), true, $history);
      };
    }
  }

  function hasDatastreamOpen($pid) {
    $status = Status::getID("Published");
    if ($status == Record::getSearchKeyIndexValue($pid, "Status", false)) {
      $datastreams = Fedora_API::callGetDatastreams($pid);
      foreach ($datastreams as $datastream) {
        if ($datastream['controlGroup'] == "M"
          && (!Misc::hasPrefix($datastream['ID'], 'preview_')
            && !Misc::hasPrefix($datastream['ID'], 'web_')
            && !Misc::hasPrefix($datastream['ID'], 'thumbnail_')
            && !Misc::hasPrefix($datastream['ID'], 'stream_')
            && !Misc::hasPrefix($datastream['ID'], 'presmd_'))
        ) {
          $userPIDAuthGroups = Auth::getAuthorisationGroups($pid, $datastream['ID']);
          if (in_array('Viewer', $userPIDAuthGroups)) {
            return true;
          }
        }
      }
    }
    return false;
  }
}
