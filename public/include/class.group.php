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
 * of groups in the system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.status.php");

class Group
{


	/**
	 * Method used to check whether a group exists or not.
	 *
	 * @access  public
	 * @param   integer $grp_id The group ID
	 * @return  boolean
	 */
	function exists($grp_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_TABLE_PREFIX . "group
                 WHERE
                    grp_id=".$db->quote($grp_id, 'INTEGER');
		
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
	 * Method used to get the group ID of the given group title.
	 *
	 * @access  public
	 * @param   string $grp_title The group title
	 * @return  integer The group ID
	 */
	function getID($grp_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    grp_id
                 FROM
                    " . APP_TABLE_PREFIX . "group
                 WHERE
                    grp_title=".$db->quote($grp_title);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;		
	}


	/**
	 * Method used to get the title of a given group ID.
	 *
	 * @access  public
	 * @param   integer $grp_id The group ID
	 * @return  string The group title
	 */
	function getName($grp_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		static $returns;

		if (!empty($returns[$grp_id])) {
			return $returns[$grp_id];
		}

		$stmt = "SELECT
                    grp_title
                 FROM
                    " . APP_TABLE_PREFIX . "group
                 WHERE
                    grp_id=".$db->quote($grp_id, 'INTEGER');
		
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
	
		if ($GLOBALS['app_cache']) {
			if (!is_array($returns) || count($returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
				$returns = array();
			}
			$returns[$grp_id] = $res;
		}
		return $res;
	}


	/**
	 * Method used to get the details for a given group ID.
	 *
	 * @access  public
	 * @param   integer $grp_id The group ID
	 * @return  array The group details
	 */
	function getDetails($grp_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "group
                 WHERE
                    grp_id=".$db->quote($grp_id, 'INTEGER');
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		$res["grp_users"] = Group::getUserColList($res["grp_id"]);
		return $res;
	}


	/**
	 * Method used to remove a given set of groups from the system.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "group
                 WHERE
                    grp_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
		try {
			$db->query($stmt, $_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}		
		Group::removeUserByGroups($_POST["items"]);
		return true;
	}


	/**
	 * Method used to remove all group/user associations for a given
	 * set of groups.
	 *
	 * @access  public
	 * @param   array $ids The group IDs
	 * @return  boolean
	 */
	function removeUserByGroups($ids)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "group_user
                 WHERE
                    gpu_grp_id IN (".Misc::arrayToSQLBindStr($ids).")";
		try {
			$db->query($stmt, $ids);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}


	/**
	 * Method used to update the details of the group information.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 otherwise
	 */
	function update()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (Validation::isWhitespace($_POST["title"])) {
			return -2;
		}
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "group
                 SET
                    grp_title=" . $db->quote($_POST["title"]) . ",
                    grp_status=" . $db->quote($_POST["status"]) . "
                 WHERE
                    grp_id=" . $db->quote($_POST["id"], 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		Group::removeUserByGroups(array($_POST["id"]));
		for ($i = 0; $i < count($_POST["users"]); $i++) {
			Group::associateUser($_POST["id"], $_POST["users"][$i]);
		}
		return 1;
	}


	/**
	 * Method used to associate an user to a group.
	 *
	 * @access  public
	 * @param   integer $grp_id The group ID
	 * @param   integer $usr_id The user ID
	 * @return  boolean
	 */
	function associateUser($grp_id, $usr_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "group_user
                 (
                    gpu_usr_id,
                    gpu_grp_id
                 ) VALUES (
                    ".$db->quote($usr_id, 'INTEGER').",
                    ".$db->quote($grp_id, 'INTEGER')."
                 )";
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}


	/**
	 * Method used to add a new group to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 or -2 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (Validation::isWhitespace($_POST["title"])) {
			return -2;
		}
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "group
                 (
                    grp_created_date,
                    grp_title,
                    grp_status
                 ) VALUES (
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    " . $db->quote($_POST["title"]) . ",
                    " . $db->quote($_POST["status"]) . "
                 )";

		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
	
		$new_grp_id = $db->lastInsertId(APP_TABLE_PREFIX . "group", "grp_id");
		for ($i = 0; $i < count($_POST["users"]); $i++) {
			Group::associateUser($new_grp_id, $_POST["users"][$i]);
		}
		return 1;
	}


	/**
	 * Method used to get the list of groups available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of groups
	 */
	function getList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
					*
                 FROM
                    " . APP_TABLE_PREFIX . "group
                 ORDER BY
                    grp_title";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get an associative array of group ID and title
	 * of all groups available in the system.
	 *
	 * @access  public
	 * @param   integer $usr_id The user ID
	 * @return  array The list of groups
	 */
	function getAssocList($usr_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		static $returns;

		if (!empty($returns[$usr_id])) {
			return $returns[$usr_id];
		}

		$stmt = "SELECT
                    grp_id,
                    grp_title
                 FROM
                    " . APP_TABLE_PREFIX . "group,
                    " . APP_TABLE_PREFIX . "group_user
                 WHERE
                    grp_id=gpu_grp_id AND
                    gpu_usr_id=".$db->quote($usr_id. 'INTEGER')."
                 ORDER BY
                    grp_title";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		if ($GLOBALS['app_cache']) {
			if (!is_array($returns) || count($returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
				$returns = array();
			}
			$returns[$usr_id] = $res;
		}
		return $res;
	}

	/**
	 * Method used to get an associative array of group ID and title
	 * of all groups available in the system.
	 *
	 * @access  public
	 * @param   integer $usr_id The user ID
	 * @return  array The list of groups
	 */
	function getAssocListAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    grp_id,
                    grp_title
                 FROM
                    " . APP_TABLE_PREFIX . "group
                 ORDER BY
                    grp_title";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the list of users associated with a given group.
	 *
	 * @access  public
	 * @param   integer $grp_id The group ID
	 * @param   string $status The desired user status
	 * @param   integer $role The role ID of the user
	 * @return  array The list of users
	 */
	function getUserAssocList($grp_id, $status = NULL, $role = NULL)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    usr_id,
                    usr_full_name
                 FROM
                    " . APP_TABLE_PREFIX . "user,
                    " . APP_TABLE_PREFIX . "group_user
                 WHERE
                    gpu_grp_id in (".$db->quote($grp_id, 'INTEGER').") AND
                    gpu_usr_id=usr_id ";
		$stmt .= "
                 ORDER BY
                    usr_full_name ASC";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get a list of user IDs associated with a given
	 * group.
	 *
	 * @access  public
	 * @param   integer $grp_id The group ID
	 * @return  array The list of user IDs
	 */
	function getUserColList($grp_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    usr_id
                 FROM
                    " . APP_TABLE_PREFIX . "user,
                    " . APP_TABLE_PREFIX . "group_user
                 WHERE
                    gpu_grp_id=".$db->quote($grp_id, 'INTEGER')." AND
                    gpu_usr_id=usr_id
                 ORDER BY
                    usr_full_name ASC";
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get a list of group IDs associated with a given
	 * user.
	 *
	 * @access  public
	 * @param   integer $user_id The user ID
	 * @return  array The list of group IDs
	 */
	function getGroupColList($usr_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    gpu_grp_id
                 FROM
                    " . APP_TABLE_PREFIX . "group_user
                 WHERE
                    gpu_usr_id=".$db->quote($usr_id, 'INTEGER');
		
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}



	/**
	 * Method used to get an associative array of group ID and title
	 * of all groups that exist in the system that are active.
	 *
	 * @access  public
	 * @return  array List of groups
	 */
	function getAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    grp_id,
                    grp_title
                 FROM
                    " . APP_TABLE_PREFIX . "group
				 WHERE grp_status = 'active'
                 ORDER BY
                    grp_title";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get an associative array of group ID and title
	 * of all groups that exist in the system that are active.
	 *
	 * @access  public
	 * @return  array List of groups
	 */
	function getActiveAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    grp_id,
                    grp_title
                 FROM
                    " . APP_TABLE_PREFIX . "group
				 WHERE grp_status = 'active'
                 ORDER BY
                    grp_title";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get an associative array of group ID and title
	 * of all groups that exist in the system except for the current persons team.
	 *
	 * @@@ 12/07/04 CK - added this function
	 * @access  public
	 * @return  array List of groups
	 */
	function getAllExcept($excluded_grp_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    grp_id,
                    grp_title
                 FROM
                    " . APP_TABLE_PREFIX . "group
				 WHERE grp_id <> ".$db->quote($excluded_grp_id, 'INTEGER')."
                 ORDER BY
                    grp_title";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get a list of names and emails that are
	 * associated with a given group and issue.
	 *
	 * @access  public
	 * @param   integer $grp_id The group ID
	 * @param   integer $issue_id The issue ID
	 * @return  array List of names and emails
	 */
	function getAddressBook($grp_id, $issue_id = FALSE)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    usr_full_name,
                    usr_email
                 FROM
                    " . APP_TABLE_PREFIX . "user,
                    " . APP_TABLE_PREFIX . "group_user
                 WHERE
                    gpu_grp_id=".$db->quote($grp_id, 'INTEGER')." AND
                    gpu_usr_id=usr_id AND
                    usr_id != " . APP_SYSTEM_USER_ID . "
                 ORDER BY
                    usr_full_name ASC";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get a list of names and emails that are
	 * associated with a given group and issue.
	 *
	 * @@@ CK - Added this function so that lookup fields didnt have the name <email> format so the javascript email validator wouldn't stuff up
	 *
	 * @access  public
	 * @param   integer $grp_id The group ID
	 * @param   integer $issue_id The issue ID
	 * @return  array List of names and emails
	 */
	function getAddressBookLookup($grp_id, $issue_id = FALSE)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    usr_full_name,
                    usr_email
                 FROM
                    " . APP_TABLE_PREFIX . "user,
                    " . APP_TABLE_PREFIX . "group_user
                 WHERE
                    gpu_grp_id=".$db->quote($grp_id, 'INTEGER')." AND
                    gpu_usr_id=usr_id AND
                    usr_id != " . APP_SYSTEM_USER_ID . "
                 ORDER BY
                    usr_full_name ASC";
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
