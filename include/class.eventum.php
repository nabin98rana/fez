<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 - 2008 The University of Queensland,              |
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . "class.mail.php");

define('STATUS_CLOSED_AND_SYNCHED', 21);

class Eventum
{
	/** 
	 * Invoking this function will cause a job to be logged in Eventum.
	 */
	function lodgeJob($subject, $body, $from)
	{
		// Bail out if we have Eventum emails turned off.
		if (APP_EVENTUM_SEND_EMAILS != 'ON') {
			return;
		}
		
		// Otherwise, assemble a message for sending.
		$to      = APP_EVENTUM_NEW_JOB_EMAIL_ADDRESS;
		$headers = 'From: ' . $from . "\r\n" .
		'Reply-To: ' . $from . "\r\n" .
		'X-Mailer: PHP/' . phpversion();

		// Send the email. We could probably use the Mail_API methods, but this will do for now.		
		if (!mail($to, $subject, $body, $headers)) {
			die("There was a problem submitting this job to Eventum. Please contact the " . APP_NAME . " Manager.");
		}
		
		return;
	}
	
	
	
	/** 
	 * Returns the subject lines of all closed My Research jobs in Eventum.
	 */
	function getAllClosedMyResearchJobs()
	{
		Eventum::dbSetup();
		
		global $db;
		
		$query = "
					SELECT
						iss_id AS ticket_id,
						iss_summary AS ticket_subject
					FROM
						eventum_issue,
						eventum_status
					WHERE
						eventum_issue.iss_sta_id = eventum_status.sta_id
						AND sta_title = 'Closed'
						AND iss_summary LIKE 'My Research :: %'
					ORDER BY
						iss_id DESC;
				";
		
		$result = mysql_query($query, $db);

		$return = array();		
		while ($row = mysql_fetch_assoc($result)) {
			$return[] = array('ticket_id' => $row['ticket_id'], 'ticket_subject' => $row['ticket_subject']);
		}
		
		return $return;
	}
	
	
	
	/**
	 * Set up the Eventum database connection.
	 */
	function dbSetup() {
	
		global $db;
		echo "Connecting to database ... ";
		$conn = @mysql_connect(APP_EVENTUM_DATABASE_HOST, APP_EVENTUM_DATABASE_USER, APP_EVENTUM_DATABASE_PASS);

		if (!$conn) {
			die("Error: Could not connect to Eventum database. Aborting.\n");
		}

		$db_selected = @mysql_select_db(APP_EVENTUM_DATABASE_NAME, $conn);
		if (!$db_selected) {
			die ("Can't use " . APP_EVENTUM_DATABASE_NAME . " : " . mysql_error());
		}
		
		echo "done.\n";
		$db = $conn;
		
		return;
	}
	
	
	/**
	 * Mark a given Eventum job 'Closed and Synched'.
	 */
	function closeAndSynchJob($eventumID)
	{
		global $db;
		
		$query = "
					UPDATE eventum_issue
					SET iss_sta_id = " . STATUS_CLOSED_AND_SYNCHED . "
					WHERE iss_id = '" . mysql_real_escape_string($eventumID) . "';
				";
		
		$result = mysql_query($query, $db);
		if (!$result) {
			echo "There was a problem removing Eventum Job " . $eventumID . " : " . mysql_error() . "\n";
		}
		
		return;
	}

}
