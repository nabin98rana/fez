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

define(APP_LOGGING_ENABLED, false);

include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.xsd_display.php");

/*

# Prior to running this, do a complete re-index of all Conference Items. This is to
  ensure that all Sub-Type search key values are in MySQL.

# Update the production CP content model to have a sub-type field + S.K.

# Update resolveSubType() array to reflect the drop-down options permitted for this 
  new field.
  
*/

echo "Converting Conference Items to Conference Papers ...\n";

$ci_xdis_id = XSD_Display::getXDIS_IDByTitleVersion('Conference Item', 'MODS 1.0');
$cp_xdis_id = XSD_Display::getXDIS_IDByTitleVersion('Conference Paper', 'MODS 1.0');

// Get all Conference Item PIDs
$query = "	
			SELECT rek_pid AS pid
			FROM fez_record_search_key
			WHERE rek_display_type = '" . $ci_xdis_id . "'
			/*AND rek_pid = 'UQ:239374'*/
			/*LIMIT 1*/
		";

$db = DB_API::get();
$log = FezLog::get();

try {
        $result = $db->fetchAll($query);
} catch (Exception $ex) {
        $log = FezLog::get();
        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
        return;
}

echo "Total Conference Items found: " . count($result) . "\n";
$pids = array();

foreach ($result as $item) {
	$pids[] = $item['pid'];
}

// Main processing loop
foreach ($pids as $pid) {
	
	echo "\n";
	
	$subType = getSubType($pid);
	$newSubType = resolveSubType($subType);
	
	$record = new RecordObject($pid); // Load the record
	$record->updateAdminDatastream($cp_xdis_id); // Convert it to new content type
	
	// If we had a subtype, create it in the new content model
	if ($subType != "") {
		$history = "automagically mapped from original Conference Paper sub-type value '" . $subType . "'";
		$record->addSearchKeyValueList(array("Genre Type"), array($newSubType), true, $history);
	}
	
	$historyDetail = "Automagically changed display type from Conference Item to Conference Paper";
	History::addHistory($pid, null, "", "", true, $historyDetail);
}

echo "\n\n";
echo 'Conference Items processing complete! Exiting ... ';
exit;




function getSubType($pid)
{
	$db = DB_API::get();
	$log = FezLog::get();

	$query = "
				SELECT rek_genre_type
				FROM fez_record_search_key
				WHERE rek_pid = '" . $pid . "'
			";
	try {
		$result = $db->fetchRow($query);
	} catch (Exception $ex) {
		$log = FezLog::get();
		$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
		return;
	}
	
	return $result['rek_genre_type'];
}




function resolveSubType($subType)
{
	$mappings = array(
					'Poster' => 'Poster',
					'Presentation Only' => 'Oral Presentation',
					'Published Abstract' => 'Published Abstract',
					'Z - Other' => 'Other'
					);
			
	$newSubType = $mappings[$subType];
					
	return $newSubType;
}