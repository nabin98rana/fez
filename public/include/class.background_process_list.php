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
include_once(APP_INC_PATH.'class.workflow_status.php');
include_once(APP_INC_PATH.'class.background_process_pids.php');
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH.'class.papertrail.php');

class BackgroundProcessList
{

	var $auto_delete_names = "'Index Auth','Fulltext Index','Fulltext Index Update','Run Webstats', 'Batch Add', 'Batch Import', 'WoK Service', 'Links AMR Update', 'Index Object'" ;

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
		$stmt = "SELECT bgp_id, bgp_usr_id, bgp_status_message, bgp_progress, bgp_state, bgp_heartbeat, bgp_name, bgp_started, bgp_filename,";
		$stmt .= " usr.usr_full_name, usr.usr_username,";
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
			$stmt .= " CASE WHEN  (bgp_heartbeat <  (TIMESTAMP '".$utc_date."' - INTERVAL '1 days')) THEN 1 ELSE 0 END AS is_old ";
		} else {
			$stmt .= " if (bgp_heartbeat < DATE_SUB('".$utc_date."',INTERVAL 1 DAY), 1, 0) as is_old ";
		}

			$stmt .= "
            FROM ".$dbtp."background_process
            LEFT JOIN ". $dbtp ."user AS usr ON bgp_usr_id = usr.usr_id
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

  //Used in testing
  public static function isFinishedProcessing()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $utc_date = Date_API::getSimpleDateUTC();
    $dbtp =  APP_TABLE_PREFIX;
    $stmt = "SELECT bgp_id ";

    $stmt .= "
            FROM ".$dbtp."background_process
            WHERE bgp_state <> 2 ";
    if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
      $stmt .= " AND (bgp_heartbeat >  (TIMESTAMP '".$utc_date."' - INTERVAL '10 minutes'))  ";
    } else {
      $stmt .= " AND (bgp_heartbeat > DATE_SUB('".$utc_date."',INTERVAL 10 MINUTE)) ";
    }
    $stmt .= "
            ORDER BY bgp_started";
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    $response = true;
    if (is_numeric($res)) {
      $response = false;
    }
    return $response;
  }


	function getDetails($id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$utc_date = Date_API::getSimpleDateUTC();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "SELECT *, ";
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
			$stmt .= " CASE WHEN  (bgp_heartbeat <  (TIMESTAMP '".$utc_date."' - INTERVAL '1 days')) THEN 1 ELSE 0 END AS is_old ";
		} else {
			$stmt .= "if (bgp_heartbeat < DATE_SUB('".$utc_date."',INTERVAL 1 DAY), 1, 0) as is_old ";
		}
		$stmt .= "
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
                    bgp_name IN (".$auto_delete_names.") ";

		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
			$stmt .=
	             "AND (((bgp_state = 0 OR bgp_state IS NULL) AND bgp_started < (TIMESTAMP '".$utc_date."' - INTERVAL '1 hours') )  " .
	             "OR ((bgp_state = 2) AND (bgp_heartbeat IS NULL OR bgp_heartbeat < (TIMESTAMP '".$utc_date."' - INTERVAL '1 hours') ) ) )";
		} else {
			$stmt .=
	             "AND (((bgp_state = 0 OR bgp_state IS NULL) AND bgp_started < DATE_SUB('".$utc_date."',INTERVAL 1 HOUR) )  " .
	             "OR ((bgp_state = 2) AND (bgp_heartbeat IS NULL OR bgp_heartbeat < DATE_SUB('".$utc_date."',INTERVAL 1 HOUR) ) ) )";
		}
		try {
			$res = $db->query($stmt);
      BackgroundProcessPids::cleanDisconnectedPids();
      WorkflowStatusStatic::cleanOld();
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
		if (APP_PAPERTRAIL_TOKEN) {
			$bgp = new BackgroundProcess($bgp_id);
			$details = $bgp->getDetails();

			if (! $details['bgp_host']) {
				return false;
			}

			$papertrail = new Papertrail();
			$message = json_decode(
				$papertrail->search(
					'system_id=' . $details['bgp_host'] . '&min_time=' .
					strtotime($details['bgp_started'])),
				true
			);
			return array_reduce($message['events'], function ($pre, $item) {
				return $pre . "\n" . $item['message'];
			});

		} else {
			$file = APP_TEMP_DIR . "fezbgp/fezbgp_" . $bgp_id . ".log";
			if (file_exists($file)) {
				return file_get_contents($file);
			}
		}
		return false;
	}

	function deleteLog($bgp_id)
	{
		@unlink(APP_TEMP_DIR."fezbgp/fezbgp_".$bgp_id.".log");
	}

}
