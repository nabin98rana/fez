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
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . "class.scopus_queue.php");

class BackgroundProcess_Find_Expired_Eids extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_find_expired_eids.php';
    $this->name = 'Find expired EIDs';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $this->findExpired();

    $this->setState(BGP_FINISHED);
  }

  function findExpired() {
    $db = DB_API::get();
    $log = FezLog::get();

    $body = '';
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $stmt =  "SELECT * FROM fez_record_search_key_scopus_id
                LEFT JOIN fez_record_search_key ON rek_pid = rek_scopus_id_pid
                LEFT JOIN fez_record_search_key_doi ON rek_pid = rek_doi_pid
                WHERE (rek_scopus_citation_count = '0' OR rek_scopus_citation_count IS NULL) AND rek_pid IS NOT NULL
                GROUP BY rek_scopus_id_pid";

    try {
      $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return;
    }

    $scopusService = new ScopusService(APP_SCOPUS_API_KEY);
    $sq = ScopusQueue::get();
    foreach($res as $row) {
      $pid = $row['rek_pid'];
      $eid = $row['rek_scopus_id'];
      $doi = $row['rek_doi'];
      $scopusId = false;

      $this->setHeartbeat();

      $result = $scopusService->getRecordByScopusId($eid);
      if (strpos($result, 'RESOURCE_NOT_FOUND') !== false) {

        if ($doi) {
          $xml = $scopusService->getRecordsBySearchQuery('DOI('.$doi.')');
          $pregMatches = array();
          preg_match('/<dc:identifier>SCOPUS_ID\:(\d+)<\/dc:identifier>/', $xml, $pregMatches);
          $scopusId = (array_key_exists(1, $pregMatches)) ? $pregMatches[1] : null;
        }

        if ($scopusId) {
          $list_new_info = array();
          $list_new_value = array();

          $record = $scopusService->getRecordByScopusId($scopusId);
          $sri = new ScopusRecItem();
          $sri->load($record);
          $sri_fields = $sri->getFields();

          $list_new_info[] = 'Scopus ID';
          $list_new_value[] = '2-s2.0-'.$scopusId;

          if (!empty($sri_fields['_issueVolume']) && $sri_fields['_issueVolume'] != Record::getSearchKeyIndexValue($pid, "Volume Number")) {
            $list_new_info[] = 'Volume Number';
            $list_new_value[] = $sri_fields['_issueVolume'];
          }
          if (!empty($sri_fields['_issueNumber']) && $sri_fields['_issueNumber'] != Record::getSearchKeyIndexValue($pid, "Issue Number")) {
            $list_new_info[] = 'Issue Number';
            $list_new_value[] = $sri_fields['_issueNumber'];
          }
          if (!empty($sri_fields['_startPage']) && $sri_fields['_startPage'] != Record::getSearchKeyIndexValue($pid, "Start Page")) {
            $list_new_info[] = 'Start Page';
            $list_new_value[] = $sri_fields['_startPage'];
          }
          if (!empty($sri_fields['_endPage']) && $sri_fields['_endPage'] != Record::getSearchKeyIndexValue($pid, "End Page")) {
            $list_new_info[] = 'End Page';
            $list_new_value[] = $sri_fields['_endPage'];
          }
          if (!empty($sri_fields['_xdisSubtype']) && $sri_fields['_xdisSubtype'] != Record::getSearchKeyIndexValue($pid, "Subtype")) {
            $list_new_info[] = 'Subtype';
            $list_new_value[] = $sri_fields['_xdisSubtype'];
          }
          if (!empty($sri_fields['_scopusDocTypeCode']) && $sri_fields['_scopusDocTypeCode'] != Record::getSearchKeyIndexValue($pid, "Scopus Doc Type", false)) {
            $list_new_info[] = 'Scopus Doc Type';
            $list_new_value[] = $sri_fields['_scopusDocTypeCode'];
          }

          $history = " Updating current Scopus ID and other information due to the previous ID no longer being valid";
          $record = new RecordObject($pid);
          $record->addSearchKeyValueList($list_new_info, $list_new_value, true, $history);

          $sq->add($scopusId);

        } else {
          if ($xml === NULL) {    // If empty but not null it is in scopus but not searchable.
            $body .= "Fail:" . " http://espace.library.uq.edu.au/view/" . $pid . " , " . $eid  . " No auto match found\n";
          }
        }

      }
      $this->setHeartbeat();
      usleep(500000);
      $this->setHeartbeat();
    }
    if (!empty($body)) {
      $mail = new Mail_API;
      $subject = "Records with invalid scopus IDs";
      $to = 'espace@library.uq.edu.au';
      $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
      $mail->setTextBody(stripslashes($body));
      $mail->send($from, $to, $subject, false);
    }
  }
}
