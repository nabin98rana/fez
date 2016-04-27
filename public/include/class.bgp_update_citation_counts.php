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
include_once(APP_INC_PATH . 'class.esti_search_service.php');
include_once(APP_INC_PATH . 'class.scopus.php');
include_once(APP_INC_PATH . 'class.wok_service.php');
include_once(APP_INC_PATH . 'class.wok_queue.php');
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");

class BackgroundProcess_Update_Citation_Counts extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_update_citation_counts.php';
    $this->name = 'Update citation counts';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    if ($runType == 'wsp') {
      $this->updateWspCounts();
    }
    if ($runType == 'scopus') {
      $this->updateScopus();
    }
    if ($runType == 'scopus-empty') {
      $this->updateScopusEmptyOnly();
    }
    
    $this->setState(BGP_FINISHED);
  }

  function updateWspCounts() {
    $max = 100;    // Max number of primary key IDs to send with each service call

    $filter = [];
    $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
    $filter["searchKey" . Search_Key::getID("Object Type")] = 3; // records only
    $filter["manualFilter"] = "isi_loc_t_s:[* TO *]"; //only records that have an isi loc

    $wok_ws = new WokService(FALSE);
    $listing = Record::getListing([], [9,10], 0, $max, 'Created Date', FALSE, FALSE, $filter);
    ob_flush();
    for ($i = 0; $i < ((int) $listing['info']['total_pages'] + 1); $i++) {
      ob_flush();
      // Skip first loop - we have called getListing once already
      if ($i > 0) {
        $listing = Record::getListing([], [9,10], $listing['info']['next_page'], $max, 'Created Date', FALSE, FALSE, $filter);
      }

      $uts = [];
      if (is_array($listing['list'])) {
        for ($j = 0; $j < count($listing['list']); $j++) {
          $ut = $listing['list'][$j]['rek_isi_loc'];
          if (!empty($ut)) {
            array_push($uts, $ut);
          }
        }
      }
      if (!empty($uts)) {
        $records_xml = $wok_ws->retrieveById($uts);

        if ($records_xml) {
          $records = simplexml_load_string($records_xml);
          foreach ($records->REC as $record) {
            if ($record->UID) {
              $recordUid = str_ireplace("WOS:", "", $record->UID);
              $pid = Record::getPIDByIsiLoc($recordUid);
              ob_flush();
              Record::updateThomsonCitationCount($pid, $record->dynamic_data->citation_related->tc_list->silo_tc->attributes()->local_count, $recordUid);
            }
          }
        }
        if (APP_SOLR_INDEXER == "ON") {
          FulltextQueue::singleton()->commit();
        }
        sleep(WOK_SECONDS_BETWEEN_CALLS); // to work within TR minimum throttling requirements
      }
    }
  }

  function updateScopus() {
    $max = APP_SCOPUS_API_RECORDS_PER_REQUEST; 	// Max number of primary key IDs to send with each service request call
    $sleep = 1; 	// Number of seconds to wait for between successive service calls
    $regex = "/^2-s2\\.0-[0-9]{10,11}/";
    $filter = [];
    $filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
    $filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
    $filter["manualFilter"] = "scopus_id_t:[* TO *]"; //only records that have a scopus id assigned

    $listing = Record::getListing([], [9,10], 0, $max, 'Created Date', false, false, $filter);
    for($i=0; $i<((int)$listing['info']['total_pages']+1); $i++) {

      // Skip first loop - we have called getListing once already
      if($i>0 && $listing['info']['next_page'] != '-1') {
        $listing = Record::getListing([], [9,10], $listing['info']['next_page'], $max, 'Created Date', false, false, $filter);
      }
      $input_keys = [];

      if (is_array($listing['list'])) {
        for($j=0; $j<count($listing['list']); $j++) {
          $record = $listing['list'][$j];
          $key = $record['rek_pid'];
          $eid = $record['rek_scopus_id'];	// We store the EID as the Scopus ID
          if(! empty($eid) && preg_match($regex, $eid)) {
            $input_keys[$key] = ['eid' => $eid];
          }
        }
      }

      if (count($input_keys) > 0) {
        $result = Scopus::getCitedByCount($input_keys);
        if ($result) { // non-empty array

          // Check that all the pids came back in the response,
          // otherwise set eid/pid to 0.

          foreach ($input_keys as $input_pid => $input_array) {
            if (is_array($result) && !array_key_exists($input_pid, $result)) {
              //can't find this pid in the response so set this eid to 0
              Record::updateScopusCitationCount($input_pid, 0, $input_array['eid']);
            }
          }
          foreach ($result as $pid => $link_data) {
            $eid = $link_data['eid'];
            if (is_numeric($link_data['citedByCount'])) {
              $count = $link_data['citedByCount'];
            } else {
              $count = 0;
            }

            Record::updateScopusCitationCount($pid, $count, $eid);
          }
          if (APP_SOLR_INDEXER == "ON") {
            FulltextQueue::singleton()->commit();
          }
        }
        sleep($sleep); // Wait before using the service again
      }
    }
  }

  function updateScopusEmptyOnly() {
    $max = 100; 	// Max number of primary key IDs to send with each service request call
    $sleep = 1; 	// Number of seconds to wait for between successive service calls
    $regex = "/^2-s2\\.0-[0-9]{10,11}/";
    $filter = [];
    $filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
    $filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
    $filter["manualFilter"] = "scopus_id_t:[* TO *] AND !scopus_citation_count_i:[* TO *] "; //only records that have a scopus id assigned and don't already have a citation count set
    $listing = Record::getListing([], [9,10], 0, $max, 'Created Date', false, false, $filter);
    echo "Found ".$listing['info']['total_rows']." pids that have a scopus id to update their citation count \n";

    for($i=0; $i<((int)$listing['info']['total_pages']+1); $i++) {

      // Skip first loop - we have called getListing once already
      if($i>0 && $listing['info']['next_page'] != '-1') {
        $listing = Record::getListing([], [9,10], $listing['info']['next_page'], $max, 'Created Date', false, false, $filter);
      }
      $input_keys = [];
      if (is_array($listing['list'])) {
        for($j=0; $j<count($listing['list']); $j++) {
          $record = $listing['list'][$j];
          $key = $record['rek_pid'];
          $eid = $record['rek_scopus_id'];	// We store the EID as the Scopus ID

          if(! empty($eid) && preg_match($regex, $eid)) {
            $input_keys[$key] = ['eid' => $eid];
          }
        }
      }
      if(count($input_keys) > 0) {
        $result = Scopus::getCitedByCount($input_keys);
        // first check that all the pids came back in the response, otherwise set that eid/pid to 0
        foreach ($input_keys as $input_pid => $input_array) {
          if (is_array($result) && !array_key_exists($input_pid, $result)) {
            Record::updateScopusCitationCount($input_pid, 0, $input_array['eid']);
          }
        }
        foreach($result as $pid => $link_data) {
          $eid = $link_data['eid'];
          if (is_numeric($link_data['citedByCount'])) {
            $count = $link_data['citedByCount'];
          } else {
            $count = 0;
          }
          Record::updateScopusCitationCount($pid, $count, $eid);
        }
        if ( APP_SOLR_INDEXER == "ON" ) {
          FulltextQueue::singleton()->commit();
        }
        sleep($sleep); // Wait before using the service again
      }
    }

  }

}
