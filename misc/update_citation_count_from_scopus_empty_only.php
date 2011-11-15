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
include_once(APP_INC_PATH . 'class.scopus.php');
include_once(APP_INC_PATH . "class.record.php");

$max = 100; 	// Max number of primary key IDs to send with each service request call
$sleep = 1; 	// Number of seconds to wait for between successive service calls 

$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
$filter["manualFilter"] = "scopus_id_t_s:[* TO *] AND !scopus_citation_count_i:[* TO *] "; //only records that have a scopus id assigned and don't already have a citation count set

//$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);
//echo "Found ".$listing['info']['total_rows']." pids that have a scopus id to update their citation count \n";
Auth::createLoginSession('uqckorte', 'Christiaan Kortekaas', 'c.kortekaas@library.uq.edu.au', '');
$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);
echo "Found ".$listing['info']['total_rows']." pids that have a scopus id to update their citation count \n";
//exit;
for($i=0; $i<((int)$listing['info']['total_pages']+1); $i++) {
	
	// Skip first loop - we have called getListing once already
	if($i>0 && $listing['info']['next_page'] != '-1') {
		$listing = Record::getListing(array(), array(9,10), $listing['info']['next_page'], $max, 'Created Date', false, false, $filter);
	}
	$input_keys = array();
	
	if (is_array($listing['list'])) {
	 	for($j=0; $j<count($listing['list']); $j++) {
	 		$record = $listing['list'][$j];
	 		$key = $record['rek_pid'];
	 		$eid = $record['rek_scopus_id'];	// We store the EID as the Scopus ID
	 		if(! empty($eid)) {
	 			$input_keys[$key] = array('eid' => $eid);
	 		}
	 	}
	}
	if(count($input_keys) > 0) {
		$result = Scopus::getCitedByCount($input_keys);
        //first check that all the pids came back in the response, otherwise set that eid/pid to 0
        foreach ($input_keys as $input_pid => $input_array) {
            if (is_array($result) && !array_key_exists($input_pid, $result)) {
                //can't find this pid in the response so set this eid to 0
//                echo "SETTING ZERO cause not found ".$input_pid." for eid ".$input_array['eid']."\n";
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
//            echo "DID FIND ".$input_pid." for eid ".$input_array['eid']." with count ".$linked_data['citedByCount']."\n";
			Record::updateScopusCitationCount($pid, $count, $eid);
		}
		sleep($sleep); // Wait before using the service again		
	}
}
