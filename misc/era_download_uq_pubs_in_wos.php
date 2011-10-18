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

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . 'class.esti_search_service.php');
include_once(APP_INC_PATH . 'class.wok_service.php');
include_once(APP_INC_PATH . 'class.wok_queue.php');
include_once(APP_INC_PATH . "class.record.php");

$query = 'OG=(Univ Queensland)';
$depth = '4week';
$timeSpan = array();
$editions = '';
$sort = '';
$first_rec = 1;
$num_recs = WOK_BATCH_SIZE;

$wok_ws = new WokService(FALSE);

//$result = EstiSearchService::searchRetrieve('WOS', $query, $depth, $editions, $sort, $first_rec, $num_recs);

// Do an initial sleep just in something else ran just before this..
sleep(WOK_SECONDS_BETWEEN_CALLS);
$response = $wok_ws->search("WOS", $query, $editions, $timeSpan, $depth, "en", $num_recs);
$queryId = $response->return->queryID;
$records_found = $response->return->recordsFound;

$result = $response->return->records;
//$result = $wok_ws->retrieve($first_rec, $num_recs);

//$records_found = (int)$result['recordsFound'];
$pages = ceil(($records_found/$num_recs));
$wq = WokQueue::get();
for($i=0; $i<$pages; $i++) {
	
	$first_rec += $num_recs;
	
	if($i>0) {
		//$result = EstiSearchService::searchRetrieve('WOS', $query, $depth, $editions, $sort, $first_rec, $num_recs);
        sleep(WOK_SECONDS_BETWEEN_CALLS);
        $response = $wok_ws->retrieve($queryId, $first_rec, $num_recs);
        $result = $response->return->records;
    }
//	$records = @simplexml_load_string($result['records']);
    $records = @simplexml_load_string($result);
	
	if($records) {
		foreach($records->REC as $record) {
			if(@$record->item) {
                $ut = (string) $record->item->ut;
                $wq->add($ut);
//				$pid = Record::getPIDByIsiLoc($record->item->ut);
			}
		}
	}	
}
// Commmented the line out below 
//$wok_ws->closeSession();
