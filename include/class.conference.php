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


class Conference
{
	/**
	 * Method used to check whether a conference exists or not.
	 *
	 * @access  public
	 * @param   integer $cnf_id The conference ID
	 * @return  boolean
	 */
	function exists($cnf_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($cnf_id) || !is_numeric($cnf_id)) {
			return false;
		}

		$stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_TABLE_PREFIX . "conference
                 WHERE
                    cnf_id = ".$db->quote($cnf_id, 'INTEGER');
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
	 * Method used to get the list of conferences available in the system.
	 *
	 * @access  public
	 * @return  array The list of conferences
	 */
	function getList($current_row = 0, $max = 25, $order_by = ' cnf_conference_name', $filter="")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$where_stmt = "";
		if (!empty($filter)) {
			$where_stmt .= " WHERE cnf_conference_name LIKE '%" . $filter . "%' ";
		}
			
		$start = $current_row * $max;

		$stmt = "SELECT
					SQL_CALC_FOUND_ROWS *
                 FROM
                    " . APP_TABLE_PREFIX . "conference
				" . $where_stmt . "
                 ORDER BY
                 	cnf_conference_name ASC
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
	                    " . APP_TABLE_PREFIX . "conference
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
	 * Method used to get the details for a given conference ID.
	 *
	 * @access  public
	 * @param   integer $cnf_id The conference ID
	 * @return  array The conference details
	 */
	function getDetails($cnf_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($cnf_id) || !is_numeric($cnf_id)) {
			return "";
		}

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "conference
                 WHERE
                    cnf_id = ".$db->quote($cnf_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}
	
	
	
	/**
	 * Method used to update the details of the conference.
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
		
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "conference
                 SET
                    cnf_conference_name = " . $db->quote($_POST["name"]) . ",
                    cnf_era_id = " . $db->quote($_POST["era_id"]) . ",
                    cnf_acronym = " . $db->quote($_POST["acronym"]) . ",
                    cnf_rank = " . $db->quote($_POST["rank"]) . ",
                    cnf_updated_date = " . $db->quote(Date_API::getCurrentDateGMT()) ."
                 WHERE
                    cnf_id = " . $db->quote($_POST["id"], 'INTEGER');
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
	 * Method used to add a new conference to the system.
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
                    " . APP_TABLE_PREFIX . "conference
                  (
                    cnf_conference_name,
					cnf_era_id,
					cnf_acronym,
					cnf_rank,
					cnf_created_date,
					cnf_updated_date
				  ) VALUES (
                    " . $db->quote($_POST["name"]) . ",
					" . $db->quote($_POST["era_id"]) . ",
					" . $db->quote($_POST["acronym"]) . ",
					" . $db->quote($_POST["rank"]) . ",
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
	 * Method used to remove a given set of conferences from the system.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "conference
                 WHERE
                    cnf_id IN (" . Misc::arrayToSQLBindStr($_POST["items"]) . ")";
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
	 * Get the complete list of conferences.
	 */
	function getConferences()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
			SELECT
			    cnf_id as matching_id,
			    cnf_era_year AS year,
				cnf_era_id AS eraid,
				cnf_rank AS rank,
				cnf_conference_name AS title
			FROM
				" . APP_TABLE_PREFIX . "conference
			ORDER BY
				cnf_conference_name ASC;
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
class ConferenceId
{
    /**
     * Method used to check whether a conference exists or not.
     *
     * @access  public
     * @param   integer $cnf_id The conference ID
     * @return  boolean
     */
    function exists($cnf_id)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if(empty($cnf_id) || !is_numeric($cnf_id)) {
            return false;
        }

        $stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_TABLE_PREFIX . "conference_id
                 WHERE
                    cfi_id = ".$db->quote($cnf_id, 'INTEGER');
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
     * Method used to get the list of conferences available in the system.
     *
     * @access  public
     * @return  array The list of conferences
     */
    function getList($current_row = 0, $max = 25, $order_by = ' cfi_conference_name', $filter="")
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $where_stmt = "";
        if (!empty($filter)) {
            $where_stmt .= " WHERE cfi_conference_name LIKE '%" . $filter . "%' ";
        }

        $start = $current_row * $max;

        $stmt = "SELECT
					SQL_CALC_FOUND_ROWS *
                 FROM
                    " . APP_TABLE_PREFIX . "conference_id
				" . $where_stmt . "
                 ORDER BY
                 	cfi_conference_name ASC
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
	                    " . APP_TABLE_PREFIX . "conference_id
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
     * Method used to get the details for a given conference ID.
     *
     * @access  public
     * @param   integer $cnf_id The conference ID
     * @return  array The conference details
     */
    function getDetails($cnf_id)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if(empty($cnf_id) || !is_numeric($cnf_id)) {
            return "";
        }

        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "conference_id
                 WHERE
                    cfi_id = ".$db->quote($cnf_id, 'INTEGER');
        try {
            $res = $db->fetchRow($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        return $res;
    }



    /**
     * Method used to update the details of the conference.
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
        $isUser = Auth::getUsername();
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "conference_id
                 SET
                    cfi_conference_name = " . $db->quote($_POST["name"]) . ",
                    cfi_updated_date = " . $db->quote(Date_API::getCurrentDateGMT()) .",
                    cfi_details_by = " . $db->quote($isUser) ."
                 WHERE
                    cfi_id = " . $db->quote($_POST["id"], 'INTEGER');
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
     * Method used to add a new conference to the system.
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
        $isUser = Auth::getUsername();

        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "conference_id
                  (
                    cfi_conference_name,
					cfi_created_date,
					cfi_updated_date,
					cfi_details_by
				  ) VALUES (
                    " . $db->quote($_POST["name"]) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    " . $db->quote($isUser) . "
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
     * Method used to remove a given set of conferences from the system.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "conference_id
                 WHERE
                    cfi_id IN (" . Misc::arrayToSQLBindStr($_POST["items"]) . ")";
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
     * Get the complete list of conferences.
     */
    function getConferences()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "
			SELECT
			    cfi_id as matching_id,
				cfi_conference_name AS title
			FROM
				" . APP_TABLE_PREFIX . "conference_id
			ORDER BY
				cfi_conference_name ASC;
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
