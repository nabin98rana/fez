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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle the business logic related to the administration
 * of authors in the system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.status.php");

class Author
{
	/**
	 * Method used to check whether a author exists or not.
	 *
	 * @access  public
	 * @param   integer $aut_id The author ID
	 * @return  boolean
	 */
	function exists($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($aut_id) || !is_numeric($aut_id)) {
			return false;
		}

		$stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}

		if ($res > 0) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Method used to get the author ID of the given author title.
	 *
	 * @access  public
	 * @param   string $aut_title The author title
	 * @return  integer The author ID
	 */
	function getID($aut_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_title=".$db->quote($aut_title);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the author ID of the given author name. Use carefully as if there are more than one match it will only return the first.
	 *
	 * @access  public
	 * @param   string $aut_fname The author first name
	 * @param   string $aut_lname The author last name
	 * @return  integer The author ID
	 */
	function getIDByName($aut_fname, $aut_lname)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE ";
		if (is_numeric(strpos($aut_fname, "."))) {
			$aut_fname = substr($aut_fname, 0, strpos($aut_fname, "."));
			$stmt .= " aut_fname like ".$db->quote($aut_fname. '%')." and aut_lname=".$db->quote($aut_lname);
		} else {
			$stmt .= " aut_fname = ".$db->quote($aut_fname)." and aut_lname=".$db->quote($aut_lname);
		}
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the title of a given author ID.
	 *
	 * @access  public
	 * @param   integer $aut_id The author ID
	 * @return  string The author title
	 */
	function getName($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($aut_id) || !is_numeric($aut_id)) {
			return "";
		}

		static $returns;

		if (!empty($returns[$aut_id])) {
			return $returns[$aut_id];
		}

		$stmt = "SELECT
                    aut_title
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		if ($GLOBALS['app_cache']) {
			if (!is_array($returns) || count($returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
				$returns = array();
			}
			$returns[$aut_id] = $res;
		}
		return $res;
	}


	/**
	 * Method used to get the details for a given author ID.
	 *
	 * @access  public
	 * @param   integer $aut_id The author ID
	 * @return  array The author details
	 */
	function getDetails($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($aut_id) || !is_numeric($aut_id)) {
			return "";
		}

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

		$res["grp_users"] = Group::getUserColList($res["aut_id"]);
		return $res;
	}
	
	function getDetailsByUsername($aut_org_username)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        if(empty($aut_org_username)) {
           return "";   
        }
        
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_org_username=".$db->quote($aut_org_username)."
                 OR aut_mypub_url=".$db->quote($aut_org_username);
        
	    try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

        if (is_numeric($res["aut_id"])) {
        	$res["grp_users"] = Group::getUserColList($res["aut_id"]);
        }
        return $res;
    }

	/**
	 * Method used to remove a given set of authors from the system.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
		try {
			$db->query($stmt, $_POST['items']);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}
		return true;
	}

	/**
	 * Method used to update the details of the author information.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 otherwise
	 */
	function update()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (Validation::isWhitespace($_POST["lname"])) {
			return -2;
		}
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "author
                 SET
                    aut_title=" . $db->quote($_POST["title"]) . ",
                    aut_fname=" . $db->quote($_POST["fname"]) . ",
                    aut_mname=" . $db->quote($_POST["mname"]) . ",
                    aut_lname=" . $db->quote($_POST["lname"]) . ",
                    aut_display_name=" . $db->quote($_POST["dname"]) . ",
                    aut_position=" . $db->quote($_POST["position"]) . ",
                    aut_org_username=" . $db->quote($_POST["org_username"]) . ",
                    aut_cv_link=" . $db->quote($_POST["cv_link"]) . ",																				
                    aut_homepage_link=" . $db->quote($_POST["homepage_link"]) . ",
                    aut_ref_num=" . $db->quote($_POST["aut_ref_num"]) . ",
                    aut_researcher_id=" . $db->quote($_POST["researcher_id"]).",
                    aut_scopus_id=" . $db->quote($_POST["scopus_id"]).",
                    aut_update_date=" . $db->quote(Date_API::getCurrentDateGMT());
		if ($_POST["org_staff_id"] !== "") {
			$stmt .= ",aut_org_staff_id=" . $db->quote($_POST["org_staff_id"]) . " ";
		} else {
			$stmt .= ",aut_org_staff_id=null ";
		}
		$stmt .= "WHERE
                    aut_id=" . $db->quote($_POST["id"], 'INTEGER');
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
		return 1;
	}
	
	
    function updateMyPubURL($username, $mypub_url)
    { 
        if (Validation::isWhitespace($mypub_url)) {
            return -1;
        }
        if (Validation::isUserFileName($mypub_url) == true) {
            return -2;
        }
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "author
                 SET
                    aut_mypub_url=? ";
        $stmt .= "WHERE
                    aut_org_username=?";
        
	    try {
			$db->query($stmt, array($mypub_url, $username));
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
        return 1;
    }
	


	/**
	 * Method used to add a new author to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 or -2 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (Validation::isWhitespace($_POST["lname"])) {
			return -2;
		}
		$insert = "INSERT INTO
                    " . APP_TABLE_PREFIX . "author
                 (
                    aut_title,
					aut_fname,
					aut_lname,
                    aut_created_date,
                    aut_display_name";

		if ($_POST["org_staff_id"] !== "")     { $insert .= ", aut_org_staff_id "; }
		if ($_POST["org_username"] !== "")     { $insert .= ", aut_org_username "; }
		if ($_POST["mname"] !== "")            { $insert .= ", aut_mname "; }
		if ($_POST["position"] !== "")         { $insert .= ", aut_position "; }
		if ($_POST["cv_link"] !== "")          { $insert .= ", aut_cv_link "; }
		if ($_POST["homepage_link"] !== "")    { $insert .= ", aut_homepage_link "; }
		if ($_POST["aut_ref_num"] !== "")      { $insert .= ", aut_ref_num "; }
		if ($_POST["researcher_id"] !== "")      { $insert .= ", aut_researcher_id "; }
		if ($_POST["scopus_id"] !== "")      { $insert .= ", aut_scopus_id "; }

		$values = ") VALUES (
                    " . $db->quote($_POST["title"]) . ",
					" . $db->quote($_POST["fname"]) . ",					
					" . $db->quote($_POST["lname"]) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . "
                  ";

		if ($_POST["dname"] !== "") {
			$values .= ", " . $db->quote($_POST["dname"]);
		} else {
			$values .= ", " . $db->quote($_POST["fname"] . '' . $_POST["lname"]);
		}

		if ($_POST["org_staff_id"] !== "") { $values .= ", " . $db->quote($_POST["org_staff_id"]); }
		if ($_POST["org_username"] !== "") { $values .= ", " . $db->quote($_POST["org_username"]); }
		if ($_POST["mname"] !== "")        { $values .= ", " . $db->quote($_POST["mname"]); }
		if ($_POST["position"] !== "")        { $values .= ", " . $db->quote($_POST["position"]); }
		if ($_POST["cv_link"] !== "")        { $values .= ", " . $db->quote($_POST["cv_link"]); }
		if ($_POST["homepage_link"] !== "")        { $values .= ", " . $db->quote($_POST["homepage_link"]);}
		if ($_POST["aut_ref_num"] !== "")        { $values .= ", " . $db->quote($_POST["aut_ref_num"]);}
		if ($_POST["researcher_id"] !== "")        { $values .= ", " . $db->quote($_POST["researcher_id"]); }
		if ($_POST["scopus_id"] !== "")        { $values .= ", " . $db->quote($_POST["scopus_id"]); }


		$values .= ")";

		$stmt = $insert . $values;
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
		return 1;
	}


	/**
	 * Method used to get the list of authors available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of authors
	 */
	function getList($current_row = 0, $max = 25, $order_by = 'aut_lname', $filter="", $staff_id = "")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$where_stmt = "";
		$extra_stmt = "";
		$extra_order_stmt = "";
		if (!empty($filter)) {
			// For the Author table we are going to keep it in MyISAM if you are using MySQL because there is no table locking issue with this table like with others.
			if (APP_SQL_DBTYPE != "mysql") {
				$where_stmt .= " WHERE ";
				$names = explode(" ", $filter);
				$nameCounter = 0;
				foreach ($names as $name) {
					$nameCounter++;
					if ($nameCounter > 1) {
						$where_stmt .= " AND ";
					}
					$where_stmt .= " (aut_fname LIKE ".$db->quote($name.'%')." OR aut_lname LIKE ".$db->quote($name.'%').") ";
				}
			} else {
				$where_stmt .= " WHERE MATCH(aut_fname, aut_lname) AGAINST (".$db->quote('*'.$filter.'*')." IN BOOLEAN MODE) ";
				$extra_stmt = " , MATCH(aut_fname, aut_lname) AGAINST (".$db->quote($filter).") as Relevance ";
				$extra_order_stmt = " Relevance DESC, ";
			}
		} elseif(!empty($staff_id)) {
			$where_stmt .= " WHERE aut_org_staff_id = ".$db->quote($staff_id);
		}
			
		$start = $current_row * $max;
		if (APP_SQL_DBTYPE != "mysql") {
			$stmt = "SELECT ";
		} else {
			$stmt = "SELECT SQL_CALC_FOUND_ROWS ";
		}
		$stmt .= "
					* ".$extra_stmt."
                 FROM
                    " . APP_TABLE_PREFIX . "author
				".$where_stmt."
                 ORDER BY ".$extra_order_stmt."
                    ".$db->quote($order_by)."
				 LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

		if (APP_SQL_DBTYPE != "mysql") {
			$stmt = "SELECT COUNT(*)
	                 FROM
	                    " . APP_TABLE_PREFIX . "author
					".$where_stmt;
		} else {
			$stmt = 'SELECT FOUND_ROWS()';
		}

		try {
			$total_rows = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
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
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page
		)
		);

	}

	function getPositionsByOrgStaffID($org_staff_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    POS_TITLE, org_title, YEAR(DT_FROM) AS DT_FROM, YEAR(DT_TO) AS DT_TO
					FROM fez_author
					LEFT JOIN hr_position_vw on WAMIKEY = aut_org_staff_id
					LEFT JOIN fez_org_structure on AOU = org_extdb_id AND org_extdb_name = 'hr'
					WHERE aut_org_staff_id = ".$db->quote($org_staff_id, 'INTEGER');

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

		return $res;
	}


	/**
	 * Method used to get the list of authors available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of authors
	 */
	function getListByStaffIDList($current_row = 0, $max = 25, $order_by = 'aut_lname', $staff_ids = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_array($staff_ids)) {
			return false;
		}

		if (count($staff_ids) == 0) {
			return false;
		}

		$where_stmt = "";
		$extra_stmt = "";
		$bind_params = array();

		if (!empty($staff_ids)) {
			$where_stmt .= " WHERE aut_org_staff_id IN  (".Misc::arrayToSQLBindStr($staff_ids) . ")";
			$bind_params = array_merge($bind_params, $staff_ids);
		}

		$start = $current_row * $max;
		$stmt = "SELECT SQL_CALC_FOUND_ROWS
                 FROM
                    " . APP_TABLE_PREFIX . "author
				".$where_stmt."
                 ORDER BY ".$db->quote($order_by)."
				 LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');
		//		echo $stmt;

		try {
			$res = $db->fetchAll($stmt, $bind_params);
			$total_rows = $db->fetchOne('SELECT FOUND_ROWS()');
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		foreach ($res as $key => $row) {
			$res[$key]['positions'] = array();
			$res[$key]['positions'] = Author::getPositionsByOrgStaffID($res[$key]['aut_org_staff_id']);
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
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page
		)
		);
	}

	/**
	 * Method used to get an associative array of author ID and concatenated title, first name, lastname
	 * of all authors available in the system.
	 *
	 * @access  public
	 * @param   integer $aut_id The author ID
	 * @return  array The list of authors
	 */
	function getAssocList($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		static $returns;

		if (!empty($returns[$aut_id])) {
			return $returns[$aut_id];
		}

		$stmt = "SELECT
                    aut_id,
                    concat_ws(', ',   aut_lname, aut_mname, aut_fname, aut_id) as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_lname";

		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

		if ($GLOBALS['app_cache']) {
			if (!is_array($returns) || count($returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
				$returns = array();
			}
			$returns[$aut_id] = $res;
		}
		return $res;
	}


	/**
	 * Method used to get an associative array of author ID and title
	 * of all authors available in the system.
	 *
	 * @access  public
	 * @param   integer $usr_id The user ID
	 * @return  array The list of authors
	 */
	function getAssocListAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    aut_id,
                    concat_ws(', ',   aut_lname, aut_fname, aut_id) as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_fullname";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get an associative array of author ID and title
	 * of all authors available in the system.
	 *
	 * @access  public
	 * @return  array The list of authors
	 */
	function getAssocListAllBasic()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    aut_id,
                    concat_ws(' ',   aut_fname, aut_lname) AS aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_fullname";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

		return $res;
	}
	/**
	 * Method used to search and suggest all the authors names for a given string.
	 *
	 * @access  public
	 * @return  array List of authors
	 */
	function suggest($term, $assoc = false)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp = APP_TABLE_PREFIX;

		//some function like concat_ws might not be supportd in all databases, however postgresql does have a mysql_compat plugin library that adds these..
		//Could be done in the code later if it is a problem
		$stmt = " SELECT aut_id as id, concat_ws(' - ', aut_org_username, aut_org_staff_id)  as username, aut_fullname as name  FROM (
			  SELECT aut_id, 
			    aut_org_username,
				aut_org_staff_id,
			    aut_display_name as aut_fullname";

		// For the Author table we are going to keep it in MyISAM if you are using MySQL because there is no table locking issue with this table like with others.
		// TODO: For postgres it might be worth adding a condition here to use TSEARCH2 which is close to fulltext indexing in MySQL MyISAM
		if (APP_SQL_DBTYPE == "mysql") {
			$stmt .= "
				,MATCH(aut_display_name) AGAINST (".$db->quote($term).") as Relevance ";
		}
		$stmt .= "
				FROM ".$dbtp."author";

		if (APP_SQL_DBTYPE == "mysql") {
			$stmt .= "
			 WHERE MATCH (aut_display_name) AGAINST (".$db->quote('*'.$term.'*')." IN BOOLEAN MODE)";
		} else {
			$stmt .= " WHERE ";
			$names = explode(" ", $term);
			$nameCounter = 0;
			foreach ($names as $name) {
				$nameCounter++;
				if ($nameCounter > 1) {
					$stmt .= " AND ";
				}
				$stmt .= " (aut_fname LIKE ".$db->quote($name.'%')." OR aut_lname LIKE ".$db->quote($name.'%').") ";
			}
		}
		if (APP_AUTHOR_SUGGEST_MODE == 2) {
			$stmt .= "AND (aut_org_username IS NOT NULL OR aut_org_staff_id IS NOT NULL)";
		}
		if (APP_SQL_DBTYPE == "mysql") {
			$stmt .= " ORDER BY Relevance DESC, aut_fullname LIMIT 0,60) as tempsuggest";
		} else {
			$stmt .= " LIMIT 60 OFFSET 0) as tempsuggest";
		}

		try {
			if( $assoc ) {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			else {
				$res = $db->fetchAssoc($stmt);
			}
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

		return $res;
	}


	/**
	 * Method used to get an associative array of author ID and title
	 * of all authors that exist in the system that are active.
	 *
	 * @access  public
	 * @return  array List of authors
	 */
	function getAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    aut_id,
                    aut_display_name as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_title";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	function getFullname($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($aut_id) || !is_numeric($aut_id)) {
			return "";
		}
			
		$stmt = "SELECT
                    aut_display_name as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER')."
                 ORDER BY
                    aut_title";
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	function getDisplayName($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($aut_id) || !is_numeric($aut_id)) {
			return "";
		}
			
		$stmt = "SELECT
                    aut_display_name
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER')."
                 ORDER BY
                    aut_title";
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

		return $res;
	}

	function getDisplayNameUserName($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($aut_id) || !is_numeric($aut_id)) {
			return "";
		}
			
		$stmt = "SELECT
                    aut_id, aut_display_name, concat_ws(' - ', aut_org_username, aut_org_staff_id)  as aut_org_username
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER');

		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}


	function getOrgStaffId($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($aut_id) || !is_numeric($aut_id)) {
			return "";
		}
			
		$stmt = "SELECT
                    aut_org_staff_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER')."
                 ORDER BY
                    aut_title";
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	function getIDsByOrgStaffIds($org_staff_ids=array())
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_org_staff_id IN (".Misc::arrayToSQLBindStr($org_staff_ids).")";
		try {
			$res = $db->fetchCol($stmt, $org_staff_ids);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get an associative array of author ID and title
	 * of all authors that exist in the system that are active.
	 *
	 * @access  public
	 * @return  array List of authors
	 */
	function getActiveAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    aut_id,
                    concat_ws(' ', aut_title, aut_fname, aut_mname, aut_lname) as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_title";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}
	
	
	/**
	 * Gets a list of alternative names this author used
	 *
	 * @param integer $authorId
	 * @return array if results found, or an empty string if no results found
	 */
	function getAlternativeNamesList($authorId)
	{
		$log = FezLog::get();
		$db = DB_API::get();
	
		$query = "SELECT rek_author, count(*) as paper_count FROM " . APP_TABLE_PREFIX . "record_search_key_author aut ";
		$query .= "JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id autid ";
		$query .= "ON (rek_author_pid = rek_author_id_pid AND aut.rek_author_order = autid.rek_author_id_order) ";
		$query .= "WHERE autid.rek_author_id = ".$db->quote($authorId, 'INTEGER');
		$query .= " GROUP BY rek_author ";
		$query .= "ORDER BY 2 desc ";

		try {
			$res = $db->fetchPairs($query);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}
	

	function najaxGetMeta()
	{
		NAJAX_Client::mapMethods($this, array('getFullname','getDisplayName' ));
		NAJAX_Client::publicMethods($this, array('getFullname','getDisplayName'));
	}
}
