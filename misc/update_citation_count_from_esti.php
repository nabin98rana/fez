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
include_once(APP_INC_PATH . "class.record.php");

$max = 100; 		// Max number of primary key IDs to send with each ESTI Search Service request call
$sleep = 1; 	// Number of seconds to wait for between successive ESTI Search Service calls 

$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only

$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

for($i=0; $i<((int)$listing['info']['total_pages']+1); $i++) {
	
	// Skip first loop - we have called getListing once already
	if($i>0)
		$listing = Record::getListing(array(), array(9,10), $listing['info']['next_page'], $max, 'Created Date', false, false, $filter);
	
	$primary_keys = '';
	if (is_array($listing['list'])) {
	 	for($j=0; $j<count($listing['list']); $j++) {
	 		$ut = $listing['list'][$j]['rek_isi_loc'];
	 		if(! empty($ut))
	 			$primary_keys .= $ut.' ';		
	 	}
	}
	
	if(!empty($primary_keys)) {
		$records_xml = EstiSearchService::retrieve($primary_keys);
		
		if($records_xml) {
			foreach($records_xml->REC as $record) {
				if(@$record->item) {
					$pid = Record::getPIDByIsiLoc($record->item->ut);	
					Record::updateThomsonCitationCount($pid, $record->attributes()->timescited, $record->item->ut);
				}
			}
		}
		sleep($sleep); // Wait before using the ESTI Search Service again		
	}	
}