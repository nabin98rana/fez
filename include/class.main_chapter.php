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
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT t1.aut_id, t1.aut_display_name, mc_status
				FROM " . APP_TABLE_PREFIX . "author AS t1
				LEFT JOIN 
				(SELECT mc_author_id, mc_status
				FROM " . APP_TABLE_PREFIX . "main_chapter AS t2
				WHERE mc_pid = " . $db->quote($pid) . ") as t3
				ON t1.aut_id = t3.mc_author_id
				WHERE t1.aut_id IN 
					(SELECT rek_author_id 
					FROM " . APP_TABLE_PREFIX . "record_search_key_author_id
					WHERE rek_author_id_pid = " . $db->quote($pid) . " 
					AND rek_author_id != 0
					)
				ORDER BY t1.aut_display_name";

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
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
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "main_chapter
                 WHERE
                    mc_pid = " . $db->quote($pid);
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}
		return true;
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
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (sizeof($mc_author_id) == 0) {
			return -1;
		}
		// Iterate through the checked authors, adding them to the main_chapter table
		foreach($mc_author_id as $authorID) {
			$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "main_chapter
					(mc_pid, mc_author_id, mc_status) VALUES 
					(" . $db->quote($pid) . ", " . $db->quote($authorID, 'INTEGER') . ", 1)";
			try {
				$db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
				return -1;
			}
		}
		return 1;
	}



	/**
	 * Retrieves a list of all records that have main chapters registered for authors that are
	 * no longer attached to the record.
	 *
	 * @access  public
	 * @return  array The associative array of PIDs and their titles.
	 */
	function getOrphanedMainChaptersAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT DISTINCT(mc_pid), rek_title " .
				"FROM " . APP_TABLE_PREFIX . "main_chapter AS t1, " . APP_TABLE_PREFIX . "record_search_key " .
				"WHERE mc_author_id NOT IN " .
				"(SELECT rek_author_id " .
				"FROM " . APP_TABLE_PREFIX . "record_search_key_author_id " .
				"WHERE rek_author_id_pid = t1.mc_pid " .
				") " .
				"AND mc_pid = rek_pid";

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return array();
		}
		return $res;
	}
}
