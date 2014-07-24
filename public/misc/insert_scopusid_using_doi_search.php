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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+

// Loops through all records in eSpace, and inserts the ScopusID by
// searching the Scopus CitedBy Retrieve on DOI

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . 'class.scopus.php');
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . 'class.scopus_queue.php');

$max = 100; 	// Max number of primary key IDs to send with each service request call
$sleep = 1; 	// Number of seconds to wait for between successive service calls

$isUser = Auth::getUsername();
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {
    echo "Script started: " . date('Y-m-d H:i:s') . "\n";

    $stmt = "SELECT rek_pid, rek_doi FROM " . APP_TABLE_PREFIX . "record_search_key
            LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_scopus_id
            ON rek_scopus_id_pid = rek_pid
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_doi
            ON rek_doi_pid = rek_pid
            WHERE rek_scopus_id_pid IS NULL";

    //Add a second argument to run over all published dates
    if (empty($argv[1])) {
        $stmt .= " AND rek_date >= '2008-01-01 00:00:00'";
    }
    try {
        $res = $db->fetchAll($stmt);
    } catch (Exception $ex) {
        $log->err($ex);
        echo "Failed to find pids: " . $ex;
    }

    echo "======================================\n";
    echo "Scopus from DOI start\n";
    echo date('d/m/Y H:i:s') . "\n";
    ob_flush();

    $input_keys = array();
    foreach($res as $row) {
        $input_keys_all[$row['rek_pid']] = array('doi' => $row['rek_doi']);
    }

    $sq = ScopusQueue::get();
    for($i=0; $i< ((int)(count($res)/$max) +1); $i++) {
        echo "Loop ".$i. "\n";
        $input_keys = array_splice($input_keys_all, $i*$max, 100);
        if(count($input_keys) > 0 ) {
            $result = Scopus::getCitedByCount($input_keys);
            foreach($result as $pid => $link_data) {
                echo "$pid: ". $link_data['eid']. "\n";
                ob_flush();
                // Update record with new Scopus ID
                $record = new RecordObject($pid);
                $search_keys = array("Scopus ID");
                $values = array($link_data['eid']);
                $record->addSearchKeyValueList($search_keys, $values, true, ' was added based on Scopus Service data given current doi');
                $sq->add($link_data['eid']);
            }

            sleep($sleep); // Wait before using the service again
        }
    }
    echo " done. ".date('d/m/Y H:i:s') . "\n";
} else {
    echo "Admin only";
}