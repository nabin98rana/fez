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

//$max = 100; 	// Max number of primary key IDs to send with each service request call
$max = 0; 	// Max number of primary key IDs to send with each service request call

$sleep = 0; 	// Number of seconds to wait for between successive service calls 
$current = 0;
//$filter = array();
//$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
//$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only

//$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

$listing = Scopus::getReturnedEIDTaggingList();
$total_pages = count($listing);
//$listing = Scopus::getReturnedEIDTaggingList($current, $max);

if (is_array($listing)) {
 	for($j=0; $j<count($listing); $j++) {
 		$record = $listing[$j];
 		$pid = $record['uq_pid'];
 		$eid = $record['sco_eid'];	// We store the EID as the Scopus ID
		$rec = new RecordGeneral($pid);
		$search_keys = array("Scopus ID");
       	$values = array($eid);
       	$rec->addSearchKeyValueList($search_keys, $values, true, ' was added based on Scopus EID Tagging data');

 	}
}
