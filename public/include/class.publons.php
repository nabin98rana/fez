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
       // https://publons.com/api/v2/academic/review/?academic=480479&pre=true
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

    //Expects paper output from getUserData
    public function savePublonsReview($authorId, $paper)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $psr_author_id = $db->quote($authorId);
        $psr_publon_id = $db->quote($paper['ids']['academic']['id']);
        $psr_date_reviewed = $db->quote($paper['date_reviewed']);
        $psr_verified = $db->quote($paper['verification']['verified']);
        $psr_publisher_id = $db->quote($paper['publisher']['ids']['id']);
        $psr_publisher_name = $db->quote($paper['publisher']['name']);
        $psr_journal_id  = $db->quote($paper['journal']['ids']['id']);
        $psr_journal_name = $db->quote($paper['journal']['name']);
        $psr_journal_issn = $db->quote($paper['journal']['ids']['issn']);
        $psr_journal_eissn = $db->quote($paper['journal']['ids']['eissn']);
        $psr_journal_article = $db->quote($paper['article']);

        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "publons_reviews
        (psr_author_id, psr_publon_id, psr_date_reviewed, psr_verified, psr_publisher_id, psr_publisher_name, psr_journal_id, psr_journal_name, psr_journal_issn, psr_journal_eissn, psr_journal_article, psr_update_data)
        VALUES(" . $psr_author_id . ", " . $psr_publon_id . ", " . $psr_date_reviewed . ", " . $psr_verified . ", " . $psr_publisher_id . ",
               " . $psr_publisher_name . ", " . $psr_journal_id . ", " . $psr_journal_name .
               ", " . $psr_journal_issn . ", " . $psr_journal_eissn . ", " . $psr_journal_article . ", NOW() )

        ON DUPLICATE KEY UPDATE

        psr_date_reviewed=" . $psr_date_reviewed . ", psr_verified=" . $psr_verified . ", psr_publisher_id = " . $psr_publisher_id . ",
        psr_publisher_name = " . $psr_publisher_name . ", psr_journal_id = " . $psr_journal_id . ", psr_journal_name = " . $psr_journal_name . ",
        psr_journal_issn =  " . $psr_journal_issn . ", psr_journal_eissn =  " .$psr_journal_eissn. ", psr_journal_article = ".$psr_journal_article.", psr_update_data = NOW()";

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }

        return $res;
    }
}
