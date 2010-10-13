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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

include_once('../config.inc.php');
include_once(APP_INC_PATH . 'class.eventum.php');

/*ini_set("display_errors", 1);
error_reporting(1);
error_reporting(E_ALL ^ E_NOTICE);
*/
set_time_limit(0);

main();

function main()
{
	echo "Running Eventum job synch script ...\n";
	
	$jobs = Eventum::getAllClosedMyResearchJobs();
	
	foreach ($jobs as $job) {
		$parts = explode(" :: ", $job); // Extract the information from the subject line
		$type = $parts[1];
		$jobID = $parts[2];
		
		closeJob($type, $jobID);
	}
	
	echo "Synch complete.";
	
	exit;
}




function closeJob($type, $jobID)
{
	$log = FezLog::get();
	$db = DB_API::get();
	
	if ($type == 'Claimed Publication') {
		$query = "
				DELETE
				FROM
					fez_my_research_possible_flagged
				WHERE
					mrp_id = " . $db->quote($jobID) . ";
				";
	} else {
		$query = "
				DELETE
				FROM
					fez_my_research_claimed_flagged
				WHERE
					mrc_id = " . $db->quote($jobID) . ";
				";
	}
	
	// Perform the deletion
	try {
		$db->query($query);
	}
	catch(Exception $ex) {
		$log->err($ex);
		return false;
	}
	
	// TODO: Check to see if something was actually removed. If not, don't display this next line.
	echo "* Deleting job type '" . $type . "', ID: " . $jobID . "\n";
		
	return true;
}
