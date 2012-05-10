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
 * Class to handle controlled vocabularies.
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


class Controlled_Vocab
{
	
	const CACHE_KEY = 'Controlled_Vocab_';

	/**
	 * Method used to remove a given list of controlled vocabularies.
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
			$child_items = Controlled_Vocab::getAllTreeIDs($item);
			if (is_array($child_items)) {
				$all_items = array_merge($all_items, $child_items);
			}
		}
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id IN (".Misc::arrayToSQLBindStr($all_items).")";
		Controlled_Vocab::deleteRelationship($all_items);
		try {
			$db->query($stmt, $all_items);
			FezCache::remove(Controlled_Vocab::CACHE_KEY);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return true;
	}

	/**
	 * Method using to delete a controlled vocabulary parent-child relationship in Fez.
	 *
	 * @access  public
	 * @param string $items The string comma separated list of CV ids to remove from parent or child relationships
	 * @return boolean
	 */
	function deleteRelationship($items)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab_relationship
                 WHERE
                    cvr_parent_cvo_id IN (".Misc::arrayToSQLBindStr($items).") OR cvr_child_cvo_id IN (".Misc::arrayToSQLBindStr($items).")";
		try {
			$db->query($stmt, array_merge($items, $items));
			FezCache::remove(Controlled_Vocab::CACHE_KEY);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return true;
	}

	/**
	 * Method used to add a new controlled vocabulary to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 (
                    cvo_title,
                    cvo_desc";
		if (is_numeric($_POST["cvo_external_id"])) {
			$stmt .= ", cvo_external_id";
		}
		if (is_numeric($_POST["cvo_hide"])) {
			$stmt .= ", cvo_hide";
		}

		$stmt .= "
                 ) VALUES (
                    " . $db->quote($_POST["cvo_title"]) . ",
                    " . $db->quote($_POST["cvo_desc"]);
		if (is_numeric($_POST["cvo_external_id"])) {
			$stmt .= "," . $db->quote(trim($_POST["cvo_external_id"]));
		}
		if (is_numeric($_POST["cvo_hide"])) {
			$stmt .= "," . $db->quote(trim($_POST["cvo_hide"]));
		}
		$stmt .=")";

		try {
			$db->exec($stmt);
			FezCache::remove(Controlled_Vocab::CACHE_KEY);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		
		// get last db entered id
		$new_id = $db->lastInsertId(APP_TABLE_PREFIX . "controlled_vocab", "cvo_id");
		if (is_numeric($_POST["parent_id"])) {
			Controlled_Vocab::associateParent($_POST["parent_id"], $new_id);
		}
		return 1;
	}

	/**
	 * Method used to add a new controlled vocabulary to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insertDirect($cvo_title, $cvo_external_id="", $parent_id="", $cvo_hide = "")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 (
                    cvo_title";
		if ($cvo_external_id != "") {
			$stmt .= ", cvo_external_id";
		}
		$stmt .= "     ) VALUES (
                " . $db->quote($cvo_title);
		if ($cvo_external_id != "") {
			$stmt .= "," . $db->quote($cvo_external_id);
		}
		if ($cvo_hide != "") {
			$stmt .= "," . $db->quote($cvo_hide);
		}
		$stmt .= ")";

		try {
			$db->exec($stmt);
			FezCache::remove(Controlled_Vocab::CACHE_KEY);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

		// get last db entered id
		$new_id = $db->lastInsertId(APP_TABLE_PREFIX . "controlled_vocab", "cvo_id");
		if (is_numeric($parent_id)) {
			Controlled_Vocab::associateParent($parent_id, $new_id);
		}
		return 1;
	}

	/**
	 * Method used to import a new controlled vocabulary to the system under a parent.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function import($parent_id, $xmlObj)
	{
		$xpath_record = $_POST["cvi_xpath_record"];
		$xpath_id = $_POST["cvi_xpath_id"];
		$xpath_title = $_POST["cvi_xpath_title"];
		$xpath_parent_id = $_POST["cvi_xpath_parent_id"];
		$xpath_extparent_id = $_POST["cvi_xpath_extparent_id"];
		/*		echo "xpath_record = ".$xpath_record."\n";
		 echo "xpath_id = ".$xpath_id."\n";
		 echo "xpath_title = ".$xpath_title."\n";
		 echo "xpath_parent_id = ".$xpath_parent_id."\n";
		 */
		$xmlDoc= new DomDocument();
		$xmlDoc->preserveWhiteSpace = false;
		$xmlDoc->loadXML($xmlObj);

