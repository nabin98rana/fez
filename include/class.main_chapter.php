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
//
//

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");

class MainChapter
{
	/**
	 * Retrieves a list of all authors attached to the current document, along with a column
	 * indicating whether or not the author is currently flagged as registered for main chapter.
	 *
	 * @access  public
	 * @param   integer $pid The PID of the document
	 * @return  array The list of authors
	 */
	function getListAll($pid)
	{
		$stmt = "SELECT t1.aut_id, t1.aut_display_name, mc_status
				FROM " . APP_TABLE_PREFIX . "author AS t1
				LEFT JOIN 
				(SELECT mc_author_id, mc_status
				FROM " . APP_TABLE_PREFIX . "main_chapter AS t2
				WHERE mc_pid = '" . $pid . "') as t3
				ON t1.aut_id = t3.mc_author_id
				WHERE t1.aut_id IN 
					(SELECT rek_author_id 
					FROM " . APP_TABLE_PREFIX . "record_search_key_author_id
					WHERE rek_author_id_pid = '" . $pid . "' 
					AND rek_author_id != 0
					)
				ORDER BY t1.aut_display_name";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        return $res;
	}



	/**
	 * Destroys all existing main chapter registrations for a given PID. Typically invoked immediately 
	 * before writing out new values.
	 *
	 * @access  public
	 * @param   integer $pid The PID of the document
	 * @return  boolean
	 */
	function nukeExisting($pid)
	{
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "main_chapter
                 WHERE
                    mc_pid = '" . $pid . "'";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
	}
	


	/**
	 * Records Main chapter registration for all authors that were checked in the form when
	 * the save button was clicked.
	 *
	 * @access  public
	 * @param   integer $pid The PID of the document, integer $mc_author_id The Author ID to record
	 * @return  boolean
	 */
	function saveMainChapters($pid, $mc_author_id)
	{
		if (sizeof($mc_author_id) == 0) {
			return -1;
		}
		// Iterate through the checked authors, adding them to the main_chapter table
		foreach($mc_author_id as $authorID) {
			$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "main_chapter 
					(mc_pid, mc_author_id, mc_status) VALUES 
					('" . $pid . "', " . $authorID . ", 1)";
			$res = $GLOBALS["db_api"]->dbh->query($stmt);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				return -1;
			}
		}
		return 1;
	}

}

?>
