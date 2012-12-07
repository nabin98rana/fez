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

// Loops through all records in eSpace, and inserts the ScopusID by
// searching the Scopus CitedBy Retrieve on DOI

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . 'class.scopus.php');
include_once(APP_INC_PATH . "class.record.php");

$max = 100; 	// Max number of primary key IDs to send with each service request call
$sleep = 0; 	// Number of seconds to wait for between successive service calls

$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only

$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

for($i=0; $i<((int)$listing['info']['total_pages']+1); $i++) {

	// Skip first loop - we have called getListing once already
	if($i>0) {
		$listing = Record::getListing(array(), array(9,10), $listing['info']['next_page'], $max, 'Created Date', false, false, $filter);
	}
	$input_keys = array();

	if (is_array($listing['list'])) {
	 	for($j=0; $j<count($listing['list']); $j++) {
	 		$record = $listing['list'][$j];
	 		$key = $record['rek_pid'];
	 		$eid = $record['rek_scopus_id'];	// We store the EID as the Scopus ID
	 		if(empty($eid)) {
		 		// Get DOI if one exists
		 		if(is_array($record['rek_link'])) {
		 			foreach($record['rek_link'] as $link) {
		 				if(strpos($link, 'http://dx.doi.org/') !== FALSE) {
		 					$doi = str_replace('http://dx.doi.org/', '', $link);
		 					$input_keys[$key] = array('doi' => $doi);
		 				}
		 			}
		 		}
	 		}
	 	}
	}

	if(count($input_keys) > 0) {
		$result = Scopus::getCitedByCount($input_keys);
		foreach($result as $pid => $link_data) {
			print "$pid: {$link_data['eid']}<br />";

			// Update record with new Scopus ID
			/*$record = new RecordObject($pid);
			$search_keys = array("Scopus ID");
        	$values = array($link_data['eid']);*/
        	//$record->addSearchKeyValueList($search_keys, $values, true, ' was added based on Scopus Service data');
		}

		sleep($sleep); // Wait before using the service again
	}
}
