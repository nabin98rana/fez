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


    /**
   	 * starSearch
   	 *
   	 * This function adds a star to the search for the current user.
   	 */
   	function starSearch($searchParameters, $description=null)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		$user = Auth::getUsername();

   		$stmt = "INSERT INTO
   					" . APP_TABLE_PREFIX . "favourites_search
   				(
   					fvs_search_parameters,
                    fvs_description,
   					fvs_username

   				) VALUES (
   					" . $db->quote($searchParameters) . ",
   					" . $db->quote($description) . ",
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
   	 * unstarSearch
   	 *
   	 * This function removes a star from a search for the current user.
   	 */
   	function unstarSearch($searchParameters)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		$user = Auth::getUsername();

   		$stmt = "DELETE FROM
   					" . APP_TABLE_PREFIX . "favourites_search
   				WHERE
   					fvs_search_parameters = " . $db->quote($searchParameters) . "
   					AND fvs_username = " . $db->quote($user)
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
   	 * getStarredSearches
   	 *
   	 * This function returns an array of searches that the current user has starred.
   	 */
   	function getStarredSearches()
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		$user = Auth::getUsername();

   		$stmt = "
   				SELECT
   					fvs_search_parameters, fvs_alias, fvs_email_me, fvs_id, fvs_description
   				FROM
   					" . APP_TABLE_PREFIX . "favourites_search
   				WHERE
   					fvs_username = " . $db->quote($user)
   				;

   		try {
   			$res = $db->fetchAll($stmt);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return false;
   		}

   		return $res;
   	}

    function isStarredSearch($searchParameters)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		$user = Auth::getUsername();

   		$stmt = "
   				SELECT
   					COUNT(*) AS total
   				FROM
   					" . APP_TABLE_PREFIX . "favourites_search
   				WHERE
   					fvs_search_parameters = " . $db->quote($searchParameters)
   				;

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

    function getSearchAliasURL($alias)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		$stmt = "
   				SELECT
   					 fvs_search_parameters
   				FROM
   					" . APP_TABLE_PREFIX . "favourites_search
   				WHERE
   					fvs_alias = " . $db->quote($alias)
   				;

   		try {
   			$res = $db->fetchOne($stmt);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return false;

           }
        return $res;
   	}

    function getSearchAliasId($alias)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		$stmt = "
   				SELECT
   					 fvs_id
   				FROM
   					" . APP_TABLE_PREFIX . "favourites_search
   				WHERE
   					fvs_alias = " . $db->quote($alias)
   				;

   		try {
   			$res = $db->fetchOne($stmt);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return false;

           }
        return $res;
   	}
    /**
   	 * Method used to remove a given set of search favourites from the system.
   	 *
   	 * @access  public
   	 * @return  boolean
   	 */
   	function removeSearchFavourites()
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

        $user = Auth::getUsername();
   		$stmt = "DELETE FROM
                       " . APP_TABLE_PREFIX . "favourites_search
                    WHERE
                       fvs_id IN (".Misc::arrayToSQLBindStr($_POST['items']).") AND
                       fvs_username = " . $db->quote($user);
   		try {
   			$db->query($stmt, $_POST['items']);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return false;
   		}
   		return true;
   	}

    function saveSearchFavourites()
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();
        $issues = array();
        $user = Auth::getUsername();
        foreach($_POST['alias'] as $key => $value) {
            $alias = $_POST['alias'][$key];
            $emailMe = $_POST['emailme'][$key];
            $description = $_POST['description'][$key];
            $rowId = $key;
            if ((!empty($alias)) && (Favourites::isSearchAliasTaken($alias, $rowId))) {
                $issues[$rowId]['issue'] = 'taken';
                $issues[$rowId]['alias'] = $alias;
            } else {
                //If email alert it turned set date for updates from now on
                $startEmailing = "";
                if (!Favourites::isEmailAlertSet($key) && $emailMe)
                {
                    $startEmailing = ", fvs_most_recent_item_date = ". $db->quote(date('c'));
                }
                $stmt = "UPDATE
                            " . APP_TABLE_PREFIX . "favourites_search
                        SET
                            fvs_alias = " . $db->quote($alias) .",
                            fvs_email_me = " . $db->quote($emailMe) .",
                            fvs_description = " . $db->quote($description) .
                            $startEmailing ."
                        WHERE
                           fvs_id = " . $db->quote($key) ."
                           AND  fvs_username = " . $db->quote($user);
                try {
                    $db->query($stmt);
                }
                catch(Exception $ex) {
                    $log->err($ex);
                    return false;
                }
            }
        }
   	return $issues;
   	}

    function isEmailAlertSet($fvs_id)
    {
        $log = FezLog::get();
      	$db = DB_API::get();

      	$stmt = "
            SELECT
                COUNT(*) AS total
            FROM
                " . APP_TABLE_PREFIX . "favourites_search
            WHERE
                fvs_email_me = '1'  AND
                fvs_id = " . $db->quote($fvs_id);
        try {
                $res = $db->fetchOne($stmt);
            }
            catch(Exception $ex) {
                $log->err($ex);
                return false;
            }

          return ($res > 0) ? true : false;
    }

    function isSearchAliasTaken($alias, $rowId)
    {
        if (Validation::isWhitespace($alias)) {
          return true;
        }
        if (Validation::isUserFileName($alias) == true) {
          return true;
        }
        if ((Favourites::getSearchAliasId($alias)) && (Favourites::getSearchAliasId($alias) != $rowId)) {
            return true;
        }
        return false;
    }

    function savedSearches()
    {
        $log = FezLog::get();
      	$db = DB_API::get();
        $stmt = "SELECT fvs_id, fvs_search_parameters, fvs_username, fvs_most_recent_item_date, fvs_alias, usr_email
                 FROM " . APP_TABLE_PREFIX . "favourites_search
                 INNER JOIN " . APP_TABLE_PREFIX . "user
                 ON usr_username = fvs_username
                 WHERE fvs_email_me = '1' ";

        try {
      			$res = $db->fetchAll($stmt);
      		}
      		catch(Exception $ex) {
      			$log->err($ex);
      			return false;

              }
        return $res;
    }
    function updateRecentItemDateSearch($fvs_id)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

        $stmt = "UPDATE
                       " . APP_TABLE_PREFIX . "favourites_search
                   SET
                       fvs_most_recent_item_date = " . $db->quote(date('c')) ."
                   WHERE
                      fvs_id = " . $db->quote($fvs_id);
           try {
               $db->query($stmt);
           }
           catch(Exception $ex) {
               $log->err($ex);
               return false;
           }

  	return true;
  	}
}