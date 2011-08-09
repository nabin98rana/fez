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
include_once(APP_INC_PATH . "class.duplicates_report.php");


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
  count(DISTINCT rek_isi_loc) as county

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
echo "counted $total for checking \n";
$inc = 100;

$orderDiff = 0;
$countDiff = 0;
$pidListCount = array();
$pidListOrder = array();
$pidListFix = array();
$pidAuthorCount = array();
$dr = new DuplicatesReport();
$wok_ws = new WokService(FALSE);
	ob_flush();
for($i=0; $i<($total); $i=$i+$inc) {
	
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
          $countDiff++;
          if (!in_array($pid, $pidListCount)) {
              array_push($pidListCount, $pid);
            }
        }
        if (!is_array($pidAuthorCount[$pid])) {
          $pidAuthorCount[$pid] = array();

          $pidAuthorCount[$pid]['ids'] = array();
          $pidAuthorCount[$pid]['id_found'] = 0;
        }
        foreach ($authorCompare[$isi_loc] as $akey => $authorWok) {

          if (preg_replace("/[^a-z]/", "", strtolower($authorWok)) != preg_replace("/[^a-z]/", "", strtolower($authors[$akey]['name']))) {
            $fails .= "@@@@@@@@@@@@@@@@@@@\n";
            $fails .= "$pid Author name at order position ". ($akey + 1) ." differs - fez: ".$authors[$akey]['name']." vs ".$authorWok."\n";
            $fails .= "@@@@@@@@@@@@@@@@@@@\n";
            $orderDiff++;
            if (!in_array($pid, $pidListOrder)) {
              array_push($pidListOrder, $pid);
            }
          }

          if (!is_array($pidListFix[$pid])) {
              $pidListFix[$pid] = array();
          }
          if (!is_array($pidListFix[$pid][$akey])) {
            $pidListFix[$pid][$akey] = array();
          }

          $pidListFix[$pid][$akey]['name'] = $authorWok;
//            $pidListFix[$pid][$akey]['name'] = $authors[$akey]['name'];
//print_r($authors);



          foreach ($authors as $apkey => $authorPair) {
            $authorTokens = $dr->tokenise($authors[$apkey]['name']);
//            print_r($authorTokens);
            foreach ($authorTokens as $atoken) {
              $x = preg_replace("/[^a-z ]/", "", strtolower($authorWok));
//              $y = preg_replace("/[^a-z]/", "", strtolower($atoken));
              $y = strtolower($atoken);
              if (is_numeric(strpos($x, $y)) && ($pidListFix[$pid][$akey]['aut_id'] != $authors[$apkey]['aut_id'])) {
                echo "$pid token matched $y on $x \n";

//              if (preg_replace("/[^a-z]/", "", strtolower($authorWok)) == preg_replace("/[^a-z]/", "", strtolower($authors[$apkey]['name']))) {
                if (!is_array($pidListFix[$pid])) {
                  $pidListFix[$pid] = array();
                }
                if (!is_array($pidListFix[$pid][$akey])) {
                  $pidListFix[$pid][$akey] = array();
                }

                $pidListFix[$pid][$akey]['aut_id'] = $authors[$apkey]['aut_id'];
                if ($authors[$apkey]['aut_id'] != 0) {
                  $pidAuthorCount[$pid]['id_found'] = $pidAuthorCount[$pid]['id_found'] + 1;
                }
              }
            }
            if ($authors[$apkey]['aut_id'] != 0) {
              if (!in_array($authors[$apkey]['aut_id'], $pidAuthorCount[$pid]['ids'])) {
                $pidAuthorCount[$pid]['ids'][] = $authors[$apkey]['aut_id'];
              }
            }
          }
          if (!array_key_exists('aut_id', $pidListFix[$pid][$akey])) {
            if (!is_array($pidListFix[$pid])) {
              $pidListFix[$pid] = array();
            }
            if (!is_array($pidListFix[$pid][$akey])) {
              $pidListFix[$pid][$akey] = array();
            }

            $pidListFix[$pid][$akey]['aut_id'] = 0;
          }
        }
      }
	  } else {
      echo "couldn't get a result for UTs ".print_r($uts,true)." \n";
    }
  }
}
//echo $matches;

echo $fails;
echo "-------\n";
echo "Total count of order difference is $orderDiff. Total count of count difference is $countDiff \n ";
echo "-------\n";
echo " The pids with different COUNTS of authors (".count($pidListCount).") are:\n";
if (is_array($pidListCount)) {
  foreach ($pidListCount as $pl) {
    echo $pl." - http://espace.library.uq.edu.au/view/".$pl."\n";
  }
} else {
  echo "(none) \n";
}

echo "-------\n";
echo " The pids with different ORDER of authors (".count($pidListOrder).") are:\n";
if (is_array($pidListOrder)) {
  foreach ($pidListOrder as $pl) {
    echo $pl." - http://espace.library.uq.edu.au/view/".$pl."\n";
  }
} else {
  echo "(none) \n";
}
echo "-------\n";

echo "The correct order of pids and authors is:\n";
print_r($pidListFix);


echo "Going to fix these now..\n";
$differentCounts = 0;
foreach ($pidListFix as $fpid => $fix) {
  if (!in_array($fpid, $pidListCount)) {
    if ($pidAuthorCount[$fpid]['id_found'] != count($pidAuthorCount[$fpid]['ids'])) {
      echo "$fpid has ".count($pidAuthorCount[$fpid]['ids'])." but could only match author ids on ".$pidAuthorCount[$fpid]['id_found']." so will have to do this one manually \n";
      $differentCounts++;
    } else {
      if (in_array($fpid, $pidListOrder)) {
        $record = new RecordObject($fpid);
        $record->replaceAuthors($fix, "Fixed author ordering based on Web of Knowledge");
        echo "Fixed $fpid http://espace.library.uq.edu.au/view/".$fpid."\n";
      }
    }
  }
}
echo "Found $differentCounts different counts that will need manual replacing to maintain the author id associations correctly.\n";
echo "Done!";


