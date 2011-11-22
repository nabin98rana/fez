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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+

include_once('../config.inc.php');
include_once(APP_INC_PATH . 'class.esti_search_service.php');
include_once(APP_INC_PATH . 'class.wok_service.php');
include_once(APP_INC_PATH . 'class.wok_queue.php');
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");

$max = 100; 		// Max number of primary key IDs to send with each ESTI Search Service request call
$sleep = 1; 	// Number of seconds to wait for between successive ESTI Search Service calls 

$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
$filter["manualFilter"] = "isi_loc_t_s:[* TO *]"; //only records that have an isi loc

$wok_ws = new WokService(FALSE);
//sleep(WOK_SECONDS_BETWEEN_CALLS);
$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);
//echo "searching...\n";
ob_flush();
for($i=0; $i<((int)$listing['info']['total_pages']+1); $i++) {
//  echo "page ".$i."\n";
  ob_flush();
	// Skip first loop - we have called getListing once already
	if($i>0)
		$listing = Record::getListing(array(), array(9,10), $listing['info']['next_page'], $max, 'Created Date', false, false, $filter);
	
	$uts = array();
	if (is_array($listing['list'])) {
	 	for($j=0; $j<count($listing['list']); $j++) {
	 		$ut = $listing['list'][$j]['rek_isi_loc'];
	 		if(! empty($ut))
//	 			$primary_keys .= $ut.' ';
        array_push($uts, $ut);
	 	}
	}
//	print_r($uts);
	if(!empty($uts)) {
//		$records_xml = EstiSearchService::retrieve($primary_keys);

    $records_xml = $wok_ws->retrieveById($uts);

//    $response = $wok_ws->search("WOS", $query, $editions, $timeSpan, $depth, "en", $num_recs);
//    $queryId = $response->return->queryID;
//    $records_found = $response->return->recordsFound;
//
//    $result = $response->return->records;

    if ($records_xml) {
      $records = simplexml_load_string($records_xml);
		  foreach($records->REC as $record) {
				if($record->item) {
					$pid = Record::getPIDByIsiLoc($record->item->ut);
//          echo "updating $pid with ut".$record->item->ut." with count of ".$record->attributes()->timescited."\n";
          ob_flush();
					Record::updateThomsonCitationCount($pid, $record->attributes()->timescited, $record->item->ut);
				}
			}
		}
    if ( APP_SOLR_INDEXER == "ON" ) {
      FulltextQueue::singleton()->commit();
    }
    sleep(WOK_SECONDS_BETWEEN_CALLS); // to work within TR minimum throttling requirements 
	}
}