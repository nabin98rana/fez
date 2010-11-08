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
include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.fedora_api.php");

$max = 100; 		// Max number of primary key IDs to send with each ESTI Search Service request call
/*$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
*/
//$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

$query1 = "SELECT count(*) as cc
FROM fez_record_search_key
INNER JOIN fez_record_search_key_subject ON rek_pid = rek_subject_pid 
INNER JOIN " . APP_TABLE_PREFIX . "controlled_vocab ON rek_subject = cvo_id 
INNER JOIN " . APP_TABLE_PREFIX . "controlled_vocab_relationship ON cvr_child_cvo_id = cvo_id AND cvr_parent_cvo_id = '450000'
INNER JOIN fez_xsd_display ON xdis_id = rek_display_type AND xdis_version = 'MODS 1.0' AND xdis_title IN ('Journal Article', 'Conference Paper', 'Book', 'Book Chapter', 'Conference Proceeding', 'Conference Item', 'Online Journal Article')
LEFT JOIN fez_record_search_key_herdc_code ON rek_pid = rek_herdc_code_pid
WHERE rek_herdc_code IS NULL
GROUP BY rek_pid
";

//$query1 = 'SELECT count(*) as subtype_count FROM __era_subtype_manual_cleanup INNER JOIN ' . APP_TABLE_PREFIX . 'record_search_key on rek_pid = st_pid ';
echo $query1."\n";
ob_flush();
$db = DB_API::get();
$log = FezLog::get();

try {
        $total = $db->fetchAll($query1);
				$total = count($total);
} catch (Exception $ex) {
        $log = FezLog::get();
        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
        return;
}
echo "count $total subject count for updating \n";
$inc = 100;

	ob_flush();
for($i=0; $i<($total+$inc); $i=$i+$inc) {

	$query2 = "SELECT rek_pid,  rek_subject, cvo_title
	FROM fez_record_search_key
	INNER JOIN fez_record_search_key_subject ON rek_pid = rek_subject_pid 
	INNER JOIN " . APP_TABLE_PREFIX . "controlled_vocab ON rek_subject = cvo_id 
	INNER JOIN " . APP_TABLE_PREFIX . "controlled_vocab_relationship ON cvr_child_cvo_id = cvo_id AND cvr_parent_cvo_id = '450000'  
	INNER JOIN fez_xsd_display ON xdis_id = rek_display_type AND xdis_version = 'MODS 1.0' AND xdis_title IN ('Journal Article', 'Conference Paper', 'Book', 'Book Chapter', 'Conference Proceeding', 'Conference Item', 'Online Journal Article')
	LEFT JOIN fez_record_search_key_herdc_code ON rek_pid = rek_herdc_code_pid
	WHERE rek_herdc_code IS NULL
GROUP BY rek_pid
	ORDER BY rek_date ASC LIMIT ".$inc.' OFFSET '.$i;
	
//	$query2 = "SELECT * FROM __era_subtype_manual_cleanup INNER JOIN " . APP_TABLE_PREFIX . "record_search_key on rek_pid
// = st_pid ORDER BY st_pid ASC  LIMIT ".$inc." OFFSET ".$i;

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
	 		$cvo_id = $listing[$j]['rek_subject'];
  		$cvo_title = $listing[$j]['cvo_title'];
			$record = new RecordGeneral($pid);
			// get parent cvo title eg HERDC Category Codes -> this will go into the new source attribute for the subject
			// $parent_title = Controlled_Vocab::getTopParentTitle($cvo_id);			
		//	$parents = Controlled_Vocab::getParentListFullDisplay($cvo_id);
		//	$parent_title = $parents[0]["cvo_title"];
			// 
/*			$values = array($cvo_id);
			if ($parent_title == "HERDC Category Codes") {
				$search_keys = array("HERDC Code");
			} elseif ($parent_title == "Fields of Research") {
				$search_keys = array("FOR");				
			} elseif ($parent_title == "Research Fields, Courses and Disciplines") {
				$search_keys = array("RFCD");
			} elseif ($parent_title == "Socio-Economic Objective (1998)") {
				$search_keys = array("SEO_1998");
			} elseif ($parent_title == "Socio-Economic Objective (2008)") {		
				$search_keys = array("SEO_2008");
			} else {
				continue;
			}*/
			echo "about to modify $pid with title ".$cvo_title." and subject ".$cvo_id."\n";			
			$history = "Copied in HERDC code from deprecated Subject field / search key value ".$cvo_title."(".$cvo_id.")";
//			$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", $search_keys, $values, false, $history);
//			$datastreamName =  "Metadata Object Description Schema";
			$datastreamName =  "MODS";

			$doc = Fedora_API::callGetDatastreamContents($pid, $datastreamName, true);
//			echo($doc);
//			$xpath = new DOMXPath($doc);
//			$xpath_query = "/mods/*";
//			$fieldNodeList = $xpath->query($xpath_query);
		// LACHLAN SUGGESTED HAX
			$doc = str_replace("</mods:mods>", "", $doc);
			

			$add = '<mods:subject ID="'.$cvo_id.'" authority="HERDC">
			<mods:topic>'.$cvo_title.'</mods:topic>
			</mods:subject>';
			$doc .= $add;	
			$doc .= "</mods:mods>";
//			echo $doc;
			
			Fedora_API::callModifyDatastreamByValue($pid, "MODS", "A", "Metadata Object Description Schema", $doc, "text/xml", "inherit");
			History::addHistory($pid, null, "", "", true, $history);
			
			Record::setIndexMatchingFields($pid);
			
//exit;

			
	 	}
	}
}
