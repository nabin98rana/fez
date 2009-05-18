<pre><?php
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
include_once(APP_INC_PATH . 'class.google_scholar.php');
include_once(APP_INC_PATH . "class.record.php");

$max = 50; 	// Max number of primary key IDs to retrieve at a time
$sleep = 63; 	// Number of seconds to wait for between successive service calls 
$options = array();
$options["sort_order"] = 1;
$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only

$listing = Record::getListing($options, array(9,10), 0, $max, 'Created Date', false, false, $filter);

for($i=0; $i<((int)$listing['info']['total_pages']+1); $i++) {
	
	// Skip first loop - we have called getListing once already
	if($i>0)
		$listing = Record::getListing($options, array(9,10), $listing['info']['next_page'], $max, 'Created Date', false, false, $filter);
	
	if (is_array($listing['list'])) {		
	 	for($j=0; $j<count($listing['list']); $j++) {
	 		
	 		$pid = $listing['list'][$j]['rek_pid'];
	 		$title = $listing['list'][$j]['rek_title'];
	 		$authors = '';
	 		for($k=0; $k<count($listing['list'][$j]['rek_author']); $k++) {
	 			$authors .=  $listing['list'][$j]['rek_author'][$k] . ' ';
	 		}
	 		$journal = $listing['list'][$j]['rek_journal_name'];	 		
	 		$year = date('Y', strtotime($listing['list'][$j]['rek_date']));
	 		
	 		$cites = Google_Scholar::citationcounts_scholar_citedby($title, $authors, $journal, $year);
			if($cites) {
				print "Updating $pid with ".$cites['citedby'] .', '.$cites['link']."\n";
				Record::updateGoogleScholarCitationCount($pid, $cites['citedby'], $cites['link']);
			}	 			 		
	 	}
		sleep($sleep); // Wait before using the ESTI Search Service again
	}	
}
