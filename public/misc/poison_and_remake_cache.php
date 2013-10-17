<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007, 2008 The University of Queensland,   |
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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>       |
// +----------------------------------------------------------------------+
//
//

/**
 * Misc function to poison the oldest cache and re create it
 * User should be aware it might effect view statistics
 * It will not delete orphan cache files (Cache files without pids)
 *
 * @version 1.0
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 */
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.filecache.php");
set_time_limit(0);
$isUser = Auth::getUsername();
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {

    //This is a limit on how many file to check for re caching each time this is run.
    $numberOfCachedPidsToProcess = 500000;

    // Get all PIDs and md5 them with key to create a reverse look up table
    $stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key";
    try {
        $res = $db->fetchCol($stmt);
    } catch (Exception $ex) {
        $log->err($ex);
        echo "Failed to retrieve pid data. Error: " . $ex;
    }
    $deletedPids = getDeletedPids();
    $res = array_merge($res, $deletedPids);
    $md5s = array();
    foreach ($res as $pid) {
        $md5s[md5('pid='.$pid)] = $pid;
    }

    //By getting robot file Fez adds us to the statistics robot section and won't effect views
    $fetch = 'curl '.APP_HOSTNAME.'/robots.txt  > /dev/null';
    exec($fetch, $outputError);
    if ($outputError) {
        echo 'Error: ' . $outputError;
    }


    //find the files in order of age
    //$command = 'find '.APP_FILECACHE_DIR.APP_HOSTNAME.' -type f -exec ls -1rt "{}" +';
    $command = 'find '.APP_FILECACHE_DIR.APP_HOSTNAME.' -type f -printf "%f,%T@ \n"';
    exec($command, $outputFiles);
    foreach ($outputFiles as $outputLine) {
        $filesAndDate[] = explode(',', $outputLine);
    }

    //Sort by unix time early to latter
    usort($filesAndDate, function ($a, $b) {
        return strcmp($a[1], $b[1]);
    });


    $i = 0;
    foreach ($filesAndDate as $file) {
        $hash = $file[0]; //, count($file)- 33 ,32);
        $pid = $md5s[$hash];
        if ($pid) {

            //We'll stop for a second each pid to ensure we don't overload the system
            sleep(1);
            $cache = new fileCache($pid, 'pid='.$pid);
            $cache->poisonCache();

            $fetch = 'curl '.APP_HOSTNAME.'/view/'.$pid.'  > /dev/null';
            exec($fetch, $outputError);
            if ($outputError) {
                echo 'Error: ' . $outputError;
            }
            $i++;
            if ($i > $numberOfCachedPidsToProcess) {
                break;
            }
            echo 'Done: '.$pid;
            flush();
            ob_flush();
        }
    }


    echo 'Fini';
} else {
    echo "Please run from command line or logged in as a super user";
}

function getDeletedPids() {
    $fedoraDirect = new Fedora_Direct_Access();
    $fedoraList = $fedoraDirect->fetchAllFedoraPIDs('*', 'D');

    // Correct for Oracle-produced array key case issue reported by Kostas
    foreach ($fedoraList as $fkey => $flist) {
        $fedoraList[$fkey] = array_change_key_case($flist, CASE_LOWER);
    }

    // Extract just the PIDs
    $fedoraPIDs = array();
    foreach ($fedoraList as $flist) {
        array_push($fedoraPIDs, $flist['pid']);
    }
    return $fedoraPIDs;
}