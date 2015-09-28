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
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . "class.scopus_queue.php");
include_once(APP_INC_PATH . "class.author_era_affiliations.php");


echo "Script started: " . date('Y-m-d H:i:s') . "\n";
$isUser = Auth::getUsername();
if (php_sapi_name()==="cli")  {   // This must be run while not logged in else it wil cause false issues in stage two
    $db = DB_API::get();
    $log = FezLog::get();

    //----------------------- Stage one Datastream Policy conflicts (Is it in two collections with different set datastream policies -----------------------//

    $body = '';
    $stmt =     "SELECT rek_ismemberof_pid AS pid, GROUP_CONCAT(rek_ismemberof)  AS collections FROM fez_record_search_key_datastream_policy 
                INNER JOIN fez_record_search_key_ismemberof
                ON rek_ismemberof = rek_datastream_policy_pid
                GROUP BY rek_ismemberof_pid
                    HAVING COUNT(rek_ismemberof) > 1 AND MAX(rek_datastream_policy) != MIN(rek_datastream_policy)
                    UNION
                    SELECT rek_ismemberof_pid AS pid, rek_ismemberof AS collections FROM  fez_record_search_key_datastream_policy AS A
                    INNER JOIN fez_record_search_key_ismemberof 
                    ON rek_ismemberof = rek_datastream_policy_pid
                    INNER JOIN fez_record_search_key_datastream_policy AS B
                    ON rek_ismemberof_pid = B.rek_datastream_policy_pid
                    WHERE A.rek_datastream_policy != B.rek_datastream_policy";

    try {
        $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
        $log->err($ex);
        return false;
    }
    if (!empty($res)) {
        foreach ($res as $row) {
            $pid = $row['pid'];
            $body .= "http:/espace.library.uq.edu.au/view/".$pid . "  has a datastream security policy that is in conflict with the collection security.  These collections it is in " . $row['collections'] . " have conflicting datastream policies with this pid.\n";
            ob_flush();
            flush();
        }
    } else {
        echo "None found for conflicts". "\n";
    }

    if (!empty($body)) {
        $body .= "\nPlease refer to the eSpace Manager or Librarian as this issue must be resolved immediately.";
        
        $mail = new Mail_API;
        $subject = "Urgent warning: Thesis security datastream policy conflict detected";
        $to = 'espace@library.uq.edu.au';
        $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
        $mail->setTextBody(stripslashes($body));
        $mail->send($from, $to, $subject, false);
    }


    //----------------------- Stage Two check for open items in special private collections ----------------------- //

    $body = '';

    //This is currently run ever two hours. It will have to be adapted if large collections are checked below
    $stmt =     "SELECT rek_pid AS pid, rek_ismemberof FROM fez_record_search_key
                  LEFT JOIN fez_record_search_key_ismemberof ON rek_pid = rek_ismemberof_pid
                  WHERE rek_ismemberof IN ('UQ:342107', 'UQ:335745')";
    try {
        $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
        $log->err($ex);
        return false;
    }
    $status = Status::getID("Published");
    foreach ($res as $pid) {
        $pid = $pid['pid'];
        if ($status == Record::getSearchKeyIndexValue($pid, "Status", false)) {
            $datastreams = Fedora_API::callGetDatastreams($pid);
            foreach ($datastreams as $datastream) {
                if ($datastream['controlGroup'] == "M"
                    && (!Misc::hasPrefix($datastream['ID'], 'preview_')
                        && !Misc::hasPrefix($datastream['ID'], 'web_')
                        && !Misc::hasPrefix($datastream['ID'], 'thumbnail_')
                        && !Misc::hasPrefix($datastream['ID'], 'stream_')
                        && !Misc::hasPrefix($datastream['ID'], 'presmd_'))) {

                    $userPIDAuthGroups = Auth::getAuthorisationGroups($pid, $datastream['ID']);
                    if (in_array('Viewer', $userPIDAuthGroups)) {
                        $body .= "http:/espace.library.uq.edu.au/view/" . $pid . "  has a datastream: " . $datastream['ID'] . "that's open in collection: " . $pid['rek_ismemberof'] . " where datastreams should be closed.\n";
                        echo $pid . " found with issues\n";
                    }

                }
            }
        }
    }
    if (!empty($body)) {
        $body .= "\nPlease refer to the eSpace Manager or Librarian as this issue must be resolved immediately.";
        $mail = new Mail_API;
        $subject = "Urgent warning: Open access file detected on an embargoed thesis";
        $to = 'espace@library.uq.edu.au';
        $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
        $mail->setTextBody(stripslashes($body));
        $mail->send($from, $to, $subject, false);
    } else {
        echo "None found for open access in closed collections". "\n";
    }

    //----------------------- Stage Three check for open citations in special private collections ----------------------- //

    $body = '';

    //This is currently run ever two hours. It will have to be adapted if large collections are checked below
    $stmt =     "SELECT rek_pid AS pid, rek_ismemberof FROM fez_record_search_key
                  LEFT JOIN fez_record_search_key_ismemberof ON rek_pid = rek_ismemberof_pid
                  WHERE rek_ismemberof IN ('UQ:335745')";
    try {
        $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
        $log->err($ex);
        return false;
    }
    $status = Status::getID("Published");
    foreach ($res as $pid) {
        $pid = $pid['pid'];
        $record = new RecordObject($pid);
        $canView = $record->canView(false);
        if ($canView) {
            $body .= "http:/espace.library.uq.edu.au/view/" . $pid . "  citation is open when it should be restricted in collection: " . $pid['rek_ismemberof'] ."\n";
            echo $pid . " found with issues\n";
        }
    }
    if (!empty($body)) {
        $body .= "\nPlease refer to the eSpace Manager or Librarian as this issue must be resolved immediately.";
        $mail = new Mail_API;
        $subject = "Urgent warning: Open citation detected on an embargoed thesis";
        $to = 'espace@library.uq.edu.au';
        $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
        $mail->setTextBody(stripslashes($body));
        $mail->send($from, $to, $subject, false);
    } else {
        echo "None found for open citations in closed citations collections". "\n";
    }


    echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "Please run from command line or logged in as a super user";
}