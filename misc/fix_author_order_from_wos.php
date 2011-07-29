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
include_once(APP_INC_PATH . "class.wok_service.php");
include_once(APP_INC_PATH . "class.wos_record.php");
include_once(APP_INC_PATH . "class.matching_journals.php");


$matches = "";
$fails = "";
$mc = new RJL();
$max = 100; 		// Max number of primary key IDs to send with each ESTI Search Service request call
/*$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
*/
//$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

$query1 = "SELECT
  count(rek_isi_loc) as county

FROM fez_record_search_key_author a
  INNER JOIN fez_record_search_key_isi_loc
    ON rek_author_pid = rek_isi_loc_pid
  INNER JOIN fez_premis_event
    ON rek_author_pid = pre_pid
  INNER JOIN fez_record_search_key_author_id b
    ON rek_author_id_pid = rek_author_pid
      AND rek_author_id_order = rek_author_order ";
//  LEFT JOIN __temp_bug_matched_authors ON rek_author_pid = mat_pid

$query1 .= "WHERE pre_detail LIKE '%using author matching%' "; // AND mat_pid IS NULL
$query1 .= "GROUP BY rek_isi_loc
";
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
echo "count $total count for checking \n";
$inc = 100;
$wok_ws = new WokService(FALSE);
	ob_flush();
for($i=0; $i<($total+$inc); $i=$i+$inc) {
	
	$query2 = "SELECT
  rek_isi_loc,
  rek_author_pid
FROM fez_record_search_key_author a
  INNER JOIN fez_record_search_key_isi_loc
    ON rek_author_pid = rek_isi_loc_pid
  INNER JOIN fez_premis_event
    ON rek_author_pid = pre_pid
  INNER JOIN fez_record_search_key_author_id b
    ON rek_author_id_pid = rek_author_pid
      AND rek_author_id_order = rek_author_order ";

//$query2 .= " LEFT JOIN __temp_bug_matched_authors ON rek_author_pid = mat_pid ";
$query2 .= " WHERE pre_detail LIKE '%using author matching%' "; // AND mat_pid IS NULL
$query2 .= " GROUP BY rek_isi_loc
LIMIT ".$inc." OFFSET ".$i;

	echo $query2 ."\n";
  
	ob_flush();
	try {
	        $listing = $db->fetchAll($query2);
	} catch (Exception $ex) {
          print_r($ex->getMessage());
	        $log = FezLog::get();
	        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
	        return;
	}
	$compareTitle = array();
  $uts = array();
//  print_r($listing);
//  exit;
	if (is_array($listing)) {
	 	for($j=0; $j<count($listing); $j++) {
	 		$pid = $listing[$j]['rek_author_pid'];
//      $title = $listing[$j]['tr_title'];
	 		$rek_isi_loc = $listing[$j]['rek_isi_loc'];
//       echo "here: ".$tr_isi_loc."\n"; exit;
      array_push($uts, $rek_isi_loc);
//      $compareTitle[$tr_isi_loc] = array('pid' => $pid, 'title' => $title);


//			$record = new RecordGeneral($pid);
//			$search_keys = array("Subtype");
//			$values = array($subtype);
//			echo "about to modify $pid with subtype ".$subtype."\n";
//			$record->addSearchKeyValueList($search_keys, $values, true);
	 	}
    echo "\nChecking these UTs:\n";
    print_r($uts);
    $result = $wok_ws->retrieveById($uts);
    $wokCompare = array();
    if ($result) {
      $doc = new DOMDocument();
      $doc->loadXML($result);
      $recs = $doc->getElementsByTagName("REC");

      foreach ($recs as $rec_elem) {

        $rec = new WosRecItem($rec_elem);
        $wok_isi_loc = $rec->ut;
//        $wok_title =  $mc->normaliseTitle($rec->itemTitle);
        $authorCompare[$wok_isi_loc] = $rec->authors;
      }

      for($j=0; $j<count($listing); $j++) {
        $pid = $listing[$j]['rek_author_pid'];
        $isi_loc = $listing[$j]['rek_isi_loc'];
        $record = new RecordObject($pid);

        $authors = $record->getAuthors();
        $wok_count = count($authorCompare[$isi_loc]);
        $fez_count = count($authors);
        if ($wok_count != $fez_count) {
          $fails .= "----------------\n";
          $fails .= "$pid author count differs (fez:".$fez_count.", wok:$wok_count)\n";
          $fails .= "eSpace: ".print_r($authors,true);
          $fails .= "\n vs \n";
          $fails .= "WoK: ".print_r($authorCompare[$isi_loc],true);
          $fails .= "----------------\n";
        }

        foreach ($authorCompare[$isi_loc] as $akey => $authorWok) {
          if ($authorWok != $authors[$akey]['name']) {
            $fails .= "@@@@@@@@@@@@@@@@@@@\n";
            $fails .= "$pid Author name at order position ". ($akey + 1) ." differs - fez: ".$authors[$akey]['name']." vs ".$authorWok."\n";
            $fails .= "@@@@@@@@@@@@@@@@@@@\n";
          }
        }
      }
	  } else {
      echo "couldn't get a result for UTs ".print_r($uts,true)." \n";
    }
  }
}
echo $matches;
echo "-------\n";
echo $fails;
