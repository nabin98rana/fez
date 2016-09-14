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
    public function getUser($orcidId, $url = null)
    {
      if (empty($url)) {
          // the slash on the end of the URL is critical
          $url = PUBLONS_BASE_URL . "academic/". urlencode($orcidId) . "/";
      }

      $response = Publons::returnPublonsData($url);

      $responseArray = json_decode($response, true);

      if (is_array($responseArray) && array_key_exists('ids', $responseArray)) {
          return $responseArray;
      }

      return false;
    }

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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_GET, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

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
        $psr_publons_id = $db->quote($paper['ids']['academic']['id']);
        $psr_date_reviewed = $db->quote($paper['date_reviewed']);
        $psr_verified = ($paper['verification']['verified'] == true) ? 1 : 0;
        $psr_journal_article = $db->quote(serialize($paper['article']));

        $stmt = "INSERT INTO fez_publons_reviews
        (psr_aut_id, psr_publons_id, psr_date_reviewed, psr_verified,  psr_publisher_id, psr_journal_id, psr_journal_article, psr_update_date)
        VALUES(" . $psr_aut_id . ", " . $psr_publons_id . ", " . $psr_date_reviewed . ", " . $psr_verified . ", ". $psr_publisher_id . ", " . $psr_journal_id. ", " . $psr_journal_article . ", NOW() )
        ON DUPLICATE KEY UPDATE
        psr_date_reviewed=" . $psr_date_reviewed . ", psr_verified=" . $psr_verified . ", psr_publisher_id = ".$psr_publisher_id. ", psr_journal_id = ".$psr_journal_id . ", psr_journal_article = ".$psr_journal_article.", psr_update_date = NOW()";

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }

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

        $stmt = "INSERT INTO fez_publons_journals
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

        $stmt = "INSERT INTO fez_publons_publishers
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

    public function getPublonsReviews($author_username, $startYear, $endYear)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $endYear = (is_numeric($endYear)) ? $endYear + 1 : $endYear; //We plus one since it's inclusive
        $startYear = (is_numeric($startYear)) ? " AND psr_date_reviewed > " . $db->quote($startYear) . " " : "";
        $endYear = (is_numeric($endYear)) ? " AND psr_date_reviewed < " . $db->quote((string)$endYear) . " " : ""; //We need to typecast since the comparison is not to integer

        $stmt = "SELECT aut_id as espace_author_id, aut_org_username as username, aut_student_username AS student_username, aut_display_name as display_name, aut_orcid_id as orcid_id,
                 psr_publons_id as publons_id, psr_date_reviewed as date_reviewed, psr_verified as verified, psp_publisher_name as publisher_name,
                 psj_journal_name as journal_name, psj_journal_issn as journal_issn, psj_journal_tier as journal_tier
                FROM fez_publons_reviews
                LEFT JOIN fez_publons_publishers ON psp_publisher_id = psr_publisher_id
                LEFT JOIN fez_publons_journals ON psj_journal_id = psr_journal_id
                LEFT JOIN fez_author ON aut_id = psr_aut_id
                WHERE aut_student_username = " . $db->quote($author_username) . " OR aut_org_username = " . $db->quote($author_username) . $startYear . $endYear;

        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }

        return $res;
    }
}
