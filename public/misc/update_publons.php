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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . 'class.publons.php');
include_once(APP_INC_PATH . 'class.api_researchers.php');


//Script to update publon information

echo "Script started: " . date('Y-m-d H:i:s') . "\n";
$isUser = Auth::getUsername();
if ((php_sapi_name() === "cli") || (User::isUserSuperAdministrator($isUser))) {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT aut_orcid_id FROM " . APP_TABLE_PREFIX . "author WHERE aut_orcid_id IS NOT NULL AND aut_orcid_id != ''";

    try {
        $res = $db->fetchCol($stmt);
    } catch (Exception $ex) {
        $log->err($ex);
        return false;
    }

    foreach ($res as $orcid) {
        $results = Publons::getUserData($orcid);

        $authorId = ApiResearchers::authorIdFromOrcid($orcid);

        foreach ($results['results'] as $paper) {
            $check = Publons::savePublonsReview($authorId, $paper);
        }
        usleep(800000);  // Rate limited to 60 per second
    }

    echo "Updating publon tiered list\n";
    $stmt = "UPDATE " . APP_TABLE_PREFIX . "publons_journals
                LEFT JOIN " . APP_TABLE_PREFIX . "journal_uq_tiered_issns ON jni_issn = psj_journal_issn
                LEFT JOIN " . APP_TABLE_PREFIX . "journal_uq_tiered ON jnl_id = jni_jnl_id
                SET " . APP_TABLE_PREFIX . "publons_journals.psj_journal_tier = " . APP_TABLE_PREFIX . "journal_uq_tiered.jnl_rank
                WHERE psj_journal_tier IS NULL OR psj_journal_tier = ''";

    try {
        $res = $db->exec($stmt);
    } catch (Exception $ex) {
        $log->err($ex);
        return false;
    }

    echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "Please run from command line or logged in as a super user";
}