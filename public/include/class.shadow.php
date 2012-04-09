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
 * Class to handle authentication and authorisation issues.
 *
 * @version 1.0
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.masquerade.php");
include_once(APP_INC_PATH . "class.auth_no_fedora_datastreams.php");


class Shadow
{
    function copySearchKeyToShadow($pid, $date, $searchKey) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "INSERT INTO ". APP_TABLE_PREFIX . "record_search_key_". $searchKey."__shadow
               SELECT *, ".$db->quote($date). " FROM ". APP_TABLE_PREFIX . "record_search_key_". $searchKey."
                        WHERE rek_".$searchKey."_pid = ".$db->quote($pid);
        try {
      			$res = $db->exec($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }
        return $res;
    }

    function copyRecordToShadow($pid, $date) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "INSERT INTO ". APP_TABLE_PREFIX . "record_search_key__shadow
               SELECT *, ".$db->quote($date). " FROM ". APP_TABLE_PREFIX . "record_search_key
                        WHERE rek_pid = ".$db->quote($pid);
        try {
      			$res = $db->exec($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }
        return $res;
    }

}

