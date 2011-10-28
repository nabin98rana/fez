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
	 * @param   integer $jnl_id The journal ID
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
                    jnl_id = ".$db->quote($jnl_id, 'INTEGER');
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
	function getList($current_row = 0, $max = 25, $order_by = 'jnl_journal_name', $filter="")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$where_stmt = "";
		if (!empty($filter)) {
			$where_stmt .= " WHERE jnl_journal_name LIKE '%" . $filter . "%' ";
		}
			
		$start = $current_row * $max;

		$stmt = "SELECT
					*
                 FROM
                    " . APP_TABLE_PREFIX . "journal
				" . $where_stmt . "
                 ORDER BY
                 	jnl_era_id ASC, jnl_era_year ASC, jnl_journal_name ASC
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
                    jnl_id = ".$db->quote($jnl_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		$res["jnl_issns"] = Journal::getISSNs($jnl_id);
		
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
                    jnl_id = ".$db->quote($jnl_id, 'INTEGER')."
                 ORDER BY
                 	jnl_issn_order ASC";
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}	
	
	
	
	/**
	 * Method used to update the details of the journal.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 otherwise
	 */
	function update()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (Validation::isWhitespace($_POST["name"])) {
			return -2;
		}
		
		/* Update the base journal item */
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "journal
                 SET
                    jnl_journal_name = " . $db->quote($_POST["name"]) . ",
                    jnl_era_id = " . $db->quote($_POST["era_id"]) . ",
                    jnl_updated_date = " . $db->quote(Date_API::getCurrentDateGMT()) ."
                 WHERE
                    jnl_id = " . $db->quote($_POST["id"], 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		
		/* Remove any existing ISSNs */
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "journal_issns
                 WHERE
                    jnl_id = " . $db->quote($_POST["id"], 'INTEGER');
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		$issnCounter = 0;
		foreach ($_POST["issn"] as $issn) {
			if ($issnCounter == 0) {
				$stmt = "INSERT INTO
							" . APP_TABLE_PREFIX . "journal_issns
							(
								jnl_id,
								jnl_issn,
								jnl_issn_order
							) VALUES ";
			}
			
			if (trim($issn) != '') {
				$issnCounter++;
				if ($issnCounter > 1) {
					$stmt .= ",";
				}
				$stmt .= "(
							" . $db->quote($_POST["id"]) . ",
							" . $db->quote($issn) . ",
							" . $issnCounter . "
						  )";
			}
		}
		
		if ($issnCounter > 0) {
			/* Actually execute the SQL we've assembled if we have something to insert */
			$stmt .= ";";
			
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return false;
			}
		}
		
		return 1;
	}
	
	
	
	/**
	 * Method used to add a new journal to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 or -2 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (Validation::isWhitespace($_POST["name"])) {
			return -2;
		}
		
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "journal
                  (
                    jnl_journal_name,
					jnl_era_id,
					jnl_created_date,
					jnl_updated_date
				  ) VALUES (
                    " . $db->quote($_POST["name"]) . ",
					" . $db->quote($_POST["era_id"]) . ",					
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . "
                   )";

		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
	
	
	
	/**
	 * Method used to remove a given set of journals from the system.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		/* Delete the base journal item */
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "journal
                 WHERE
                    jnl_id IN (" . Misc::arrayToSQLBindStr($_POST["items"]) . ")";
		try {
			$db->query($stmt, $_POST['items']);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		/* Delete any attached ISSNs */
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "journal_issns
                 WHERE
                    jnl_id IN (" . Misc::arrayToSQLBindStr($_POST["items"]) . ")";
		try {
			$db->query($stmt, $_POST['items']);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return true;
	}
	
	
	
	/**
	 * Get the complete list of journals.
	 */
	function getJournals()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
			SELECT
			    jnl_id as matching_id,
			    jnl_era_year,
				jnl_era_id AS eraid,
				jnl_rank AS rank,
				jnl_journal_name AS title
			FROM
				" . APP_TABLE_PREFIX . "journal
			ORDER BY
				jnl_era_id ASC, jnl_era_year ASC, jnl_journal_name ASC;
		";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}

}
