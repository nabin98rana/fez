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

$rawData = ISSNfix::getISBNandISSNlist();
$misplacedISSNs = ISSNfix::extractISSNsFromISBNlist($rawData);

print_r($misplacedISSNs);


exit;


$actualISSNsISSNfix::getActualISSNs($rawData);
$pidsWithBadISSNs = ISSNfix::extractISSNsFromISBNlist($rawData);

echo "LOL!";

exit;


foreach ($misplacedISSN as $issn) {
	
	if (ISSNfix::isISSNinProperISSNfield($issn)) {
		echo "* The ISSN " . $issn . " is already in the ISSN field ... skipping.\n";
	} else {
		echo "ISSN not already on file. ADD! *** IMPLEMENT ME ***"; // LKDB
		// TODO - actually write a function that will do this.
	}
	
	
}

echo "Exiting ... \n";
exit;



























/***************************************************************************************************************
 ***************************************************************************************************************
 ***************************************************************************************************************/

/* Build a list of PIDs */
$pids = array();
foreach ($result as $item) {
	$pids[] = $item['rek_pid'];
}

$search_keys = array("Language"); // The search key we are updating

foreach ($pids as $pid) {
	echo $pid . "\n";
	$record = new RecordGeneral($pid);
	$languages = getLanguagesForPID($pid);
	$mapping = $langMapping[$languages];
	$history = "was updated based on automagic language mapping";
	
	if (count($mapping) == 0) {
		echo "ERROR - no mappings found for " . $languages . "\n";
	} else {
		$mapCount = 1;
		foreach ($mapping as $map) {
			if (count($mapping) > 1) {
				// Multiple languages
				if ($mapCount == 1) {
					$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("Language"), array($map), true, $history);
				} else {
					$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("Language"), array($map), false, $history);
				}
			} else {
				// Just one language
				$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("Language"), array($map), true, $history);
			}
			$mapCount++;
		}
	}
	echo "\n";
}

/***************************************************************************************************************
 ***************************************************************************************************************
 ***************************************************************************************************************/




















class ISSNfix {
	
	function getMisplacedISSNs($data)
	{
		$suspectedISSN = array();
		foreach ($data as $possibleISSN) {
			if ($possibleISSN['issn'] != '') {
				$suspectedISSN[] = $possibleISSN['issn'];
			}
		}
		
		print_r($suspectedISSN);
		
		foreach($suspectedISSN as $issn) {
			echo "~";
			// LKDB -- store the normalised ISSN in parallel
		}
		
		echo "DOWN TO HERE!";
		exit;
		return "*";
	}
	
	
	
	function normaliseISBN($isbn)
	{
		//$isbn = preg_replace("/[^0-9\-X]/", "", $isbn);
		$isbn = preg_replace("/[^0-9X]/", "", $isbn);
		return $isbn;
	}
	
	
	
	function extractISSNsFromISBNlist($data)
	{
		$issns = array();
		
		foreach ($data as $item) {
			$isbnClean = ISSNfix::normaliseISBN($item['isbn']);
			if (strlen($isbnClean) == 8) {
				$issns[$item['pid']] = $item['isbn'];
			}
		}
		
		print_r($issns);
		exit;
		
		return $issns;
	}
	
	
	
	function getISBNandISSNlist()
	{
		$db = DB_API::get();
		$log = FezLog::get();
		
		$sql = 	"
					SELECT
						rek_pid, rek_isbn, rek_issn
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
	
}

?>
