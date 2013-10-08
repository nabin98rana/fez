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
 * Misc function to re create cache after a delete, to try and ensure it's recreated at a time when normal usage is low as well as doing it slowly
 * User should be aware it 'might' effect view statistics
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
    $numberOfCachedPidsToProcess = 300000;

    // Get all PIDs
    $stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key";
    try {
        $res = $db->fetchCol($stmt);
    } catch (Exception $ex) {
        $log->err($ex);
        echo "Failed to retrieve pid data. Error: " . $ex;
    }

    //By getting robot file Fez adds us to the statistics robot section and won't effect views
    $fetch = 'curl '.APP_HOSTNAME.'/robots.txt  > /dev/null';
    exec($fetch, $outputError);
    if ($outputError) {
        echo 'Error: ' . $outputError;
    }

    $i = 0;
    foreach ($res as $pid) {
        //We'll stop for .5 of a second each pid to ensure we don't overload the system
        usleep(500000);
        $cache = new fileCache($pid, 'pid='.$pid);
        if ($cache->checkCacheFileExists()) {
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
        } else {
            echo 'Cache Exists: '.$pid;
        }
        flush();
        ob_flush();
    }
    echo 'Fini';
} else {
    echo "Please run from command line or logged in as a super user";
}