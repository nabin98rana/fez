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

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.record.php");

echo "Migrating internal notes to HERDC notes field ...\n";

// Get what we need
$query = "	
			SELECT
				rek_pid AS pid,
				rek_herdc_notes AS existing_herdc_notes,
				ain_detail AS admin_notes
			FROM
				fez_record_search_key
			
			LEFT JOIN fez_record_search_key_ismemberof
				ON rek_pid = rek_ismemberof_pid
			
			LEFT JOIN fez_internal_notes
				ON rek_pid = ain_pid
			
			WHERE
				rek_ismemberof = 'UQ:218311'
				AND ain_detail IS NOT NULL

				/*AND (rek_herdc_notes IS NULL OR rek_herdc_notes = '')*/
			
			ORDER
				BY rek_pid ASC
			;
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

echo "Total records found: " . count($result) . "\n";

// Main processing loop
foreach ($result as $record) {
	$pid = $record['pid'];
	$existing = $record['existing_herdc_notes'];
	$internal = $record['admin_notes'];

	echo $pid . " :: \n";
	
	$new = "";
	// Determine if we are writing clean, or appending
	if ($existing == '' || is_null($existing) || trim($existing) == '') {
		$new = $internal;
	} else {
		$new = $existing . "\n\n" . $internal;
	}

	$new = nl2br($new); // Onvert to HTML to preserve formatting for the rich text field.
	
	$record = new RecordGeneral($pid);
	$history = "updated using data in internal notes field";
	$record->addSearchKeyValueList(array("HERDC Notes"), array($new), true, $history);

	delete_internal_note($pid);
	
	echo "\n";
}

echo "\n\n";
echo "Complete! Exiting ... \n";
exit;



function delete_internal_note($pid)
{
	$db = DB_API::get();
	$log = FezLog::get();

	$query = "DELETE FROM fez_internal_notes WHERE ain_pid = '" . $pid . "';";

	echo $query . "\n\n";
	return;

	// We will log all these and play them manually, rather than this business.

	try {
		$result = $db->fetchAll($query);
	} catch (Exception $ex) {
		$log = FezLog::get();
		$log->err('Could not delete internal note. Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
		return;
	}

	return;
}
