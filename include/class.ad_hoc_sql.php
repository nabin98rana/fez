<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007, 2008 The University of Queensland,   |
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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au                      |
// |          Rhys Palmer <r.palmer@library.uq.edu.au                     |
// |          Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle Ad Hoc SQL Pid array lists
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 */

class Ad_Hoc_SQL {

	/**
	 * Gets the Ad Hoc SQL Pid array lists
	 *
	 * @return array An ad hoc SQL list
	 */
	public static function getList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$res = array();
		
		$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "ad_hoc_sql";
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));	
			return false;
		}
		return $res;
	}

	/**
	 * Returns the PIDs by executing the Adoc SQL query specified
	 *
	 * @param int $ahs_id The id of the ad hoc SQL query to execute
	 * @return array Resulting array of PID
	 */
	public static function getPIDS($ahs_id)
	{
		$log = FezLog::get();

		$res = array();
		$db = DB_API::get();
		
		$details = Ad_Hoc_SQL::getDetails($ahs_id);
		if(! $details) {
			$log->notice('No details found');
			return false;
		}
		$stmt = $details['ahs_query'];

		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
		}
		return $res;
	}

	/**
	 * Executes an Ad Hoc SQL show query and returns the resulting list
	 *
	 * @param int $ahs_id The id of the Ad Hoc SQL show query to execute
	 * @param int $page OPTIONAL The current page to return results from. Default is page 0.
	 * @param int $max OPTIONAL The maximum number of results to return. Default is 50.
	 * @return array The results of the ad hoc SQL show query
	 */
	public static function getResultSet($ahs_id, $page = 0, $max = 50)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (!is_numeric($ahs_id)) {
			$log->notice('ahs_id is not numeric');
			return false;
		}
		$details = Ad_Hoc_SQL::getDetails($ahs_id);
		$stmtCount = $details['ahs_query_count'];

		$res = array();
		try {
			$res = $db->fetchOne($stmtCount);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}

		if (is_numeric($res)) {
			$count = $res;
		} else {
			return false;
		}

		$stmtShow = $details['ahs_query_show'];
		if (!is_numeric($page) || !is_numeric($max)) {
			$page = 0;
			$max = 50;
		}
		$offset = $page * $max;
		$limit = $max;

		$stmtShow .= ' LIMIT '.$db->quote($limit, 'INTEGER').' OFFSET '.$db->quote($offset, 'INTEGER');

		$res = array();
		try {
			$res = $db->fetchAll($stmtShow);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}
		if (empty($res)) {
			return array();
		}

		$start = $page;
		$total_rows = $count;
		if (($start + $max) < $total_rows) {
			$total_rows_limit = $start + $max;
		} else {
			$total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
		$last_page = $total_pages - 1;
		return array(
            "list" => $res,
            "info" => array(
                "current_page"  => $page,
                "start_offset"  => $start,
                "end_offset"    => $start + ($total_rows_limit),
                "total_rows"    => $total_rows,
                "total_pages"   => $total_pages,
                "prev_page" 	=> ($page == 0) ? "-1" : ($page - 1),
                "next_page"     => ($page == $last_page) ? "-1" : ($page + 1),
                "last_page"     => $last_page
		)
		);
		return $res;
	}

	/**
	 *
	 * @param $ahs_id
	 * @return unknown_type
	 */
	public static function getDetails($ahs_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$res = array();

		if (!is_numeric($ahs_id)) {
			$log->notice('ahs_id is not numeric');
			return false;
		}

		$stmt = 'SELECT *
                 FROM ' . APP_TABLE_PREFIX . 'ad_hoc_sql
                 WHERE ahs_id = '.$db->quote($ahs_id, 'INTEGER');

		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	public static function insert()
	{
		$log = FezLog::get();

		$res = array();
		$db = DB_API::get();

		if ((is_numeric(stripos($_POST["ahs_query"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query"], "UPDATE")))) {
			$log->notice('Restricted statement detected in ahs_query: '.$_POST['ahs_query']);
			return false;
		}
		if ((is_numeric(stripos($_POST["ahs_query_count"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query_count"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query_count"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query_count"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query_count"], "UPDATE")))) {
			$log->notice('Restricted statement detected in ahs_query_count: '.$_POST['ahs_query_count']);
			return false;
		}
		if ((is_numeric(stripos($_POST["ahs_query_show"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query_show"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query_show"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query_show"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query_show"], "UPDATE")))) {
			$log->notice('Restricted statement detected in ahs_query_show: '.$_POST['ahs_query_show']);
			return false;
		}

		$data = array(
				'ahs_name' => $_POST['ahs_name'],
				'ahs_query' => $_POST['ahs_query'],
				'ahs_query_count' => $_POST['ahs_query_count'],
				'ahs_query_show' => $_POST['ahs_query_show']
		);

		try {
			$db->insert(APP_TABLE_PREFIX . 'ad_hoc_sql', $data);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
		return 1;
	}

	public static function getAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		

		$res = array();

		$stmt = "SELECT
                    ahs_id,
                    ahs_name
                 FROM
                    " . APP_TABLE_PREFIX . "ad_hoc_sql
                 ORDER BY
                    ahs_id ASC";

		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			
			return '';
		}

		
		return $res;
	}

	public static function update($ahs_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		

		$res = array();

		if ((is_numeric(stripos($_POST["ahs_query"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query"], "UPDATE")))) {
			$log->notice('Restricted statement detected in ahs_query: '.$_POST['ahs_query']);
			
			return false;
		}
		if ((is_numeric(stripos($_POST["ahs_query_count"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query_count"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query_count"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query_count"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query_count"], "UPDATE")))) {
			$log->notice('Restricted statement detected in ahs_query: '.$_POST['ahs_query_count']);
			
			return false;
		}
		if ((is_numeric(stripos($_POST["ahs_query_show"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query_show"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query_show"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query_show"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query_show"], "UPDATE")))) {
			$log->notice('Restricted statement detected in ahs_query: '.$_POST['ahs_query_show']);
			
			return false;
		}
		if(! is_numeric($ahs_id)) {
			
			return false;
		}	

		$data = array(
				'ahs_name' => $_POST['ahs_name'],
				'ahs_query' => $_POST['ahs_query'],
				'ahs_query_count' => $_POST['ahs_query_count'],
				'ahs_query_show' => $_POST['ahs_query_show']
		);

		try {
			$db->update(APP_TABLE_PREFIX . 'ad_hoc_sql', $data, 'ahs_id='.$db->quote($ahs_id, 'INTEGER'));
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			
			return -1;
		}
		
		return 1;
	}

	public static function remove()
	{
		$log = FezLog::get();

		$res = array();
		$db = DB_API::get();
				
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "ad_hoc_sql
                 WHERE
                    ahs_id IN (".Misc::arrayToSQLBindStr($_POST['items']).")";

		try {
			$db->query($stmt, $_POST['items']);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			
			return false;
		}
		
		return true;
	}
}

