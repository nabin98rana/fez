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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");


class Journal
{
	/**
	 * Method used to check whether a journal exists or not.
	 *
	 * @access  public
	 * @param   integer $aut_id The journal ID
	 * @return  boolean
	 */
	function exists($jnl_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($jnl_id) || !is_numeric($jnl_id)) {
			return false;
		}

		$stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_TABLE_PREFIX . "journal
                 WHERE
                    aut_id=".$db->quote($jnl_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}

		if ($res > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	
	
	/**
	 * Method used to get the list of journals available in the system.
	 *
	 * @access  public
	 * @return  array The list of journals
	 */
	function getList($current_row = 0, $max = 25, $order_by = 'jnl_journal_name', $filter="", $era_id = "")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$where_stmt = "";
		$extra_stmt = "";
		$extra_order_stmt = "";
		if (!empty($filter)) {
			$where_stmt .= " WHERE MATCH(jnl_journal_name) AGAINST (".$db->quote('*'.$filter.'*')." IN BOOLEAN MODE) ";
			$extra_stmt = " , MATCH(jnl_journal_name) AGAINST (".$db->quote($filter).") as Relevance ";
			$extra_order_stmt = " Relevance DESC, ";
		} elseif(!empty($era_id)) {
			$where_stmt .= " WHERE jnl_era_id = ".$db->quote($era_id);
		}
			
		$start = $current_row * $max;

		$stmt = "SELECT
					* ".$extra_stmt."
                 FROM
                    " . APP_TABLE_PREFIX . "journal
				".$where_stmt."
                 ORDER BY ".$extra_order_stmt."
                    ".$db->quote($order_by)."
				 LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt = "SELECT COUNT(*)
	                 FROM
	                    " . APP_TABLE_PREFIX . "journal
					".$where_stmt;
		} else {
			$stmt = 'SELECT FOUND_ROWS()';
		}

		try {
			$total_rows = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		if (($start + $max) < $total_rows) {
			$total_rows_limit = $start + $max;
		} else {
			$total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
		$last_page = $total_pages - 1;
		return array(
                "list" => $res,
                "list_info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $total_rows_limit,
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "prev_page" 	=> ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page
		)
		);
	}
	
	
	
	/**
	 * Method used to get the details for a given journal ID.
	 *
	 * @access  public
	 * @param   integer $jnl_id The journal ID
	 * @return  array The journal details
	 */
	function getDetails($jnl_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($jnl_id) || !is_numeric($jnl_id)) {
			return "";
		}

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "journal
                 WHERE
                    jnl_journal_id = ".$db->quote($jnl_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		$res["jnl_issns"] = Journal::getISSNs($res["jnl_journal_id"]);
		return $res;
	}
	
	
	
	/**
	 * Method used to get all ISSNs for a given journal ID.
	 *
	 * @access  public
	 * @param   integer $jnl_id The journal ID
	 * @return  array The journal's ISSNs
	 */
	function getISSNs($jnl_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($jnl_id) || !is_numeric($jnl_id)) {
			return "";
		}

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "journal_issns
                 WHERE
                    jnl_journal_id = ".$db->quote($jnl_id, 'INTEGER')."
                 ORDER BY
                 	jnl_issn_order DESC";
		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}	
	
	
	
	/*
	TODO:
		
	update
	remove
	insert	
	
	*/
	
}
