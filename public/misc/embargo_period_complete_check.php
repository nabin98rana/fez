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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
/**
 * make all embargoed documents public if the embargo date is passed
 *
 * @version 1.0
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 */
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.filecache.php");
set_time_limit(0);
$log = FezLog::get();

//This needs to be changed if the templates are rearranged.
$templateNumber = 7;
if (str_replace(array("\n", "\r", "\t", " "), '', FezACML::getQuickTemplateValue($templateNumber)) != "<FezACML><inherit_security>on</inherit_security></FezACML>") {
    $log->err("!!!!!!! Possible error with template in embargo Cron job !!!!!!!!!");
    echo "Error with template";
    exit;
}

$isUser = Auth::getUsername();
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {
    echo "Script started: " . date('Y-m-d H:i:s') . "\n";

    $stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "datastream_info WHERE dsi_embargo_date < NOW() AND dsi_embargo_processed != 1";
    try {
        $res = $db->fetchAll($stmt);
    } catch (Exception $ex) {
        $log->err($ex);
        echo "Failed to retrieve embargo data. Error: " . $ex;
    }

    foreach ($res as $pidInfo) {
        $pidPermisisons = Auth::getAuthPublic($pidInfo['dsi_pid'], $pidInfo['dsi_dsid']);
        if (!($pidPermisisons['lister'] && $pidPermisisons['viewer'])) {
            Datastream::setfezACML($pidInfo['dsi_pid'], $pidInfo['dsi_dsid'], $templateNumber);
            echo $pidInfo['dsi_pid']." ".$pidInfo['dsi_dsid']." \n";
            if (APP_FILECACHE == "ON") {
                $cache = new fileCache($pidInfo['dsi_pid'], 'pid='.$pidInfo['dsi_pid']);
                $cache->poisonCache();
            }
        }
        Datastream::setEmbargoProcessed($pidInfo['dsi_pid'], $pidInfo['dsi_dsid']);
        History::addHistory($pidInfo['dsi_pid'], null, '', '', true, 'Embargo was found to be complete for '.$pidInfo['dsi_dsid'].' and was made public via cron job');
    }

    echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "Please run from command line or logged in as a super user";
}