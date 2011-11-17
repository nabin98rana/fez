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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.exiftool.php");
include_once(APP_INC_PATH . "class.fedora_direct_access.php");
include_once(APP_INC_PATH . "class.session.php");


//This will update Exif data on all objects if it is not considered the latest version hard coded below, current 8.5
$isUser = Auth::getUsername();
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {
    $db = DB_API::get();
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $stmt =  "SELECT exif_pid, exif_dsid " .
                "FROM {$dbtp}exif WHERE `exif_all` NOT LIKE 'ExifTool Version Number         : 8.50%'";

    try {
      $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    $fda = new Fedora_Direct_Access();
    foreach($res as $row) {
        $pid = $row['exif_pid'];
        $dsID = $row['exif_dsid'];
        $databaseDetails = Exiftool::getDetails($pid,$dsID);
        $dsVersionID = $fda->getMaxDatastreamVersion($pid, $dsID);
        $path = $fda->getDatastreamManagedContentPath($pid, $dsID, $dsVersionID);
        $newDetails = Exiftool::extractMetadata($path);
        $newDetails['pid'] = $pid;
        $newDetails['dsid'] = $dsID;
        Exiftool::remove($pid, $dsID);
        Exiftool::insert($newDetails);
        echo $pid."<br />";
        ob_end_flush();
        ob_flush();
        flush();
        ob_start();
    }
    echo "<br />Done";
} else {
    echo "Please run from command line or logged in as a super user";
}