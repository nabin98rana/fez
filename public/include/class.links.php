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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . "class.error_handler.php");


class Links
{

   /**
 	 * Get the complete list of links.
 	 */
 	function getLinks($pid)
 	{
         if (empty($pid)) {
             return false;
         }
 		$log = FezLog::get();
 		$db = DB_API::get();

 		$stmt = "
 			SELECT
 			    rek_link, rek_link_description
 			FROM
 				" . APP_TABLE_PREFIX . "record_search_key_link INNER JOIN
 				" . APP_TABLE_PREFIX . "record_search_key_link_description
 		    ON rek_link_order = rek_link_description_order
 			WHERE
 			  rek_link_pid = ".$db->quote($pid)."
 			AND rek_link_description_pid = ".$db->quote($pid);
 		try {
 			$res = $db->fetchAll($stmt);
 		}
 		catch(Exception $ex) {
 			$log->err($ex);
 			return '';
 		}

 		return $res;
 	}


}
