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
include_once(APP_INC_PATH . "class.date.php");


class News
{

	/**
	 * Method used to inspect the supplied data fields, and report any problems back
	 * to the user prior to insert / update operation being performed.
	 *
	 * @access  public
	 * @return  integer 1 if data OK, -N otherwise
	 */
	function checkFieldData()
	{
		// Check for non-supplied field data
		if (Validation::isWhitespace($_POST["title"])) {
			return -2;
		}
		if (Validation::isWhitespace($_POST["message"])) {
			return -3;
		}
		// Check for field data that exceeds the length of underlying DB field limits
		if (strlen($_POST["title"]) > 255) {
			return -4;
		}
		if (strlen($_POST["message"]) > 65535) {
			return -5;
		}
		return 1;
	}


	/**
	 * Method used to add a news entry to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$checkResult = News::checkFieldData();
		if ($checkResult !== 1) {
			return $checkResult;
		}

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "news
                 (
                    nws_usr_id,
                    nws_created_date,
                    nws_title,
                    nws_message,";
		if ($_POST["status"] == "active") {
			$stmt .= "nws_published_date,";
		}
		$stmt .= "
                    nws_status
                 ) VALUES (
                    " . $db->quote(Auth::getUserID(), 'INTEGER') . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    " . $db->quote($_POST["title"]) . ",
                    " . $db->quote($_POST["message"]) . ",";
		if ($_POST["status"] == "active") {
			$stmt .= "
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",";
		}

		$stmt .=  $db->quote($_POST["status"]) . ")";
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
	 * Method used to remove a news entry from the system.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "news
                 WHERE
                    nws_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
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
	 * Method used to update a news entry in the system.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 otherwise
	 */
	function update()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$checkResult = News::checkFieldData();
		if ($checkResult !== 1) {
			return $checkResult;
		}

		// get existing details for the publish date condition
		$existing_res = News::getDetails($_POST["id"]);

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "news
                 SET
                    nws_title=" . $db->quote($_POST["title"]) . ",
                    nws_message=" . $db->quote($_POST["message"]) . ",
                    nws_status=" . $db->quote($_POST["status"]) . ",
					";
		if (($_POST["status"] == "active") && ($existing_res['published_date'] != '0000-00-00 00:00:00')) {
			$stmt .= "
					nws_published_date = " . $db->quote(Date_API::getCurrentDateGMT()) . ",";
		}
		$stmt .= "
                    nws_updated_date=" . $db->quote(Date_API::getCurrentDateGMT()) . "					
                 WHERE
                    nws_id=" . $db->quote($_POST["id"], 'INTEGER');
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
	 * Method used to get the details of a news entry for a given news ID.
	 *
	 * @access  public
	 * @param   integer $nws_id The news entry ID
	 * @return  array The news entry details
	 */
	function getDetails($nws_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "news
                 WHERE
                    nws_id=".$db->quote($nws_id, 'INTEGER');
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
	 * Method used to get the list of news entries available in the system.
	 *
	 * @access  public
	 * @return  array The list of news entries
	 */
	function getList($maxPosts = 999999)
	{
		$log = FezLog::get();
		$db = DB_API::get();
				
		$stmt = "SELECT
					*
                 FROM
                    " . APP_TABLE_PREFIX . "news,
                    " . APP_TABLE_PREFIX . "user					
                 WHERE nws_status = 'active' and usr_id = nws_usr_id
                 ORDER BY
                    nws_created_date DESC
                 LIMIT " . $db->quote($maxPosts, 'INTEGER');
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		// Avoid multiple calls when getting timezone from the DB
		$timezone = Date_API::getPreferredTimezone();
		
		foreach ($res as $key => $row) {
			$res[$key]["nws_created_date"] = Date_API::getFormattedDate($res[$key]["nws_created_date"], $timezone);
			$res[$key]["nws_updated_date"] = Date_API::getFormattedDate($res[$key]["nws_updated_date"], $timezone);
			$res[$key]["nws_published_date"] = Date_API::getFormattedDate($res[$key]["nws_published_date"], $timezone);
			//$res[$key]["nws_created_date"] = Date_API::getFormattedDate($res[$key]["nws_created_date"], APP_DEFAULT_USER_TIMEZONE);
			//$res[$key]["nws_updated_date"] = Date_API::getFormattedDate($res[$key]["nws_updated_date"], APP_DEFAULT_USER_TIMEZONE);
			//$res[$key]["nws_published_date"] = Date_API::getFormattedDate($res[$key]["nws_published_date"], APP_DEFAULT_USER_TIMEZONE);
		}
		return $res;
	}

	/**
	 * Method used to get the list of news entries available in the system.
	 *
	 * @access  public
	 * @return  array The list of news entries
	 */
	function getListAll()
	{	
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
					*
                 FROM
                    " . APP_TABLE_PREFIX . "news,
                    " . APP_TABLE_PREFIX . "user					
				 WHERE usr_id = nws_usr_id
                 ORDER BY
                    nws_created_date DESC";
		try {	
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		$timezone = Date_API::getPreferredTimezone();
		
		foreach ($res as $key => $row) {
			$res[$key]["nws_created_date"] = Date_API::getFormattedDate($res[$key]["nws_created_date"], $timezone);
			$res[$key]["nws_updated_date"] = Date_API::getFormattedDate($res[$key]["nws_updated_date"], $timezone);
			$res[$key]["nws_published_date"] = Date_API::getFormattedDate($res[$key]["nws_published_date"], $timezone);
		}
		return $res;
	}
}
