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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.db_api.php");

$log = FezLog::get();
$db = DB_API::get();

$callback = $_GET['callback'];
$callback = !empty($callback) ? preg_replace('/[^a-z0-9\.$_]/si', '', $callback) : false;
$author_username  = $_GET['author_username'];
if(!ctype_alnum($author_username)) {   //is alphanumeric
    exit();
}

$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "author WHERE aut_org_username =  " .$db->quote($author_username);

try {
    $res1 = $db->fetchAll($stmt);
}
catch (Exception $ex) {
    $log->err($ex);
    return false;
}

$stmt = "SELECT rek_author_id, rek_doi, " . APP_TABLE_PREFIX . "altmetric.*, " . APP_TABLE_PREFIX . "record_search_key.* FROM " . APP_TABLE_PREFIX . "author
INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON aut_id = rek_author_id
INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_doi ON rek_doi_pid = rek_author_id_pid
INNER JOIN " . APP_TABLE_PREFIX . "altmetric ON as_doi = rek_doi
INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_author_id_pid
WHERE aut_org_username =" .$db->quote($author_username) . "
ORDER BY as_1d DESC, as_2d DESC, as_3d DESC, as_4d DESC, as_5d DESC, as_6d DESC, as_1w DESC, as_1m DESC, as_3m DESC, as_6m DESC, as_1y DESC LIMIT 3";



try {
    $res2 = $db->fetchAll($stmt);
}
catch (Exception $ex) {
    $log->err($ex);
    return false;
}

$stmt = "SELECT rek_isi_loc, " . APP_TABLE_PREFIX . "thomson_citations.*, " . APP_TABLE_PREFIX . "record_search_key.*  FROM " . APP_TABLE_PREFIX . "author
    INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON aut_id = rek_author_id
    INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_isi_loc ON rek_isi_loc_pid = rek_author_id_pid
    INNER JOIN " . APP_TABLE_PREFIX . "thomson_citations ON tc_isi_loc = rek_isi_loc
    INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_author_id_pid
    WHERE aut_org_username = " .$db->quote($author_username) . " AND tc_created > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -180 DAY)) AND tc_diff_previous IS NOT NULL AND tc_diff_previous > 0
    GROUP BY tc_isi_loc
    ORDER BY tc_created DESC LIMIT 3;";

try {
    $res3 = $db->fetchAll($stmt);
}
catch (Exception $ex) {
    $log->err($ex);
    return false;
}

$stmt = "SELECT rek_scopus_id, " . APP_TABLE_PREFIX . "scopus_citations.*, " . APP_TABLE_PREFIX . "record_search_key.* FROM " . APP_TABLE_PREFIX . "author
    INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON aut_id = rek_author_id
    INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_scopus_id ON rek_scopus_id_pid = rek_author_id_pid
    INNER JOIN " . APP_TABLE_PREFIX . "scopus_citations ON sc_eid = rek_scopus_id
    INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_author_id_pid
    WHERE aut_org_username = " .$db->quote($author_username) . " AND sc_created > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -180 DAY)) AND sc_diff_previous IS NOT NULL  AND sc_diff_previous > 0
    GROUP BY sc_eid
    ORDER BY sc_created DESC LIMIT 3;";

try {
    $res4 = $db->fetchAll($stmt);
}
catch (Exception $ex) {
    $log->err($ex);
    return false;
}


$output = array();
$output['author_details'] = $res1;
$output['altmetric'] = $res2;
$output['thomson'] = $res3;
$output['scopus'] = $res4;



header('Access-Control-Allow-Origin: *');
$callback = !empty($callback) ? preg_replace('/[^a-z0-9\.$_]/si', '', $callback) : false;
header('Content-Type: ' . ($callback ? 'application/javascript' : 'application/json') . ';charset=UTF-8');
echo json_encode($output);