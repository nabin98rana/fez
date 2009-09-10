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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH . "class.date.php");

class BackgroundProcessList
{

	var $auto_delete_names = "'Index Auth','Fulltext Index','Fulltext Index Update'";

	function getList($usr_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$isAdministrator = Auth::isAdministrator();
		$extra_sql = "";
		if ($isAdministrator) {
			$extra_sql = " OR bgp_state = 1";
		}
		$utc_date = Date_API::getSimpleDateUTC();
		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "SELECT bgp_id, bgp_usr_id, bgp_status_message, bgp_progress, bgp_state, bgp_heartbeat,bgp_name,bgp_started," .
                "if (bgp_heartbeat < DATE_SUB('".$utc_date."',INTERVAL 1 DAY), 1, 0) as is_old
            FROM ".$dbtp."background_process
            WHERE bgp_usr_id=".$db->quote($usr_id)." ".$extra_sql."
            ORDER BY bgp_started";
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}

		foreach ($res as $key => $row) {
			$tz = Date_API::getPreferredTimezone($res[$key]["bgp_usr_id"]);
			$res[$key]["bgp_started"] = Date_API::getFormattedDate($res[$key]["bgp_started"], $tz);
			$res[$key]["bgp_heartbeat"] = Date_API::getFormattedDate($res[$key]["bgp_heartbeat"], $tz);
		}
		return $res;
	}

	function getDetails($id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$utc_date = Date_API::getSimpleDateUTC();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "SELECT *,if (bgp_heartbeat < DATE_SUB(".$utc_date.",INTERVAL 1 DAY), 1, 0) as is_old
            FROM ".$dbtp."background_process
            WHERE bgp_id=".$db->quote($id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	function getStates()
	{
		$bgp = new BackgroundProcess;
		return $bgp->states;
	}

	function delete($items)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		foreach ($items as $item) {
			BackgroundProcessList::deleteLog($item);
		}

		// get the filenames and delete them
		$stmt = "SELECT bgp_filename FROM ".$dbtp."background_process WHERE bgp_id IN (".Misc::arrayToSQLBindStr($items).") ";

		try {
			$res = $db->fetchCol($stmt, $items);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}

		foreach ($res as $filename) {
			if (!empty($filename)) {
				@unlink($filename);
			}
		}

		$stmt = "DELETE FROM ".$dbtp."background_process WHERE bgp_id IN (".Misc::arrayToSQLBindStr($items).") ";
		try {
			$db->query($stmt, $items);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}

	function autoDeleteOld($usr_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$auto_delete_names = $this->auto_delete_names;
		$dbtp =  APP_TABLE_PREFIX;
		$utc_date = Date_API::getSimpleDateUTC();
		$stmt = "DELETE FROM ".$dbtp."background_process
                WHERE 
                    bgp_name IN (".$auto_delete_names.") " .
                    "AND (((bgp_state = 0 OR bgp_state IS NULL) AND bgp_started < DATE_SUB('".$utc_date."',INTERVAL 1 HOUR) )  " .
                    "OR ((bgp_state = 2) AND (bgp_heartbeat IS NULL OR bgp_heartbeat < DATE_SUB('".$utc_date."',INTERVAL 1 HOUR) ) ) )";
		try {
			$res = $db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
/*
		if (!empty($res)) {
			$this->delete($res);
		}
		$stmt = "SELECT bgp_id FROM ".$dbtp."background_process
                WHERE 
                    bgp_usr_id=".$db->quote($usr_id, 'INTEGER')."  " .
                    "AND bgp_name IN (".$auto_delete_names.") " .
                    "AND (bgp_state = '0' OR bgp_state = '2') " .
                    "ORDER BY bgp_started ASC";		
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		if (count($res) > 3) {
			array_pop($res);
			array_pop($res);
			array_pop($res);
			$this->delete($res);
		} */
	}

	function getLog($bgp_id)
	{
		return file_get_contents(APP_TEMP_DIR."fezbgp/fezbgp_".$bgp_id.".log");
	}

	function deleteLog($bgp_id)
	{
		@unlink(APP_TEMP_DIR."fezbgp/fezbgp_".$bgp_id.".log");
	}

}
