<?php
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
// | Author: Aaron Brown <a.brown@library.uq.edu.au>                           |
// +----------------------------------------------------------------------+

/**
 * The purpose of this script is to
 * set up permisisons for all pids and datastreams. Not inherited permissions
 * need to be stored.
 * 
 * This is a one-off migration script as part of Fedora-less project.
 */
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.dsresource.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.auth_no_fedora_datastreams.php");
error_reporting(1);


// Get all PIDs without parents. Recalculate permisisons. This will filter down to child pids and child datastreams
$stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key
LEFT JOIN fez_record_search_key_ismemberof
ON rek_ismemberof_pid = rek_pid
WHERE rek_ismemberof IS NULL";

try {
    $res = $db->fetchAll($stmt);
} catch (Exception $ex) {
    $log->err($ex);
    echo "Failed to retrieve pid data. Error: " . $ex;
}

$i=0;
foreach ($res as $pid) {
    AuthNoFedora::recalculatePermissions($pid);
    echo 'Done: '.$pid.'<br />';
}