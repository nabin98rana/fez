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
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . 'class.scopus_queue.php');
include_once(APP_INC_PATH . 'class.wok_service.php');
include_once(APP_INC_PATH . 'class.wok_queue.php');
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.researcherid.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");

class BackgroundProcess_Download_Uq_Pubs extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_download_uq_pubs.php';
    $this->name = 'Download new UQ pubs';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    if ($runType == 'wos') {
      $this->downloadFromWos();
    }
    if ($runType == 'scopus') {
      $this->downloadFromScopus();
    }
    if ($runType == 'researcherid') {
      $this->downloadFromResearcherId();
    }

    $this->setState(BGP_FINISHED);
  }

  function downloadFromWos() {
    echo "Downloading from WoS..";
    $query = 'OG=(Univ Queensland)';
    $depth = '4week';
    $timeSpan = array();
    $databaseID = "WOS";

    //Edition set to "" should default to all
    $editions = array();
    $first_rec = 1;
    $num_recs = WOK_BATCH_SIZE;
    $wok_ws = new WokService(FALSE);

    // Do an initial sleep just in something else ran just before this..
    sleep(WOK_SECONDS_BETWEEN_CALLS);
    $response = $wok_ws->search($databaseID, $query, $editions, $timeSpan, $depth, "en", $num_recs);
    if (is_soap_fault($response)) {
      $log = FezLog::get();
      $log->err($response->getMessage());
      exit;
    }
    $queryId = $response->return->queryId;
    $records_found = $response->return->recordsFound;

    $result = $response->return->records;
    $pages = ceil(($records_found/$num_recs));
    $wq = WokQueue::get();
    for($i=0; $i<$pages; $i++) {
      if($i>0) {
        sleep(WOK_SECONDS_BETWEEN_CALLS);
        $response = $wok_ws->retrieve($queryId, $first_rec, $num_recs);
        $result = $response->return->records;
      }
      $first_rec += $num_recs;
      $records = @simplexml_load_string($result);

      if($records) {
        foreach($records->REC as $record) {
          if(@$record->UID) {
            $ut = (string) $record->UID;
            $ut = str_ireplace("WOS:", "", $ut );
            $wq->add($ut);
          }
        }
      }
    }
  }

  function downloadFromScopus() {
    echo "Downloading from Scopus..";
    $afids = array('60031004', '60087457');

    $scopusService = new ScopusService();
    $sq = ScopusQueue::get();
    $i = 0;
    $foundResults = TRUE;

    while ($i < 5030 && $foundResults) {
      $foundResults = FALSE;
      //get the last 30 days of recently added records
      $query = array(
        'query' => '(af-id(' . $afids[0] . ') OR af-id(' . $afids[1] . ')) AND recent(30)',
        'count' => 30,
        'start' => $i,
        'view' => 'STANDARD'
      );
      $resp = $scopusService->search($query);
      $doc = new DOMDocument();
      $doc->loadXML($resp);
      $records = $doc->getElementsByTagName('identifier');
      foreach ($records as $record) {
        if (!$foundResults) {
          $foundResults = TRUE;
        }
        $scopus_id = $record->nodeValue;
        echo $scopus_id . "\n";
        $sq->add($scopus_id);
      }
      $i += 30;
    }
  }

  function downloadFromResearcherId() {
    echo "Downloading from ResearcherID..";
    $authors = Author::getAllCurrentStaffWithResearcherId();
    $rids_chunked = array_chunk($authors, 100);
    $date = getdate(time()-3600*24*28); //28 days previous
    for ($i=0; $i<count($rids_chunked); $i++) {
      ResearcherID::downloadRequest($rids_chunked[$i], 'researcherIDs', 'researcherID', array($date['year'],$date['mon'],$date['mday']));
    }
  }
}
