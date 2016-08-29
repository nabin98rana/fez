<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
// | Authors: Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . 'class.background_process.php');
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . "class.scopus_queue.php");
include_once(APP_INC_PATH . "class.author_era_affiliations.php");

class BackgroundProcess_Alert_Datastream_Policy_Conflicts extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_alert_datastream_policy_conflicts.php';
    $this->name = 'Alert datastream policy conflicts';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $this->alertConflicts();

    $this->setState(BGP_FINISHED);
  }

  function alertConflicts() {
    $db = DB_API::get();
    $log = FezLog::get();

    // Stage one Datastream Policy conflicts (Is it in two collections with
    // different set datastream policies

    $body = '';
    $stmt =     "SELECT rek_ismemberof_pid AS pid, GROUP_CONCAT(rek_ismemberof)  AS collections 
                FROM " . APP_TABLE_PREFIX . "record_search_key_datastream_policy 
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_ismemberof
                ON rek_ismemberof = rek_datastream_policy_pid
                GROUP BY rek_ismemberof_pid
                HAVING COUNT(rek_ismemberof) > 1 AND MAX(rek_datastream_policy) != MIN(rek_datastream_policy)
                UNION
                SELECT rek_ismemberof_pid AS pid, rek_ismemberof AS collections FROM " . APP_TABLE_PREFIX . "record_search_key_datastream_policy AS A
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_ismemberof 
                ON rek_ismemberof = rek_datastream_policy_pid
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_datastream_policy AS B
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
        $body .= APP_URL . "/view/".$pid . "  has a datastream security policy that is in conflict with the " .
          "collection security.  These collections it is in " . $row['collections'] .
          " have conflicting datastream policies with this pid.\n";
      }
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
    $stmt =     "SELECT rek_pid AS pid, rek_ismemberof FROM " . APP_TABLE_PREFIX . "record_search_key
                  LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_ismemberof ON rek_pid = rek_ismemberof_pid
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
      $isMemberOf = $pid['rek_ismemberof'];
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
              $body .= APP_URL . "/view/" . $pid . "  has a datastream: " . $datastream['ID'] .
                "that's open in collection: " . $isMemberOf . " where datastreams should be closed.\n";
            }

          }
        }
      }
    }
    if (!empty($body)) {
      $body .= "\nPlease refer to the eSpace Manager or Librarian as this issue must be resolved immediately.";
      $mail = new Mail_API;
      $subject = "Urgent warning: Open access file detected on an embargoed thesis";
      $to = APP_ADMIN_EMAIL;
      $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
      $mail->setTextBody(stripslashes($body));
      $mail->send($from, $to, $subject, false);
    }

    //----------------------- Stage Three check for open citations in special private collections ----------------------- //

    $body = '';

    //This is currently run ever two hours. It will have to be adapted if large collections are checked below
    $stmt =     "SELECT rek_pid AS pid, rek_ismemberof FROM " . APP_TABLE_PREFIX . "record_search_key
                  LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_ismemberof ON rek_pid = rek_ismemberof_pid
                  WHERE rek_ismemberof IN ('UQ:335745')";
    try {
      $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    foreach ($res as $pid) {
      $isMemberOf = $pid['rek_ismemberof'];
      $pid = $pid['pid'];
      $record = new RecordObject($pid);
      $canView = $record->canView(false);
      if ($canView) {
        $body .= APP_URL . "/view/" . $pid . "  citation is open when it should be restricted " .
          "in collection: " . $isMemberOf ."\n";
      }
    }

    if (!empty($body)) {
      $body .= "\nPlease refer to the eSpace Manager or Librarian as this issue must be resolved immediately.";
      $mail = new Mail_API;
      $subject = "Urgent warning: Open citation detected on an embargoed thesis";
      $to = APP_ADMIN_EMAIL;
      $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
      $mail->setTextBody(stripslashes($body));
      $mail->send($from, $to, $subject, false);
    }
  }
}
