<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005-2010 The University of Queensland,                |
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>        |
// +----------------------------------------------------------------------+
ini_set("display_errors", 1); // LKDB - tmp (was 1)
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.record.php");

$max = 100; 		// Max number of primary key IDs to send with each ESTI Search Service request call
/*$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
*/
//$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

$query1 = 'SELECT count(*) as empty_count FROM scd_dnf_doi_match WHERE existing_scopus_id_eid IS NULL';
echo $query1."\n";
ob_flush();
$db = DB_API::get();
$log = FezLog::get();

try {
        $total = $db->fetchOne($query1);
} catch (Exception $ex) {
        $log = FezLog::get();
        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
        return;
}
echo "count $total empty eids count for updating \n";
$inc = 100;

	ob_flush();
for($i=0; $i<($total+$inc); $i=$i+$inc) {

	$query2 = "SELECT * FROM scd_dnf_doi_match ORDER BY rek_pid ASC  LIMIT ".$inc." OFFSET ".$i;

	echo $query2 ."\n";
	ob_flush();
	try {
	        $listing = $db->fetchAll($query2);
	} catch (Exception $ex) {
	        $log = FezLog::get();
	        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
	        return;
	}

	if (is_array($listing)) {
	 	for($j=0; $j<count($listing); $j++) {
	 		$pid = $listing[$j]['rek_pid'];
	 		$scopus_id = $listing[$j]['dnf_eid'];
			$record = new RecordGeneral($pid);
			$search_keys = array("Scopus ID");
			$values = array($subtype);
			echo "about to modify $pid with Scopus ID ".$scopus_id."\n";
			$record->addSearchKeyValueList($search_keys, $values, true);
	 	}
	}
}
