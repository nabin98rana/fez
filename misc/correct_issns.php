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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

@define(APP_LOGGING_ENABLED, false);

include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.record.php");

echo "Running ISSN clean-up utility ...\n";



$rawData = ISSNfix::getISBNandISSNlist(); /* All PIDs, with ISSN and ISBN data */
$misplacedISSNs = ISSNfix::extractISSNsFromISBNlist($rawData); /* Just those PIDs with what appears to be ISSNs in the ISBN field - including normalised data*/
$actualISSNs = ISSNfix::getActualISSNs($rawData); /* All PIDs with ISSNs, including normalised data */

$history = "Auto-moved suspected ISSN across from ISBN field";

echo "Entering update run ...\n";

foreach ($misplacedISSNs as $isbnKey => $isbnVal) {

	$record = new RecordGeneral($isbnKey);
	
	echo "\nProcessing record " . $isbnKey . " ... \n";
	echo "Suspected ISSN: " . $isbnVal['isbn_raw'] . "\n";
	echo "Current ISSN: " . $actualISSNs[$isbnKey]['issn_raw'] . "\n";
	
	if (ISSNfix::doesCandidateExistInProperISSNfield($isbnVal['isbn_clean'], $actualISSNs[$isbnKey]['issn_clean'])) {
		// A suspected ISSN was found in the ISBN field, but it appears to already be in the ISSN field too. We need to zero the ISBN field.
		//echo "ISSN repeated in ISBN and ISSN fields. Clearing from ISBN field... ";
		//$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("ISSN"), array(''), true, $history);
		//echo "done.\n";
		//exit;
	} else {
		if ($actualISSNs[$isbnKey]['issn_raw'] == '') {
			// We have no ISSN yet. Add our new value.
			echo "New ISSN, with no existing. Adding ... ";
			/*$history2 = "Auto-cleared old ISBN field";*/
			$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("ISSN"), array($isbnVal['isbn_raw']), true, $history);
			/*$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("ISBN"), array(), true, $history2);*/
			echo "done.\n";
		} else {
			// We have an EXISTING ISSN, not including our new value. Append!
			echo "New ISSN, with existing ISSN data. Appending ... ";
			$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("ISSN"), array($actualISSNs[$isbnKey]['issn_raw'] . "; " . $isbnVal['isbn_raw']), true, $history);
			echo "done.\n";
		}
	}
	
	echo "\n";
	
}

echo "Exiting ... \n";
exit;



class ISSNfix {
	
	function normaliseISBN($isbn)
	{
		$isbn = preg_replace("/[^0-9X]/", "", $isbn);
		return $isbn;
	}
	
	
	
	function extractISSNsFromISBNlist($data)
	{
		$issns = array();
		
		foreach ($data as $item) {
			$isbnClean = ISSNfix::normaliseISBN($item['isbn']);
			if (strlen($isbnClean) == 8) {
				$issns[$item['pid']]['isbn_raw'] = $item['isbn'];
				$issns[$item['pid']]['isbn_clean'] = $isbnClean;
			}
		}
		
		return $issns;
	}
	
	
	
	function getActualISSNs($data)
	{
		$issns = array();
		
		foreach ($data as $key => $val) {
			if ($data[$key]['issn'] != '') {
				$issnClean = ISSNfix::normaliseISBN($data[$key]['issn']);
				$issns[$data[$key]['pid']]['issn_raw'] = $data[$key]['issn'];
				$issns[$data[$key]['pid']]['issn_clean'] = $issnClean;
			}
		}
		
		return $issns;
	}
	
	
	
	function getISBNandISSNlist()
	{
		$db = DB_API::get();
		$log = FezLog::get();
		
		$sql = 	"
					SELECT
						rek_pid AS pid, rek_isbn AS isbn, rek_issn AS issn
					FROM
						fez_record_search_key
						
					LEFT JOIN 
						fez_record_search_key_isbn
					ON 
						rek_pid = rek_isbn_pid
						
					LEFT JOIN 
						fez_record_search_key_issn
					ON 
						rek_pid = rek_issn_pid
						
					LEFT JOIN
						fez_xsd_display
					ON
						rek_display_type = xdis_id
						
					/*
					WHERE
						rek_object_type = 3
						AND fez_record_search_key.rek_date >= '2003-01-01 00:00:00'
						AND fez_record_search_key.rek_date < '2009-01-01 00:00:00'
						AND xdis_title IN ('Book', 'Book Chapter', 'Conference Paper', 'Creative Work', 'Design', 'Generic Document', 'Image', 'Journal Article', 'Online Journal Article', 'RQF 2006 Book', 'RQF 2006 Book Chapter', 'RQF 2006 Conference Paper', 'RQF 2006 Journal Article', 'RQF 2007 Journal Article', 'Reference Entry')
						AND xdis_title IN ('Journal Article', 'Online Journal Article', 'RQF 2006 Journal Article', 'RQF 2007 Journal Article')
					;
					*/
				";
		
		try {
		        $result = $db->fetchAll($sql);
		} catch (Exception $ex) {
		        $log = FezLog::get();
		        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
		        return;
		}
		
		return $result;
	}
	
	
	
	function doesCandidateExistInProperISSNfield($issn, $suspect)
	{
		if ($suspect == '') {
			return false;
		}
		
		if (substr_count($suspect, $issn, 0) > 0) {
			return true;
		}
		
		return false;
	}

}

?>
