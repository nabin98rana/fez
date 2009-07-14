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
 * Class to handle the author -> organisational structure relationship.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 * @author Lachlan Kuhn <l.kuhn@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");

class Author_Org
{
	/**
	 * Method used to get the list of organisations associated with a given author ID.
	 *
	 * @access  public
	 * @param   integer $aut_id The author ID
	 * @return  array The organisational membership details
	 */
	function getList($aut_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		static $returns;

		if (!empty($returns[$aut_id])) {
			return $returns[$aut_id];
		}

		$stmt = "SELECT
                    auo_id, auo_assessed, auo_assessed_year, org_ext_table, org_title 
                 FROM
                    " . APP_TABLE_PREFIX . "author_org_structure, 
                    " . APP_TABLE_PREFIX . "org_structure
                 WHERE auo_org_id = org_id 
                 AND auo_aut_id = " . $db->quote($aut_id, 'INTEGER') . " 
                 ORDER BY
                    org_title";

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}

		if (empty($res)) {
			return array();
		} else {
			return $res;
		}
	}


	/**
	 * Method used to get the details of a organisational membership for a given organisational membership relation ID.
	 *
	 * @access  public
	 * @param   integer $rel_id The organisational membership ID
	 * @return  array The details for the organisational membership
	 */
	function getDetails($rel_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "author_org_structure
                 WHERE
                    auo_id = ".$db->quote($rel_id, 'INTEGER');

		try {
			$res = $db->fetchRow($stmt, array($ahs_id));
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	/**
	 * Method used to add a new organisational membership to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 or -2 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		// Run the 'isWhitespace' check on all required fields, in case JavaScript has been turned off.
		if (Validation::isWhitespace($_POST["organisation_id"]) || Validation::isWhitespace($_POST["classification_id"]) || Validation::isWhitespace($_POST["function_id"])) {
			return -1;
		}

		// Perform some basic null-setting clean-up.
		empty($_POST["assessed"]) ? $assessed_val = null : $assessed_val = $_POST["assessed"];
		empty($_POST["assessed_year"]) ? $assessed_year_val = null : $assessed_year_val = $_POST["assessed_year"];

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "author_org_structure
                 (
                    auo_org_id,
                    auo_aut_id,
                    auo_cla_id,
                    auo_fun_id,
                    auo_assessed,
                    auo_assessed_year
                ) VALUES (
                    " . $db->quote($_POST["organisation_id"], 'INTEGER') . ",
                    " . $db->quote($_POST["author_id"], 'INTEGER') . ",
                    " . $db->quote($_POST["classification_id"], 'INTEGER') . ",
                    " . $db->quote($_POST["function_id"], 'INTEGER') . ",
                    " . $db->quote($assessed_val) . ",
                    " . $db->quote($assessed_year_val) . "
                 )";
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
	 * Method used to update the details of the organisational membership.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 otherwise
	 */
	function update()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (Validation::isWhitespace($_POST["organisation_id"]) || Validation::isWhitespace($_POST["classification_id"]) || Validation::isWhitespace($_POST["function_id"])) {
			return -1;
		}

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "author_org_structure
                 SET
                    auo_org_id=" . $db->quote($_POST["organisation_id"], 'INTEGER') . ",
                    auo_aut_id=" . $db->quote($_POST["author_id"], 'INTEGER') . ",
                    auo_cla_id=" . $db->quote($_POST["classification_id"], 'INTEGER') . ",
                    auo_fun_id=" . $db->quote($_POST["function_id"], 'INTEGER') . ",
                    auo_assessed=" . $db->quote($_POST["assessed"]) . ",
                    auo_assessed_year=" . $db->quote($_POST["assessed_year"]) . " 
                 WHERE
                    auo_id=" . $db->quote($_POST["id"], 'INTEGER');
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
	 * Method used to remove a given set of author-organisation relationships from the system.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove() 
	{	
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "author_org_structure
                 WHERE
                    auo_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
		
		try {
			$db->query($stmt, $_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}
		return true;
	}
}
