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

class matching
{
	/**
	 * Returns all matches (automatic, manual, and black-listed items).
	 */
	function getAllMatches($type)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if ($type == 'J') {
			$table = "journals";
			$prefix = "mtj";
		} elseif ($type == 'C') {
			$table = "conferences";
			$prefix = "mtc";
		}
		
		$stmt = "	
			SELECT
				" . $prefix . "_pid AS pid,
				" . $prefix . "_eraid AS eraid,
				" . $prefix . "_status AS status,
				rek_title AS record_title,
				title AS match_title
			FROM
				fez_matched_" . $table . "
			LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key ON " . APP_TABLE_PREFIX . "matched_" . $table . "." . $prefix . "_pid = " . APP_TABLE_PREFIX . "record_search_key.rek_pid
			LEFT JOIN __era_" . $table . " ON " . APP_TABLE_PREFIX . "matched_" . $table . "." . $prefix . "_eraid = __era_" . $table . ".eraid
			/*WHERE
				" . $prefix . "_status != 'B'*/
			ORDER BY
				" . $prefix . "_status
			;
		";
		
		try {
			$result = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $result;
	}
	
	
	
	function getMatchingExceptions($type)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if ($type == 'J') {
			$table = "journals";
			$prefix = "mtj";
		} elseif ($type == 'C') {
			$table = "conferences";
			$prefix = "mtc";
		}
		
		$stmt = "	
			SELECT
				" . $prefix . "_pid AS pid
			FROM
				fez_matched_" . $table . "
			WHERE
				" . $prefix . "_status != 'A'
			;
		";
		
		try {
			$result = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $result;
	}
	
	
	
	/**
	 * Save an existing mapping.
	 */
	function save()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$type = $_POST['type'];
		$pid = $_POST['pid'];
		$eraid = $_POST['eraid'];
		$status = $_POST['status'];
		
		if ($type == 'J') {
			$table = "journals";
			$prefix = "mtj";
		} elseif ($type == 'C') {
			$table = "conferences";
			$prefix = "mtc";
		}
		
		if ($status == 'B') {
			$eraid = 'N/A';
		}
		
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "matched_" . $table . "
                 SET
                    " . $prefix . "_eraid = " . $db->quote($eraid) . ",
                    " . $prefix . "_status = " . $db->quote($status) . "
                 WHERE
                    " . $prefix . "_pid = " . $db->quote($pid) . ";";
                    
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		
		header("Location: http://" . APP_HOSTNAME . "/manage/matching.php?type=" . $type);
		exit;
	}
	
	
	
	/**
	 * Add a brand new mapping.
	 */
	function add()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$type = $_POST['type'];
		$pid = $_POST['pid'];
		$eraid = $_POST['eraid'];
		$status = $_POST['status'];
		
		if ($type == 'J') {
			$table = "journals";
			$prefix = "mtj";
		} elseif ($type == 'C') {
			$table = "conferences";
			$prefix = "mtc";
		}
		
		if ($status == 'B') {
			$eraid = 'N/A';
		}
		
		$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "matched_" . $table . "
				(
				" . $prefix . "_pid,
				" . $prefix . "_eraid,
				" . $prefix . "_status
				) VALUES (
				" . $db->quote($pid) . ",
				" . $db->quote($eraid) . ",
				" . $db->quote($status) . "
				);";
		
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		
		header("Location: http://" . APP_HOSTNAME . "/manage/matching.php?type=" . $type);
		exit;
	}

}
