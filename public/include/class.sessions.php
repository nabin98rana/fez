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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle non-core session-related functionality.
 *
 * @version 1.0
 * @author Lachlan Kuhn <l.kuhn@library.uq.edu.au>
 */

class Sessions {

	public function listActiveSessions($loggedInOnly = false) {
		
		$log = FezLog::get();
		$db = DB_API::get();
		
		$cond = '';
		if ($loggedInOnly) {
			$cond = 'AND user_id IS NOT NULL';
		}
		
		$stmt = '
				SELECT MAX(updated) AS updated, session_id, session_ip, created, session_data, user_id
				FROM ' . APP_TABLE_PREFIX . 'sessions
				WHERE ';
				
				if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
					$stmt .= " (updated + INTERVAL '" . APP_SESSION_TIMEOUT . " seconds') > NOW() ";
				} else {
					$stmt .= ' DATE_ADD(updated, INTERVAL ' . APP_SESSION_TIMEOUT . ' SECOND) > NOW() ';

				}
				
		$stmt .= '		
					' . $cond . '
					AND user_id != ' . APP_SYSTEM_USER_ID . '
				GROUP BY user_id
				ORDER BY updated DESC
		';
		
		try {	
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		$userIDs = array();
		foreach ($res as &$row) {
			//$unserialised = Sessions::session_real_decode($row['session_data']); // Old way
			$userIDs[] = $row['user_id'];
			$userDetails = User::getDetailsByID($row['user_id']);
			$row['fullname'] = $userDetails['usr_full_name'];
			$row['username'] = $userDetails['usr_username'];
		}

		// Get distinct users
		$distinctUserCount = sizeof(array_unique($userIDs));
		return compact('res', 'distinctUserCount');
	}
	
	
	public function deleteSession($ids) {

		$log = FezLog::get();
		$db = DB_API::get();
		
		if (sizeof($ids) == 0) {
			// Bail out if we have nothing to do.
			return;
		}

		$idList = '';
		foreach ($ids as $id) {
			if ($idList != '') {
				$idList .= ", ";
			}
			$idList .= "'" . $id . "'";
		}
		
		$stmt = "DELETE FROM " . APP_TABLE_PREFIX . "sessions WHERE session_id IN (" . $idList . ");";

		try {	
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return;
		}
		
		return true;
	} 
	
	function session_real_decode($str) {
	
		/**
		 * This function snaffled from the PHP Manual comments
		 * Source: http://au2.php.net/session_decode
		 */
		define('PS_DELIMITER', '|');
		define('PS_UNDEF_MARKER', '!');
	    
	    $str = (string)$str;
	    $endptr = strlen($str);
	    $p = 0;
	
	    $serialized = '';
	    $items = 0;
	    $level = 0;
	
	    while ($p < $endptr) {
	        $q = $p;
	        while ($str[$q] != PS_DELIMITER)
	            if (++$q >= $endptr) break 2;
	
	        if ($str[$p] == PS_UNDEF_MARKER) {
	            $p++;
	            $has_value = false;
	        } else {
	            $has_value = true;
	        }
	        
	        $name = substr($str, $p, $q - $p);
	        $q++;
	
	        $serialized .= 's:' . strlen($name) . ':"' . $name . '";';
	        
	        if ($has_value) {
	            for (;;) {
	                $p = $q;
	                switch (strtolower($str[$q])){
	                    case 'n': /* null */
	                    case 'b': /* boolean */
	                    case 'i': /* integer */
	                    case 'd': /* decimal */
	                        do $q++;
	                        while ( ($q < $endptr) && ($str[$q] != ';') );
	                        $q++;
	                        $serialized .= substr($str, $p, $q - $p);
	                        if ($level == 0) break 2;
	                        break;
	                    case 'r': /* reference  */
	                        $q+= 2;
	                        for ($id = ''; ($q < $endptr) && ($str[$q] != ';'); $q++) $id .= $str[$q];
	                        $q++;
	                        $serialized .= 'R:' . ($id + 1) . ';'; /* increment pointer because of outer array */
	                        if ($level == 0) break 2;
	                        break;
	                    case 's': /* string */
	                        $q+=2;
	                        for ($length=''; ($q < $endptr) && ($str[$q] != ':'); $q++) $length .= $str[$q];
	                        $q+=2;
	                        $q+= (int)$length + 2;
	                        $serialized .= substr($str, $p, $q - $p);
	                        if ($level == 0) break 2;
	                        break;
	                    case 'a': /* array */
	                    case 'o': /* object */
	                        do $q++;
	                        while ( ($q < $endptr) && ($str[$q] != '{') );
	                        $q++;
	                        $level++;
	                        $serialized .= substr($str, $p, $q - $p);
	                        break;
	                    case '}': /* end of array|object */
	                        $q++;
	                        $serialized .= substr($str, $p, $q - $p);
	                        if (--$level == 0) break 2;
	                        break;
	                    default:
	                        return false;
	                }
	            }
	        } else {
	            $serialized .= 'N;';
	            $q+= 2;
	        }
	        $items++;
	        $p = $q;
	    }
		
	    return @unserialize( 'a:' . $items . ':{' . $serialized . '}' );
	}

}
