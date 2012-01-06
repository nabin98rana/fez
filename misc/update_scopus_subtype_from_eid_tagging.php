<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005-2010 The University of Queensland,                |
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>        |
// +----------------------------------------------------------------------+

/*
* To find differing current doctypes aready in espace compared to the ones we are about to import

    SELECT rek_pid, sco_eid, sco_doc_type, rek_scopus_doc_type,sdt_code  FROM era_2012_eid_returned_results_all JOIN fez_record_search_key_scopus_id
    ON sco_eid = rek_scopus_id AND uq_pid = rek_scopus_id_pid
    JOIN fez_record_search_key
    ON uq_pid = rek_pid
    JOIN fez_scopus_doctypes
    ON  sco_doc_type = sdt_description
    WHERE sco_status='Successfully Matched'
    AND (rek_scopus_doc_type IS NOT NULL OR rek_scopus_doc_type_xsdmf_id IS NOT NULL)
    AND rek_scopus_doc_type != sdt_code


To find doctypes form the import that don't match

    SELECT * FROM era_2012_eid_returned_results_all LEFT JOIN fez_scopus_doctypes
    ON sco_doc_type = sdt_description
    WHERE sco_status = 'Successfully Matched'
    AND sdt_description IS NULL


 */

ini_set("display_errors", 1);
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.record.php");

$query = "  SELECT rek_pid, sco_eid, sco_doc_type, rek_scopus_doc_type,sdt_code
            FROM era_2012_eid_returned_results_all JOIN fez_record_search_key_scopus_id
            ON sco_eid = rek_scopus_id AND uq_pid = rek_scopus_id_pid
            JOIN fez_record_search_key
            ON uq_pid = rek_pid
            JOIN fez_scopus_doctypes
            ON  sco_doc_type = sdt_description
            WHERE sco_status='Successfully Matched'";

$db = DB_API::get();
$log = FezLog::get();

try {
        $res = $db->fetchAll($query);
} catch (Exception $ex) {
        $log = FezLog::get();
        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
        return;
}
$i=0;
foreach ($res as $row) {
    echo $row['rek_pid']."-";
    $i++;
    $pid = $row['rek_pid'];
    $record = new RecordGeneral($pid);
    $search_keys = array('scopus doc type');
    $values = array($row['sdt_code']);
    $record->addSearchKeyValueList($search_keys, $values, true,"Added from ERA data");
    if ($i == 100)
    {
        break;
    }
}