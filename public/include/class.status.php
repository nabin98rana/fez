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
 * Class to handle system object status's.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");

class Status
{

	/**
	 * Method used to remove a given list of statuss.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "status
                 WHERE
                    sta_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
		try {
			$db->query($stmt, $_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}


	/**
	 * Method used to add a new status to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert($params = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($params)) {
			$params = $_POST;
		}

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "status
                 (
                    sta_title,
					sta_order,
					sta_color
                 ) VALUES (
                    " . $db->quote($params["sta_title"]) . ",
					" . $db->quote($params["sta_order"], 'INTEGER') . ",
					" . $db->quote($params["sta_color"]) . "					
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
	 * Method used to update details of a status.
	 *
	 * @access  public
	 * @param   integer $sta_id The status ID
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($sta_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "status
                 SET 
                    sta_title = " . $db->quote($_POST["sta_title"]) . ",
					sta_order = " . $db->quote($_POST["sta_order"]) . ",
					sta_color = " . $db->quote($_POST["sta_color"]) . "
                 WHERE sta_id = " . $db->quote($sta_id, 'INTEGER');
		
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
	 * Method used to get the title of a specific status.
	 *
	 * @access  public
	 * @param   integer $sta_id The status ID
	 * @return  string The title of the status
	 */
	function getTitle($sta_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sta_title
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 WHERE
                    sta_id=".$db->quote($sta_id, 'INTEGER');
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
	 * Method used to get the ID of a specific status.
	 *
	 * @access  public
	 * @param   integer $sta_title The status title
	 * @return  string The ID of the status
	 */
	function getID($sta_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sta_id
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 WHERE
                    sta_title=".$db->quote($sta_title);
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
	 * Method used to get the list of statuss available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of statuss in an associative array (for drop down lists).
	 */
	function getAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sta_id,
					sta_title
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 ORDER BY
                    sta_title ASC";
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
	 * Method used to get the list of statuss available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of statuss in an associative array (for drop down lists).
	 */
	function getUnpublishedAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sta_id,
					sta_title
                 FROM
                    " . APP_TABLE_PREFIX . "status
				 WHERE sta_title != 'Published' 
                 ORDER BY
                    sta_title ASC";
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
	 * Method used to get the list of statuss available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of statuss
	 */
	function getList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 ORDER BY
                    sta_order ASC";
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
	 * Method used to get the details of a specific status.
	 *
	 * @access  public
	 * @param   integer $sta_id The status ID
	 * @return  array The status details
	 */
	function getDetails($sta_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 WHERE
                    sta_id=".$db->quote($sta_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;		
	}

}
