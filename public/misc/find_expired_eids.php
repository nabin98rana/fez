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
include_once(APP_INC_PATH . "class.session.php");
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . "class.scopus_queue.php");


//Script to find Scopus id's that are incorrect. Replace them if if doi matches in Scopus.

echo "Script started: " . date('Y-m-d H:i:s') . "\n";
$isUser = Auth::getUsername();
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {
    $db = DB_API::get();
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $stmt =  "SELECT * FROM fez_record_search_key_scopus_id
                LEFT JOIN fez_record_search_key ON rek_pid = rek_scopus_id_pid
                LEFT JOIN fez_record_search_key_doi ON rek_pid = rek_doi_pid
                WHERE (rek_scopus_citation_count = '0' OR rek_scopus_citation_count IS NULL) AND rek_pid IS NOT NULL
                GROUP BY rek_scopus_id_pid";

    try {
      $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    $scopusService = new ScopusService(APP_SCOPUS_API_KEY);
    $sq = ScopusQueue::get();
    foreach($res as $row) {
        $pid = $row['rek_pid'];
        $eid = $row['rek_scopus_id'];
        $doi = $row['rek_doi'];
        $scopusId = false;

        $result = $scopusService->getRecordByScopusId($eid);
        if (strpos($result, 'RESOURCE_NOT_FOUND') !== false) {
            echo "Fail:" . " " . $pid . ", " . $eid ;

            if ($doi) {
                $xml = $scopusService->getRecordsBySearchQuery('DOI('.$doi.')');
                $pregMatches = array();
                preg_match('/<dc:identifier>SCOPUS_ID\:(\d+)<\/dc:identifier>/', $xml, $pregMatches);
                $scopusId = (array_key_exists(1, $pregMatches)) ? $pregMatches[1] : null;
            }

            if ($scopusId) {
                $history .= " Updating Scopus ID due to previous ID being invalid";
                //$record->addSearchKeyValueList(array("Scopus ID"), array($scopusId), true, $history);
                //$sq->add($scopusId);
                echo " Replaced with new Scopus Id: ".$scopusId."\n";
            } else {
                echo " No match found\n";
            }

        }
        ob_flush();
        flush();
        usleep(500000);
    }
    echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "Please run from command line or logged in as a super user";
}