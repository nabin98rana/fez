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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+
//
// This function finds open access(Viewable while not logged in) attachments and puts them into a table for further analysis
// It has no web based interface
/*
 CREATE TABLE `fez_open_access_items` (
  `oai_id` int(11) NOT NULL AUTO_INCREMENT,
  `oai_pid` varchar(255) DEFAULT NULL,
  `oai_dsid` varchar(255) DEFAULT NULL,
  `oai_document_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`oai_id`)
)

sudo php open_access_find_items.php > /var/log/open_access_find_items.log

ie. finding Theses that are open access
SELECT oai_pid, oai_dsid, oai_document_type FROM fez_open_access_items WHERE oai_document_type = 'thesis' AND oai_dsid LIKE '%.pdf' GROUP BY oai_pid

*/

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.auth.php");
error_reporting(E_ALL & !E_NOTICE);

//We'll only allow this to be run from the command line, if it's run from the web it will find all  accessiable by the person currently logged in.
if ((php_sapi_name()==="cli") || 1) {
    set_time_limit(0);
    echo "Script started: " . date('Y-m-d H:i:s') . "\n";
    echo "--------------------------\n";

    $db = DB_API::get();
    $log = FezLog::get();
    $isUser = Auth::getUsername();

    /*$query = "DELETE FROM " . APP_TABLE_PREFIX . "open_access_items";
    try {
        $db->exec($query);
    }
    catch (Exception $ex) {
        null; // don't do anything here.
    }*/

    $fedoraPids = Fedora_Direct_Access::fetchAllFedoraPIDs('','');
    //$fedoraPids[] = array('pid' => 'UQ:3875');
    $status = Status::getID("Published");
    foreach ($fedoraPids as $pid) {
        $pid = $pid['pid'];
        if ($status == Record::getSearchKeyIndexValue($pid, "Status", false)) {
            $documentType = Record::getSearchKeyIndexValue($pid, "Display Type", false);
            $datastreams = Fedora_API::callGetDatastreams($pid);
            foreach ($datastreams as $datastream) {
                if ($datastream['controlGroup'] == "M"
                    && (!Misc::hasPrefix($datastream['ID'], 'preview_')
                        && !Misc::hasPrefix($datastream['ID'], 'web_')
                        && !Misc::hasPrefix($datastream['ID'], 'thumbnail_')
                        && !Misc::hasPrefix($datastream['ID'], 'stream_')
                        && !Misc::hasPrefix($datastream['ID'], 'presmd_'))) {

                    if (!inDatabase($pid, $datastream['ID'])) {
                        $userPIDAuthGroups = Auth::getAuthorisationGroups($pid, $datastream['ID']);
                        if (in_array('Viewer', $userPIDAuthGroups)) {
                            saveOpenAccessItemInfo($pid, $datastream['ID'], $documentType, $datastream['createDate'], $datastream['label'], $isUser);
                            $open = true;

                        }
                    }
                }
            }
        }

        if ($open) {
            $open = false;
            echo "--------------------------\n";
        }
    }

    echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
    echo "--------------------------\n";

} else {
    echo "Must be run from the command line";
}

function saveOpenAccessItemInfo($pid, $dsID, $documentType, $created, $label, $isUser) {
    $log = FezLog::get();
    $db = DB_API::get();
    $stmt = "INSERT INTO ".APP_TABLE_PREFIX."open_access_items  (oai_pid, oai_dsid, oai_document_type, oai_created, oai_label, oai_user)
                VALUES (".$db->quote($pid).", ".$db->quote($dsID).", ".$db->quote($documentType).", ".$db->quote($created).", ".$db->quote($label).", ".$db->quote($isUser).")";
    try {
        $res = $db->exec($stmt);
    }
    catch(Exception $ex) {
        echo $ex;
    }
    echo $pid." ". $dsID . " ". $documentType."\n";
}

function inDatabase($pid, $dsID) {
    $log = FezLog::get();
    $db = DB_API::get();
    $stmt = "SELECT oai_pid FROM ".APP_TABLE_PREFIX."open_access_items WHERE oai_pid = ".$db->quote($pid)." AND oai_dsid = ".$db->quote($dsID);
    try {
        $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
        echo $ex;
    }
    return !empty($res);

}