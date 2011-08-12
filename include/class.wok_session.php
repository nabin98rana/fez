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
 * Class to handle the TR web services premium client session to perpetuate it as long as possible to avoid throttling.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");

class WokSession
{

	/**
	 * Method used to remove a session.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "wok_session";
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
	 * Method used to add a new session.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert($session)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (empty($params)) {
			$params = $_POST;
		}

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "wok_session
                 (
                    wks_id,
                 ) VALUES (
                    " . $db->quote($session) . ",
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
	 * Method used to update details of a session.
	 *
	 * @access  public
	 * @param   text $session The session value
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($session)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "wok_session
                 SET
                    wks_id = " . $db->quote($session) . "";

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
	 * Method used to get the session value.
	 *
	 * @access  public
	 * @return  string The session value
	 */
	function get()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    wks_id
                 FROM
                    " . APP_TABLE_PREFIX . "wok_session";
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}
}