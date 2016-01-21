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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");

class Publons
{
    /**
     * Method used to perform a service request
     *
     */
    public function getUserData($orcidId, $url = null)
    {
    $log = FezLog::get();


    if (empty($url)) {
        $url = PUBLONS_BASE_URL . "academic/review/?academic=".$orcidId . "&pre=true&paginate_by=100";
    }

    $response = Publons::returnPublonsData($url);
    //use recursion to get all results
    $responseArray = json_decode( $response, true );
    if(!empty($responseArray['next'])) {
        $responseAdditional = Publons::getUserData($orcidId, $responseArray['next']);

        //$responseAdditionalArray = json_decode( $responseAdditional, true );

        $res['results'] = array_merge( $responseArray['results'], $responseAdditional['results'] );
        $res['count'] = $responseAdditional['count'];
        //$response = json_encode( $res );
        $response = $res;
    } else {
        $response = $responseArray;
    }

    return $response;
    }

    public function getUniversityData($url = null)
    {
        if (empty($url)) {
            $url = PUBLONS_BASE_URL . 'academic/?institution=The%20University%20of%20Queensland&paginate_by=100';
        }
        $response =  Publons::returnPublonsData($url);
        //use recursion to get all results
        $responseArray = json_decode( $response, true );
        if(!empty($responseArray['next'])) {
            $responseAdditional = Publons::getUniversityData( $responseArray['next']);
            $res['results'] = array_merge( $responseArray['results'], $responseAdditional['results'] );
            $res['count'] = $responseAdditional['count'];
            $response = $res;
        } else {
            $response = $responseArray;
        }

        return $response;
    }

    public function returnPublonsData($url)
    {
        $log = FezLog::get();
        // Do the service request
        $header[] = "Content-type: application/json";
        $header[] = 'Authorization: Token ' . PUBLONS_TOKEN;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_GET, 1);

        if (APP_HTTPS_CURL_CHECK_CERT == 'OFF') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $log->err(array(curl_error($ch), $response, __FILE__, __LINE__));
            return false;
        } else {
            curl_close($ch);
            return $response;
        }
    }

    //Expects output from getUserData
    public function saveArrayReturnedByPublons($orcidId, $data)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        foreach ($data as $paper) {

        }
    }

    public function returnOrcidIfHasPublons($author_id)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT aut_orcid_id FROM fez_author
            INNER JOIN fez_publons_reviews ON psr_aut_id = aut_id
            WHERE aut_id = " . $db->quote($author_id) . "
            LIMIT 1;";

        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }

        return $res;

    }

    //Expects paper output from getUserData
    public function savePublonsReview($authorId, $paper)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (empty($paper['publisher']['ids']['id'])) {
            $psr_publisher_id = 'NULL';
        } else {
            $psr_publisher_id = $db->quote($paper['publisher']['ids']['id']);
        }

        if (empty($paper['journal']['ids']['id'])) {
            $psr_journal_id = 'NULL';
        } else {
            $psr_journal_id = $db->quote($paper['journal']['ids']['id']);
        }

        $psr_aut_id = $db->quote($authorId);
        $psr_publon_id = $db->quote($paper['ids']['academic']['id']);
        $psr_date_reviewed = $db->quote($paper['date_reviewed']);
        $psr_verified = ($paper['verification']['verified'] == true) ? 1 : 0;
        $psr_journal_article = $db->quote(serialize($paper['article']));

        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "publons_reviews
        (psr_aut_id, psr_publon_id, psr_date_reviewed, psr_verified,  psr_publisher_id, psr_journal_id, psr_journal_article, psr_update_date)
        VALUES(" . $psr_aut_id . ", " . $psr_publon_id . ", " . $psr_date_reviewed . ", " . $psr_verified . ", ". $psr_publisher_id . ", " . $psr_journal_id. ", " . $psr_journal_article . ", NOW() )
        ON DUPLICATE KEY UPDATE
        psr_date_reviewed=" . $psr_date_reviewed . ", psr_verified=" . $psr_verified . ", psr_publisher_id = ".$psr_publisher_id. ", psr_journal_id = ".$psr_journal_id . ", psr_journal_article = ".$psr_journal_article.", psr_update_date = NOW()";

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }
        Publons::savePublonsId($authorId, '1');  //Currently we don't want to store the publons id
        Publons::savePublonsJournal($paper);
        Publons::savePublonsPublisher($paper);
        return $res;
    }

    public function savePublonsId($authorId, $publonsId)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (empty($publonsId)) {
            return false;
        }

        $stmt = "UPDATE fez_author SET aut_publons_id = " . $db->quote($publonsId) . " WHERE aut_id = " . $db->quote($authorId);

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }
        return $res;
    }

    //Expects paper output from getUserData
    public function savePublonsJournal($paper)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if(empty($paper['journal']['ids']['id'])) { // Ok not to have a Journal name
            return true;
        }

        $psj_journal_id  = $db->quote($paper['journal']['ids']['id']);
        $psj_journal_name = $db->quote($paper['journal']['name']);
        $psj_journal_issn = $db->quote($paper['journal']['ids']['issn']);
        $psj_journal_eissn = $db->quote($paper['journal']['ids']['eissn']);

        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "publons_journals
        (psj_journal_id, psj_journal_name, psj_journal_issn, psj_journal_eissn)
        VALUES( " . $psj_journal_id . ", " . $psj_journal_name .
            ", " . $psj_journal_issn . ", " . $psj_journal_eissn  . ")

        ON DUPLICATE KEY UPDATE

        psj_journal_id = " . $psj_journal_id . ", psj_journal_name = " . $psj_journal_name . ",
        psj_journal_issn =  " . $psj_journal_issn . ", psj_journal_eissn =  " .$psj_journal_eissn;

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }

        return $res;
    }

    //Expects paper output from getUserData
    public function savePublonsPublisher($paper)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if(empty($paper['publisher']['ids']['id'])) { // Ok not to have a publisher
            return true;
        }

        $psp_publisher_id = $db->quote($paper['publisher']['ids']['id']);
        $psp_publisher_name = $db->quote($paper['publisher']['name']);

        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "publons_publishers
        (psp_publisher_id, psp_publisher_name)
        VALUES(" . $psp_publisher_id . ", " . $psp_publisher_name . " )
        ON DUPLICATE KEY UPDATE
        psp_publisher_id = " . $psp_publisher_id . ", psp_publisher_name = " . $psp_publisher_name;

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }

        return $res;
    }

    public function getPublonsReviews($author_username)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT aut_id, aut_org_username,aut_display_name, aut_orcid_id, psr_publon_id, psr_date_reviewed, psr_verified, psp_publisher_name, psj_journal_name, psj_journal_issn, psj_journal_tier
                FROM " . APP_TABLE_PREFIX . "publons_reviews
                LEFT JOIN " . APP_TABLE_PREFIX . "publons_publishers ON psp_publisher_id = psr_publisher_id
                LEFT JOIN " . APP_TABLE_PREFIX . "publons_journals ON psj_journal_id = psr_journal_id
                LEFT JOIN " . APP_TABLE_PREFIX . "author ON aut_id = psr_aut_id
                WHERE aut_org_username = " . $db->quote($author_username);

        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }

        return $res;
    }
}
