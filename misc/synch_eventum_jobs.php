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
include_once(APP_INC_PATH . 'class.my_research.php');

set_time_limit(0);

main();

function main()
{
	echo "Running Eventum job synch script ...\n";
	
	$jobs = Eventum::getAllClosedMyResearchJobs();
	
	// Step through each of the Closed (but non-synched) Eventum jobs
	foreach ($jobs as $job) {
		
		$parts = explode(" :: ", $job['ticket_subject']); // Extract the information from the subject line
		$type = $parts[1];
		$jobID = $parts[2];
		
		MyResearch::closeJob($type, $jobID); // Kill the job in Fez
		Eventum::closeAndSynchJob($job['ticket_id']); // Mark the job 'Closed and Synched' in Eventum
	}
	
	echo "Synch complete.";
	
	exit;
}
