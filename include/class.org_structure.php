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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle organisational structures.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");


class Org_Structure
{

	/**
	 * Method used to remove a given list of organsational structures.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		// first delete all children
		// get all immediate children
		$items = $_POST["items"];
		if (!is_array($items)) { return false; }
		$all_items = $items;
		foreach ($items as $item) {
			$child_items = Org_Structure::getAllTreeIDs($item);
			if (is_array($child_items)) {
				$all_items = array_merge($all_items, $child_items);
			}
		}
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 WHERE
                    org_id IN (".Misc::arrayToSQLBindStr($all_items).")";
		Org_Structure::deleteRelationship($all_items);
		
		try {
			$db->query($stmt, $all_items);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}

	/**
	 * Method using to delete a organisational structure parent-child relationship in Fez.
	 *
	 * @access  public
	 * @param string $items The string comma separated list of org ids to remove from parent or child relationships
	 * @return boolean
	 */
	function deleteRelationship($items) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "org_structure_relationship
                 WHERE
                    orr_parent_org_id IN (".Misc::arrayToSQLBindStr($items).") OR orr_child_org_id IN (".Misc::arrayToSQLBindStr($items).")";

		try {
			$db->query($stmt, array_merge($items));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return true;
	}

	/**
	 * Method used to add a new organisational structure to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "org_structure
                 (
                    org_title,
					org_desc,
					org_ext_table,
					org_image_filename
                 ) VALUES (
                    " . $db->quote($_POST["org_title"]) . ",
                    " . $db->quote($_POST["org_desc"]) . ",
                    " . $db->quote($_POST["org_ext_table"]) . ",
                    " . $db->quote($_POST["org_image_filename"]) . "										
                 )";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		
		// get last db entered id
		$new_id = $db->lastInsertId();
		Org_Structure::associateParent($_POST["parent_id"], $new_id);
		return 1;
	}


	/**
	 * Method used to add a new organisational structure parent-child relationship to the system.
	 *
	 * @access  public
	 * @param string $parent_id The parent ID to add to the relationship
	 * @param array $child_id The child ID to add to the relationship
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function associateParent($parent_id, $child_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		// no need to associate null parent
		if (empty($parent_id)) {
			return -1;
		}


		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "org_structure_relationship
                 (
                    orr_parent_org_id,
                    orr_child_org_id					
                 ) VALUES (
                    " .$db->quote($parent_id, 'INTEGER'). ",
                    " .$db->quote($child_id, 'INTEGER'). "					
                 )";
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
	/**
	 * Method used to update details of a organisational structure.
	 *
	 * @access  public
	 * @param   integer $org_id The organisational structure ID
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($org_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "org_structure
                 SET 
                    org_title = " . $db->quote($_POST["org_title"]) . ",
                    org_desc = " . $db->quote($_POST["org_desc"]) . ",
                    org_ext_table = " . $db->quote($_POST["org_ext_table"]) . ",
                    org_image_filename = " . $db->quote($_POST["org_image_filename"]) . "
                 WHERE org_id = ".$db->quote($org_id, 'INTEGER');
		
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
	 * Method used to get the title of a specific organisational structure.
	 *
	 * @access  public
	 * @param   integer $org_id The organisational structure ID
	 * @return  string The title of the organisational structure
	 */
	function getTitle($org_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 WHERE
                    org_id=".$db->quote($org_id, 'INTEGER');
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
	 * Method used to get the default org id of a user from their HR feed data
	 *
	 * @access  public
	 * @param   integer $org_id The organisational structure ID
	 * @return  string The title of the organisational structure
	 */
	function getDefaultOrgIDByUsername($username)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_id
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 INNER JOIN hr_position_vw ON AOU = org_extdb_id
                 AND USER_NAME = ".$db->quote($username)."
                 WHERE (org_extdb_name = 'hr' OR org_extdb_name = 'rrtd')";
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
	 * Method used to get the list of organsational structures available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of organsational structures in an associative array (for drop down lists).
	 */
	function getAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
			     WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)
                 ORDER BY
                    org_title ASC";
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
	 * Method used to get the list of organsational structures available in the
	 * system returned in an associative array for drop down lists. This method
	 * returns only those org units that are tagged as coming from HR.
	 *
	 * @access  public
	 * @return  array The list of HR organsational structures in an associative array (for drop down lists).
	 */
	function getAssocListHR()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
			     WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)
				 AND (org_extdb_name = 'hr' OR org_extdb_name = 'rrtd') 
                 ORDER BY
                    org_title ASC";
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
	 * Method used to get the list of organsational structures available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of organsational structures in an associative array (for drop down lists).
	 */
	function getAssocListAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 ORDER BY
                    org_title ASC";
		try {
			$res = $db->fetchAssoc($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the list of organsational structures available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of organsational structures in an associative array (for drop down lists).
	 */
	function getAssocListByID($id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		// used by the xsd match forms
		$stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
				 WHERE org_id = ".$db->quote($id, 'INTEGER');
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
	 * Method used to search and suggest all the org structure names for a given string.
	 *
	 * @access  public
	 * @return  array List of Org structure titles
	 */
	function suggest($term, $assoc = false) 
	{
		$log = FezLog::get();
		$db = DB_API::get();		
		
		$dbtp = APP_TABLE_PREFIX;

		$stmt = " SELECT org_id as id, org_title as name FROM (";
		$stmt .= "
			  SELECT org_id, 
				org_title ";

		if (APP_MYSQL_INNODB_FLAG == "ON" || (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql")))) {
			$stmt .= " FROM ".$dbtp."org_structure
				 WHERE org_title LIKE ".$db->quote($term.'%')." AND org_title NOT LIKE 'Faculty of%' AND (org_extdb_name = 'hr' OR org_extdb_name = 'rrtd') ";
			$stmt .= " LIMIT 10 OFFSET 0) AS tempsuggest";
		} else {
			$stmt .= ",MATCH(org_title) AGAINST (".$db->quote($term).") as Relevance FROM ".$dbtp."org_structure
		 WHERE MATCH (org_title) AGAINST (".$db->quote('*'.$term.'*')." IN BOOLEAN MODE) AND org_title NOT LIKE 'Faculty of%' AND (org_extdb_name = 'hr' OR org_extdb_name = 'rrtd') ";
			$stmt .= " ORDER BY Relevance DESC, org_title LIMIT 10 OFFSET 0) AS tempsuggest";
		}
		
		try {
			if ($assoc) 
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			else
				$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the list of organsational structures available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of organsational structures in an associative array (for drop down lists).
	 */
	function getAssocListByLevel($org_level)
	{
		$log = FezLog::get();
		$db = DB_API::get();		
		
		// used by the xsd match forms
		$stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
				 WHERE org_ext_table = ".$db->quote($org_level);
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
	 * Method used to get the list of organsational structures levels available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of organsational structure levels in an associative array (for drop down lists).
	 */
	function getAssocListLevels()
	{
		$log = FezLog::get();
		$db = DB_API::get();	
		
		// used by the xsd match forms
		$stmt = "SELECT
                    distinct org_ext_table,
					org_ext_table
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure";
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
	 * Method used to get the list of organsational structures available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of organsational structures in an associative array (for drop down lists).
	 */
	function getListByID($id)
	{
		$log = FezLog::get();
		$db = DB_API::get();	
		
		$stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
				 WHERE org_id = ".$db->quote($id, 'INTEGER');
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
	 * Method used to get the list of all organsational structures available in the
	 * system for drop down lists, with supplemental information.
	 *
	 * @access  public
	 * @return  array The list of organsational structures (for drop down lists).
	 */
	function getListAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();	
		
		$stmt = "SELECT
                    org_id,
                    org_title,
                    org_ext_table 
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 ORDER BY 
                    org_title";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if (empty($res)) {
			return array();
		} else {
			return $res;
		}	
	}


	/**
	 * Method used to get the list of organsational structures available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of organsational structures
	 */
	function getList($parent_id=false)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship
					     WHERE orr_parent_org_id = ".$db->quote($parent_id, 'INTEGER')." AND orr_child_org_id = org_id ";			
		} else {
			$stmt .= " WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)";
		}
		$stmt .= " ORDER BY  org_title ASC";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		if (empty($res)) {
			return array();
		} else {
			return $res;
		}
	}

	/**
	 * Method used to get the list of organsational structures available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of organsational structures
	 */
	function getAssocListFullDisplay($parent_id=false, $indent="", $level=0, $level_limit=false)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (is_numeric($level_limit)) {
			if ($level == $level_limit) {
				return array();
			}
		}
		$level++;
		$stmt = "SELECT
                    org_id,
					concat(".$db->quote($indent).",org_title) as org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship
					     WHERE orr_parent_org_id = ".$db->quote($parent_id, 'INTEGER')." AND orr_child_org_id = org_id ";			
		} else {
			$stmt .= " WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)";
		}
		
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if (empty($res)) {
			return array();
		} else {
			$newArray = array();
			$tempArray = array();
			if ($parent_id != false) {
				$indent .= "---------";
			}
			foreach ($res as $key => $data) {
				if ($parent_id != false) {
					$newArray[$key] = $data;
				}
				$tempArray = Org_Structure::getAssocListFullDisplay($key, $indent, $level, $level_limit);
				if (count($tempArray) > 0) {
					if ($parent_id == false) {
						$newArray['data'][$key] = Misc::array_merge_preserve(@$newArray[$key], $tempArray);
						$newArray['title'][$key] = $data;
					} else {
						$newArray = Misc::array_merge_preserve($newArray, $tempArray);
					}
				}
			}
			$res = $newArray;
			return $res;
		}
	}

	/**
	 * Method used to get the associative list of organsational structures available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of organsational structures
	 */
	function getParentAssocListFullDisplay($child_id, $indent="")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_id,
					concat(".$db->quote($indent).",org_title) as org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";
		$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship
					     WHERE orr_parent_org_id = org_id AND orr_child_org_id = ".$db->quote($child_id, 'INTEGER');			
		$stmt .= "
                 ORDER BY
                    org_title ASC";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		if (empty($res)) {
			return array();
		} else {
			$newArray = array();
			$tempArray = array();
			foreach ($res as $key => $data) {
				if ($child_id != false) {
					$newArray[$key] = $data;
				}
				$tempArray = Org_Structure::getParentAssocListFullDisplay($key, $indent);
				if (count($tempArray) > 0) {
					if ($child_id == false) {
						$newArray['data'][$key] = Misc::array_merge_preserve($tempArray, $newArray[$key]);
						$newArray['title'][$key] = $data;
					} else {
						$newArray = Misc::array_merge_preserve($tempArray, $newArray);
					}
				}
			}
			$res = $newArray;
			return $res;
		}
	}

	/**
	 * Method used to get the list of authors associated with organsational structures available in the
	 * system.
	 *
	 * @access  public
	 * @param string $org_id The organisation ID
	 * @return  array The list of authors in the given organisation ID
	 */
	function getAuthorsByOrgID($org_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT distinct
                    aut_id,
                    concat_ws(', ',   aut_lname, aut_mname, aut_fname, aut_id) as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author INNER JOIN ".
		APP_SQL_DBNAME . "." . APP_TABLE_PREFIX . "author_org_structure ON (auo_org_id = ".$db->quote($org_id, 'INTEGER')." AND aut_id = auo_aut_id)
				 WHERE auo_assessed = 'Y'
                 ORDER BY
                    aut_fullname ASC";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}



	function getAuthorOrgListByOrgStaffID($org_staff_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_title
					FROM fez_author
					LEFT JOIN hr_position_vw on WAMIKEY = aut_org_staff_id
					LEFT JOIN fez_org_structure on AOU = org_extdb_id AND org_extdb_name = 'hr'
					WHERE aut_org_staff_id = ".$db->quote($org_staff_id, 'INTEGER');
		
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
	 * Method used to get the list of organsational structures available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of organsational structures
	 */
	function getParentListFullDisplay($child_id, $indent="")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_id,
					concat(".$db->quote($indent).",org_title) as org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";
		$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship
					     WHERE orr_parent_org_id = org_id AND orr_child_org_id = ".$db->quote($child_id, 'INTEGER');			
		$stmt .= "
                 ORDER BY
                    org_title ASC";

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		if (empty($res)) {
			return array();
		} else {
			$newArray = array();
			$tempArray = array();
			foreach ($res as $key => $data) {
				if ($child_id != false) {
					$newArray[$key] = $data;
				}
				$tempArray = Org_Structure::getParentListFullDisplay($key, $indent);
				if (count($tempArray) > 0) {
					if ($child_id == false) {
						$newArray['data'][$key] = array_merge($tempArray, $newArray[$key]);
						$newArray['title'][$key] = $data;
					} else {
						$newArray = array_merge($tempArray, $newArray);
					}
				}
			}
			$res = $newArray;
			return $res;
		}		
	}

	/**
	 * Recursive function to get all the IDs in a CV tree (to be used in counts for entire CV parents including children).
	 *
	 * @access  public
	 * @param string $parent_id
	 * @return  array The list of organsational structures
	 */
	function getAllTreeIDs($parent_id=false) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    org_id
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";
		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship
						 WHERE orr_parent_org_id = ".$db->quote($parent_id, 'INTEGER')." AND orr_child_org_id = org_id ";			
		} else {
			$stmt .= " WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)";
		}
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		$newArray = array();
		foreach ($res as $row) {
			$tempArray = array();
			$tempArray = Org_Structure::getAllTreeIDs($row[0]);
			if (count($tempArray) > 0) {
				$newArray[$row[0]] = $tempArray;
			} else {
				$newArray[$row[0]] = $row[0];
			}
		}
		return $newArray;		
	}


	/**
	 * Method used to get the details of a specific organisational structure.
	 *
	 * @access  public
	 * @param   integer $org_id The organisational structure ID
	 * @return  array The organisational structure details
	 */
	function getDetails($org_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 WHERE
                    org_id=".$db->quote($org_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}
		
	/**
	 * Method used to get a list of all known org units.
	 *
	 * @access  public
	 * @return  array The organisational unit list.
	 */
	function getOrgUnitList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
					aurion_org_id,
					aurion_org_desc
				FROM
					hr_org_unit_distinct_manual
				ORDER BY
					aurion_org_desc;
				";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		$return = array();
		if (empty($res)) {
			return array();
		} else {
			
			foreach ($res as $row) {
				$return[$row['aurion_org_id']] = $row['aurion_org_desc'];
			}
			return $return;
		}
		
	}
	
}
