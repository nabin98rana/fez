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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

require_once(APP_INC_PATH . "class.auth.php");

class Favourites
{
	/**
	 * star
	 *
	 * This function adds a star to a record for the current user.
	 */
	function star($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$user = Auth::getUsername();
		
		$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "favourites
				(
					fvt_pid,
					fvt_username
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($user) . ");"
				;
		try {
			$db->exec($stmt);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return true;
	}
	
	
	
	/**
	 * unstar
	 *
	 * This function removes a star from a record for the current user.
	 */
	function unstar($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$user = Auth::getUsername();
		
		$stmt = "DELETE FROM
					" . APP_TABLE_PREFIX . "favourites
				WHERE
					fvt_pid = " . $db->quote($pid) . "
					AND fvt_username = " . $db->quote($user)
				;
		try {
			$db->exec($stmt);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return true;
	}
	
	
	
	/**
	 * getStarred
	 *
	 * This function returns an array of PIDs that the current user has starred.
	 */
	function getStarred()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$user = Auth::getUsername();
		
		$stmt = "
				SELECT
					fvt_pid
				FROM
					" . APP_TABLE_PREFIX . "favourites
				WHERE
					fvt_username = " . $db->quote($user)
				;
		
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return $res;
	}
	
}
