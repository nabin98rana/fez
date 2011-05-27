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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

set_time_limit(0);
ini_set("display_errors", 1);
error_reporting(1);

include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.my_research.php");

echo "Beginning author claim automated matching ... \n";

$claims = getClaims();

foreach ($claims as $claim) {
	$claimID = $claim['claim_id'];
	$pid = $claim['pid'];
	$authorUsername = $claim['author_username'];
	$authorDetails = Author::getDetailsByUsername($authorUsername);
	$authorID = $authorDetails['aut_id'];

	// Attempt the match	
	$record = new RecordObject($pid);
	$result = $record->matchAuthor($authorID, TRUE, TRUE, 1, FALSE);
	
	// Output results
	echo "Claim: " . $claimID . ", PID: " . $pid . ", Author ID: " . $authorID . " :: ";
	if ((is_array($result)) && $result[0] === true) {
		echo "Success! -- " . $result[1];
	} else {
		echo "MATCH FAILED -- " . $result[1];
	}
	echo "\n";
}

echo "\nProcessing complete. Exiting ...\n";
exit();



function getClaims()
{
	$db = DB_API::get();
	$log = FezLog::get();
	
	// Get all the claims that ALSO have corrections
	// Tweak this query as necessary, depending on what we're doing ...
	$query = "	
				SELECT mrp_id AS claim_id, mrp_pid AS pid, mrp_author_username AS author_username
				FROM fez_my_research_possible_flagged
				WHERE mrp_correction != ''
				AND mrp_type = 'C'
				ORDER BY mrp_timestamp ASC
				;
			";
	
	try {
	        $result = $db->fetchAll($query);
	} catch (Exception $ex) {
	        $log = FezLog::get();
	        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
	        return;
	}
	
	return $result;
}