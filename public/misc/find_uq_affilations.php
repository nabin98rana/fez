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


//Script to find uq affiliations in Scopus

echo "Script started: " . date('Y-m-d H:i:s') . "\n";
$isUser = Auth::getUsername();
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {
    $db = DB_API::get();
    $body = '';
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $stmt =     "SELECT DISTINCT pid, rek_scopus_id FROM __era_ro_uq_asc_req
                LEFT JOIN fez_record_search_key_scopus_id ON rek_scopus_id_pid = pid
                WHERE rek_scopus_id IS NOT NULL";

    try {
      $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    $res[0]['rek_scopus_id'] = '2-s2.0-84875434442'; $res[0]['pid'] = 'UQ:20817';
    $scopusService = new ScopusService(APP_SCOPUS_API_KEY);

    foreach($res as $row) {
        $pid = $row['pid'];
        $scopusId = $row['rek_scopus_id'];

        $record = $scopusService->getRecordByScopusId($scopusId);
        if (strpos($record, 'RESOURCE_NOT_FOUND') !== false) {
            echo "Fail?? " . " " . $pid . ", " . $eid;
        }
        $sri = new ScopusRecItem();
        $sri->load($record);
        $sri_fields = $sri->getFields();
        $authorObj = new Author();
        $pidAuthors = $authorObj->getAuthorsByPID($pid, true);
        if (count($pidAuthors) != count($sri_fields['_author_affiliation_ids'])) {
            echo "Error, author counts miss match : $pid : $scopusId \n";
        } else {
            echo "$pid : $scopusId \n";
        }
        $found = false;
        $staff_id = array();
        $af_era_comment = array();
        $aae_status_id_lookup = array();
        $count = 1;  //called function starts array at 1
        for ($i=0; $i < count($pidAuthors); $i++) {
            echo $pidAuthors[$i]['rek_author']  . ' - '. $sri_fields['_authors'][$i] . ' - '. implode(', ', $sri_fields['_author_affiliation_ids'][$i]);
             if (in_array('60031004', $sri_fields['_author_affiliation_ids'][$i]) || in_array(60087457, $sri_fields['_author_affiliation_ids'][$i])) {
                 echo " - The University of Queensland\n";
                 $details = Author::getDetails($pidAuthors[$i]['rek_author_id']);
                 if (!empty($pidAuthors[$i]['rek_author_id']) && !empty($details['aut_org_staff_id'])) {
                     $staff_id[$count] = $details['aut_org_staff_id'];
                     $af_era_comment[$count] = 'Marked in Scopus as affiliated with UQ';
                     $aae_status_id_lookup[$count] = 2;
                     $found = true;
                     $count++;
                 } else {
                     echo "Warning affiliated but no author ID / Staff ID!!!!!!";
                 }
             } else {
                 echo " - No record of affiliation\n";
                 $details = Author::getDetails($pidAuthors[$i]['rek_author_id']);
                 if (!empty($pidAuthors[$i]['rek_author_id']) && !empty($details['aut_org_staff_id'])) {
                     $staff_id[$count] = $details['aut_org_staff_id'];
                     $af_era_comment[$count] = '';
                     $aae_status_id_lookup[$count] = 1;
                     $count++;
                 }
             }
        }
        if ($found) {
            author_era_affiliations::save(null, $pid, $aae_status_id_lookup, $af_era_comment, $staff_id);
        }

        echo "\n";
        ob_flush();
        flush();
        usleep(500000);
    }

    echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "Please run from command line or logged in as a super user";
}