		$xpath = new DOMXPath($xmlDoc);

		$recordNodes = $xpath->query($xpath_record);

		foreach ($recordNodes as $recordNode) {
			$record_id = "";
			if ($xpath_id != "") {
				$id_fields = $xpath->query($xpath_id, $recordNode);
					
				foreach ($id_fields as $id_field) {
					if  ($record_id == "") {
						$record_id = $id_field->nodeValue;
					}
				}
			}
			$title_fields = $xpath->query($xpath_title, $recordNode);
			$record_title = "";
			foreach ($title_fields as $title_field) {
				if  ($record_title == "") {
					$record_title = $title_field->nodeValue;
				}
			}
			$record_parent_id = "";
			if ($xpath_parent_id != "") {
				$parent_id_fields = $xpath->query($xpath_parent_id, $recordNode);
				foreach ($parent_id_fields as $parent_id_field) {
					if  ($parent_id_field == "") {
						$record_parent_id = $parent_id_field->nodeValue;
					}
				}
			}

			// Checks for reference to parent(s) by external id
			// kj 2007/08/27 kai.jauslin@library.ethz.ch

			// $xpath_extparent_id = "parent[@mode='internal']";

			if ($xpath_extparent_id != "") {
				$extparent_id_fields = $xpath->query($xpath_extparent_id, $recordNode);
					
				foreach ($extparent_id_fields as $extparent_id_field) {
					print $extparent_id_field->nodeValue;

					if  ($extparent_id_field->nodeValue > '') {
						$extparent_id = $extparent_id_field->nodeValue;
							
						// set first external reference as parent (overrides internal parent_id)
						$intparent_id = Controlled_Vocab::getInternalIDByExternalID($extparent_id);
							
						if ($intparent_id != '') {
							$record_parent_id =	$intparent_id;
							//print "<br><b>$intparent_id</b>";
						}
					}
				}
			}
			if ($record_id != "" && $record_title != "") {
				if ($record_parent_id == "") {
					$record_parent_id = $parent_id;
				}
				Controlled_Vocab::insertDirect($record_title, $record_id, $record_parent_id);
			}

		}
	}

	/**
	 * Method used to add a new controlled vocabulary parent-child relationship to the system.
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

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "controlled_vocab_relationship
                 (
                    cvr_parent_cvo_id,
                    cvr_child_cvo_id					
                 ) VALUES (
                    " .$db->quote($parent_id, 'INTEGER'). ",
                    " .$db->quote($child_id, 'INTEGER'). "					
                 )";
		try {
			$db->query($stmt);
			FezCache::remove(Controlled_Vocab::CACHE_KEY);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
	/**
	 * Method used to update details of a controlled vocabulary.
	 *
	 * @access  public
	 * @param   integer $cvo_id The controlled vocabulary ID
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($cvo_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 SET 
                    cvo_title = " . $db->quote($_POST["cvo_title"]) . ",
                    cvo_external_id = " . $db->quote(trim($_POST["cvo_external_id"])). ",
                    cvo_desc = " . $db->quote($_POST["cvo_desc"]) . ",
                    cvo_hide = " . $db->quote($_POST["cvo_hide"]) . "
                 WHERE cvo_id = ".$db->quote($cvo_id, 'INTEGER');		
		try {
			$db->exec($stmt);
			FezCache::remove(Controlled_Vocab::CACHE_KEY);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}


	/**
	 * Method used to get the title of a specific controlled vocabulary.
	 *
	 * @access  public
	 * @param   integer $cvo_id The controlled vocabulary ID
	 * @return  string The title of the controlled vocabulary
	 */
	function getTitle($cvo_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($cvo_id)) {
			return "";
		}
		$stmt = "SELECT
                    cvo_title
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id=".$db->quote($cvo_id, 'INTEGER');
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
	 * Method used to check a cvo_id is in the db.
	 *
	 * @access  public
	 * @param   integer $cvo_id The controlled vocabulary ID
	 * @return  boolean true/false
	 */
	function exists($cvo_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($cvo_id)) {
			return false;
		}
		$stmt = "SELECT
                    cvo_title
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id=".$db->quote($cvo_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		if (count($res) == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method used to get the title of a specific controlled vocabulary.
	 *
	 * @access  public
	 * @param   integer $cvo_external_id The controlled vocabulary external ID
	 * @return  string The title of the controlled vocabulary
	 */
	function getTitleByExternalID($cvo_external_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    cvo_title
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_external_id=".$db->quote($cvo_external_id);
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
	 * Method used to map external (content specific) to internal (database) id.
	 * kj 2007/08/27 kai.jauslin@library.ethz.ch
	 *
	 * @access public
	 * @param integer $cvo_external_id  The controlled vocabulary external ID
	 * @return string  The ID of the controlled vocabulary
	 */
	function getInternalIDByExternalID($cvo_external_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    cvo_id
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_external_id=".$db->quote($cvo_external_id);
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
	 * Method used to get the title of a specific controlled vocabulary.
	 *
	 * @access  public
	 * @param   string $cvo_title The controlled vocabulary title
	 * @return  string The ID of the controlled vocabulary
	 */
	function getID($cvo_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    cvo_id
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_title LIKE ".$db->quote($cvo_title."%");
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
	 * Method used to get the list of controlled vocabularies available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
	 */
	function getAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
			     WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_TABLE_PREFIX . "controlled_vocab_relationship)
                 ORDER BY
                    cvo_title ASC";
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
	 * Method used to get the list of controlled vocabularies available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
	 */
	function getAssocListAll($start="", $max="")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab";
		if (is_numeric($start) && is_numeric($max)) {
			$stmt .= " LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');
		}
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
	 * Method used to get the list of controlled vocabularies available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
	 */
	function getChildListAll($start="", $max="")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
				 WHERE cvo_id not in (SELECT cvr_parent_cvo_id from  " . APP_TABLE_PREFIX . "controlled_vocab_relationship)
				 ORDER BY cvo_id ASC";
		if (is_numeric($start) && is_numeric($max)) {
			$stmt .= " LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');
		}
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
	 * Method used to get the list of controlled vocabularies available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
	 */
	function getAssocListByID($id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		// used by the xsd match forms
		if (!is_numeric($id)) {
			return array();
		}

		$stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
				 WHERE cvo_id = ".$db->quote($id, 'INTEGER');
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
	 * Method used to get the list of controlled vocabularies available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
	 */
	function getListByID($id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab
				 WHERE cvo_id = ".$db->quote($id, 'INTEGER');
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
	 * Method used to get the list of controlled vocabularies available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies
	 */
	function getList($parent_id=false)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "controlled_vocab_relationship
					     WHERE cvr_parent_cvo_id = ".$db->quote($parent_id, 'INTEGER')." AND cvr_child_cvo_id = cvo_id ";			
		} else {
			$stmt .= " WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
		}
		$stmt .= "
                 ORDER BY
                    cvo_title ASC";
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
	 * Method used to get the list of controlled vocabularies available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies
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
                    cvo_id, ";
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= "cvo_title || ' ' || cvo_desc as cvo_title ";
		} else {
			$stmt .= "CONCAT(cvo_title, ' ', cvo_desc) as cvo_title ";			
		}
    $stmt .= "FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "controlled_vocab_relationship
					     WHERE cvr_parent_cvo_id = ".$db->quote($parent_id, 'INTEGER')." AND cvr_child_cvo_id = cvo_id ";			
		} else {
			$stmt .= " WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
		}
		$stmt .= "
                 ORDER BY
                    cvo_title ASC";
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
				$newArray[$key] = $data;
				$tempArray = Controlled_Vocab::getAssocListFullDisplay($key, $indent, $level, $level_limit);
				if (count($tempArray) > 0) {
					$newArray['data'][$key] = Misc::array_merge_preserve(@$newArray[$key], $tempArray);
					$newArray['title'][$key] = $data;
				}
			}
			$res = $newArray;
			return $res;
		}
	}



	/**
	 * Method used to get the associative list of controlled vocabularies available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies
	 */
	function getParentAssocListFullDisplay($child_id, $indent="")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($child_id)) {
	 		return array();
	 	}
		$stmt = "SELECT
                    cvo_id, ";
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= "cvo_title || ' ' || cvo_desc as cvo_title ";
		} else {
			$stmt .= "CONCAT(cvo_title, ' ', cvo_desc) as cvo_title ";			
		}
    $stmt .= "FROM
	                    " . APP_TABLE_PREFIX . "controlled_vocab ";
		$stmt .=   "," . APP_TABLE_PREFIX . "controlled_vocab_relationship
						     WHERE cvr_parent_cvo_id = cvo_id AND cvr_child_cvo_id = ".$db->quote($child_id, 'INTEGER');			
		$stmt .= " ORDER BY cvo_title ASC";
		
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
				$tempArray = Controlled_Vocab::getParentAssocListFullDisplay($key, $indent);
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
	 * Method used to get the list of controlled vocabularies available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of controlled vocabularies
	 */
	function getParentListFullDisplay($child_id, $indent="")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    cvo_id, ";
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= $db->quote($indent)." || ' ' || cvo_title as cvo_title ";
		} else {
			$stmt .= "CONCAT(".$db->quote($indent).", ' ', cvo_title) as cvo_title ";			
		}
    $stmt .= " FROM " . APP_TABLE_PREFIX . "controlled_vocab ";
		$stmt .=   "," . APP_TABLE_PREFIX . "controlled_vocab_relationship
					     WHERE cvr_parent_cvo_id = cvo_id AND cvr_child_cvo_id = ".$db->quote($child_id, 'INTEGER');			
		$stmt .= " ORDER BY cvo_title ASC";
		
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
				$tempArray = Controlled_Vocab::getParentListFullDisplay($key, $indent);
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
	 * @return  array The list of controlled vocabularies
	 */
	function getAllTreeIDs($parent_id=false) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT cvo_id
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab ";
		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "controlled_vocab_relationship
						 WHERE cvr_parent_cvo_id = ".$db->quote($parent_id, 'INTEGER')." AND cvr_child_cvo_id = cvo_id ";			
		} else {
			$stmt .= " WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
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
			$tempArray = Controlled_Vocab::getAllTreeIDs($row['cvo_id']);
			if (count($tempArray) > 0) {
				$newArray[$row['cvo_id']] = $tempArray;
			} else {
				$newArray[$row['cvo_id']] = $row['cvo_id'];
			}
		}
		return $newArray;
	}


	/**
	 * Method used to get the details of a specific controlled vocabulary.
	 *
	 * @access  public
	 * @param   integer $cvo_id The controlled vocabulary ID
	 * @return  array The controlled vocabulary details
	 */
	function getDetails($cvo_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "controlled_vocab 
                 		LEFT JOIN " . APP_TABLE_PREFIX . "controlled_vocab_relationship ON 
						cvr_child_cvo_id = cvo_id";		
		
		if ($cvo_id != "") {
			$stmt .= " WHERE cvo_id=" . $db->quote($cvo_id, 'INTEGER');
		}
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		$log->debug($res);
		return $res;
	}

	function najaxGetMeta()
	{
		NAJAX_Client::mapMethods($this, array('getTitle' ));
		NAJAX_Client::publicMethods($this, array('getTitle'));
	}

	// TODO: Refactor
	// AM: Really need to look at the way we store controlled vocabs. Converting from the current adjacency list model
	// to a nested set model is one possible solution. What we want to achieve is easy reads at the cost of more 
	// expensive writes.
	public static function getVisibleCvs($parent_id = '') 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$cv = array();
		$parent_cvo_ids = array();
		$username = Auth::getUsername();
		$isAdministrator = User::isUserAdministrator($username);
		
		if (is_numeric($parent_id)) {
			// Get all of the controlled_vocabularies with this parent
			$stmt = "SELECT cvo_id
	                 FROM
	                    " . APP_TABLE_PREFIX . "controlled_vocab,			
						" . APP_TABLE_PREFIX . "controlled_vocab_relationship WHERE ";
			if ($isAdministrator != true) {
				$stmt .= " cvo_hide != 1 AND ";
			}
			$stmt .= "
							cvr_parent_cvo_id = ".$db->quote($parent_id, 'INTEGER')." AND cvr_child_cvo_id = cvo_id ";			
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			
			foreach ($res as $row) {
				$cv[$row['cvo_id']] = $row['cvo_id'];
			}
			
			// Get all of the controlled_vocabularies with children
			$stmt = "SELECT cvo_id
	                 FROM
	                    " . APP_TABLE_PREFIX . "controlled_vocab, 
														" . APP_TABLE_PREFIX . "controlled_vocab_relationship WHERE ";
			if ($isAdministrator != true) {
				$stmt .= " cvo_hide != 1 AND ";
			}
			$stmt .= "
							  cvr_parent_cvo_id = ".$db->quote($parent_id, 'INTEGER')." AND cvr_child_cvo_id = cvo_id  AND cvo_id in (SELECT cvr_parent_cvo_id from " . APP_TABLE_PREFIX . "controlled_vocab_relationship)";			
			
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
			}
			
			foreach ($res as $row) {
				$parent_cvo_ids[] = $row['cvo_id'];
			}
			
		}
		else {
			$stmt = "SELECT cvo_id
	                 FROM
	                    " . APP_TABLE_PREFIX . "controlled_vocab WHERE ";
	
			if ($isAdministrator != true) {
				$stmt .= " cvo_hide != 1 AND ";
			}
			$stmt .= " cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			foreach ($res as $row) {
				$cv[$row['cvo_id']] = $row['cvo_id'];
				$parent_cvo_ids[] = $row['cvo_id'];
			}
		}		
		
		// Get the visible children for each parent controlled_vocab
		foreach ($parent_cvo_ids as $cvo_id) {
			$cv[$cvo_id] = Controlled_Vocab::getVisibleCvs($cvo_id);
		}
		return array_values(Misc::array_flatten($cv, '', false));
	}

	/**
	 * Method used to assemble the CV tree in YUI treeview form, as an array.
	 *
	 * @access  public
	 * @param   $parentID get a one level based on the parent
     * @param   $allLevels get all levels based on the parent
	 * @return  array The JavaScript tree creation statements
	 */
	function buildCVtree($parentID = false, $allLevels = false)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$username = Auth::getUsername();
		$isAdministrator = User::isUserAdministrator($username);

		$cache_key = 'buildCVtree';		
		if ($isAdministrator) {
			$cache_key .= "_admin";
		}
		$cache_key .= "_" . $parentID; // Make each cached CV tree unique!
		
		$cvTree = array();
		$cache = FezCache::load(Controlled_Vocab::CACHE_KEY);

		if($cache && array_key_exists($cache_key, $cache)) {
			return $cache[$cache_key];
		}
		else if(! $cache) {
			$cache = array();
		}
	
		$visible_cv_ids = Controlled_Vocab::getVisibleCvs();

		$where = '';
		if (is_numeric($parentID)) {
            if (!$allLevels) {
                $where = "WHERE cvr_parent_cvo_id = " . $db->quote($parentID) . " " .
                         "OR cvo_id = " . $db->quote($parentID) . " ";
            } else
            {
                $children = implode(",", Controlled_Vocab::getAllChildren($parentID));
                $where = "WHERE cvo_id IN (".$children.") ";
            }
        }
		
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt = "SELECT cvo_id, cvo_title, cvo_hide, cvo_title || ' ' || cvo_desc as cvo_title_extended, cvr_parent_cvo_id as cvo_parent_id ";
		} else {
			$stmt = "SELECT cvo_id, cvo_title, cvo_hide, CONCAT(cvo_title, ' ', cvo_desc) as cvo_title_extended, cvr_parent_cvo_id as cvo_parent_id ";
		}
		$stmt .=
				"FROM " . APP_TABLE_PREFIX . "controlled_vocab AS t1 " .
				"LEFT JOIN " .
				"(SELECT cvr_parent_cvo_id, cvr_child_cvo_id " .
				"FROM " . APP_TABLE_PREFIX . "controlled_vocab_relationship) AS t2 " .
				"ON t1.cvo_id = t2.cvr_child_cvo_id " .
				$where .
				"ORDER BY cvo_id ASC";

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		foreach ($res as $row) {
			if (is_numeric($parentID)) {
				if (is_null($row['cvo_parent_id'])) {
					array_push($cvTree, "var tmpNode".$row['cvo_id']." = new YAHOO.widget.TextNode('" . addslashes($row['cvo_title']) . "', tree.getRoot(), false);");
				} else {				
					array_push($cvTree, "var tmpNode".$row['cvo_id']." = new YAHOO.widget.TextNode('<a href=\"javascript:addItemToParent(" . $row['cvo_id'] . ", \'" . addslashes(addslashes($row['cvo_title_extended'])) . "\');\">" . addslashes($row['cvo_title_extended']) . "</a>', tmpNode" . $row['cvo_parent_id'] . ", false);");
				}
			} else {
				if (is_null($row['cvo_parent_id'])) {
					if(($row['cvo_hide'] != '1' || $isAdministrator == true) && (in_array($row['cvo_id'], $visible_cv_ids))) {
						array_push($cvTree, "var tmpNode".$row['cvo_id']." = new YAHOO.widget.TextNode('" . addslashes($row['cvo_title']) . "', tree.getRoot(), false);");
					}
				} else {				
					if(($row['cvo_hide'] != '1' || $isAdministrator == true)  &&  (in_array($row['cvo_id'], $visible_cv_ids))) {
						array_push($cvTree, "var tmpNode".$row['cvo_id']." = new YAHOO.widget.TextNode('<a href=\"javascript:addItemToParent(" . $row['cvo_id'] . ", \'" . addslashes(addslashes($row['cvo_title_extended'])) . "\');\">" . addslashes($row['cvo_title_extended']) . "</a>', tmpNode" . $row['cvo_parent_id'] . ", false);");
					}
				}
			}
		}

		$cache[$cache_key] = $cvTree;
		FezCache::save($cache, Controlled_Vocab::CACHE_KEY);
		
		return $cvTree;
	}

    /**
   	 * Recussive function to find all children of CVO.
   	 *
   	 * @access  public
   	 * @param   $cvo to start at
   	 * @return  array children
   	 */
    function getAllChildren($cvo_ids, $includeParents='true')
    {
        $parents = $cvo_ids;
        if (is_numeric($cvo_ids)) {
            $parents = array($cvo_ids);
        }

        $parentsList = implode(",", $parents);

        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = " SELECT cvr_child_cvo_id
                  FROM " . APP_TABLE_PREFIX . "controlled_vocab
                  INNER JOIN " . APP_TABLE_PREFIX . "controlled_vocab_relationship
                  ON cvo_id = cvr_parent_cvo_id
                  WHERE cvo_id IN (".$parentsList.")";
        try {
      			$res = $db->fetchCol($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }
        $children = $res;
        if ($includeParents) {
            $children  = array_merge($res, $parents);
        }
        if (!empty($res)) {
            $childrensChildren = Controlled_Vocab::getAllChildren($res);
            if (is_array($childrensChildren)) {
                $children = array_merge($children, $childrensChildren);
            }
        }

        return $children;

    }
	function suggest($value, $parent_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = " SELECT cvo_id, cvo_title
                  FROM " . APP_TABLE_PREFIX . "controlled_vocab
                  WHERE (cvo_hide != 1 AND cvo_external_id LIKE ".$db->quote("%$value%")." OR cvo_title LIKE ".$db->quote("%$value%").")"; //, cvo_title
        if (is_numeric($parent_id)){
            $children = implode(",", Controlled_Vocab::getAllChildren($parent_id, false));
            $stmt .=  " AND cvo_id IN (".$children.") ";
        }

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
	 * Method used to produce the YUI treeview-ready JavaScript as a printable string.
	 *
	 * @access  public
	 * @param   array The JavaScript tree creation statements
	 * @return  string The JavaScript tree creation statements
	 */
	function renderCVtree($cvTreeArray) 
	{
		$output = "";
		foreach ($cvTreeArray as $cvThing) {
			$output .= $cvThing . "\n";
		}

		return $output;
	}

}
