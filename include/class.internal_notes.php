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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle the logging of internal administrator notes.
 *
 * @version 1.0
 * @author Lachlan Kuhn <l.kuhn@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.auth.php");

class InternalNotes
{
	/**
	 * Read and return the note for the requested PID.
	 */
	function readNote($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
					ain_detail
				 FROM
					" . APP_TABLE_PREFIX . "internal_notes
				 WHERE
						ain_pid = " . $db->quote($pid) . ";";
		try {
			$res = $db->fetchOne($stmt);			
		} catch(Exception $ex) {
			$log->err($ex);
			return;
		}
		
		return $res;
	}
	
	
	
	/**
	 * Clear the note entirely (sufficient to handle empty/deleted note), and possibly write 
	 * in the new note, if one has been supplied.
	 */
	function recordNote($pid, $note)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		InternalNotes::clearNote($pid); // Murder, Death, Kill.
		
		if ($note !== '') {

			$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "internal_notes
					(
						ain_pid,
						ain_detail
					) VALUES (
						" . $db->quote($pid) . ",
						" . $db->quote($note) . "
					);";
			try {
				$db->exec($stmt);
			} catch(Exception $ex) {
				$log->err($ex);
				return false;
			}
		}
		
		return;
	}



	/**
	 * Completely remove an internal note.
	 */
	function clearNote($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
					" . APP_TABLE_PREFIX . "internal_notes
				 WHERE
					ain_pid = " . $db->quote($pid) . ";";
		
		try {
			$db->query($stmt);
		} catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return;
	}
	
	
	
	/**
	 * Move a note from one PID to another.
	 */
	function moveNote($oldPID, $newPID)
	{
		// TODO: This function has not yet been implemented.
		/* Description of functionality required: 
			1a. Append the existing note attached to oldPID to the note of newPID, OR
			1b. Write oldPID's note to newPID's note if no newPID yet. THEN
			1. Delete $oldPID's note.
		*/
	}
	
}
