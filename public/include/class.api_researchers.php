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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle researcher API stuff
 *
 * @version 1.0
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.crossref.php");
include_once(APP_INC_PATH . "class.author.php");

class ApiResearchers
{

    public function getAuthorDetails($author_username)
    {

        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT aut_id, aut_org_username,  aut_student_username, aut_email,
aut_display_name, aut_fname, aut_mname, aut_lname, aut_title, aut_position, aut_homepage_link, aut_researcher_id, aut_scopus_id, aut_mypub_url,
aut_people_australia_id, aut_description, aut_orcid_id, aut_google_scholar_id, aut_rid_last_updated, aut_publons_id FROM " . APP_TABLE_PREFIX . "author WHERE aut_org_username =  " . $db->quote($author_username) . " OR aut_student_username = " . $db->quote($author_username);

        try {
            $res = $db->fetchAll($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    public function getAltmetrics($author_username)
    {

        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT a.rek_author_id, GROUP_CONCAT(rek_author ORDER BY b.rek_author_order SEPARATOR ';') as rek_author,
                GROUP_CONCAT(c.rek_author_id ORDER BY c.rek_author_id_order SEPARATOR ';') as rek_author_ids,
                rek_doi, " . APP_TABLE_PREFIX . "altmetric.*, rek_pid, rek_title, rek_formatted_title,
                rek_thomson_citation_count, rek_scopus_citation_count, rek_journal_name, rek_date
                 FROM " . APP_TABLE_PREFIX . "author
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id a ON aut_id = rek_author_id
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_doi ON rek_doi_pid = a.rek_author_id_pid
                INNER JOIN " . APP_TABLE_PREFIX . "altmetric ON as_doi = rek_doi
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = a.rek_author_id_pid
                INNER JOIN " . APP_TABLE_PREFIX . "auth_index2_lister ON authi_pid = rek_pid AND authi_arg_id = '11'
                LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_author b on rek_pid = rek_author_pid
                LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id c on b.rek_author_pid = c.rek_author_id_pid and c.rek_author_id_order = b.rek_author_order
                LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_journal_name on rek_pid = rek_journal_name
                WHERE (aut_org_username =" . $db->quote($author_username) . " OR aut_student_username =" . $db->quote($author_username) . " ) AND rek_status = 2
                GROUP BY as_doi
                ORDER BY as_1d DESC, as_2d DESC, as_3d DESC, as_4d DESC, as_5d DESC, as_6d DESC, as_1w DESC, as_1m DESC, as_3m DESC, as_6m DESC, as_1y DESC LIMIT 3";


        try {
            $res = $db->fetchAll($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    public function setThomsonMetrics($author_username)
    {

        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT a.rek_author_id, rek_isi_loc, GROUP_CONCAT(rek_author ORDER BY b.rek_author_order SEPARATOR ';') as rek_author,
        " . APP_TABLE_PREFIX . "thomson_citations_cache.*, rek_pid, rek_date, rek_journal_name, rek_title, rek_formatted_title,
        GROUP_CONCAT(c.rek_author_id ORDER BY c.rek_author_id_order SEPARATOR ';') as rek_author_ids,
          rek_scopus_citation_count, rek_altmetric_score FROM " . APP_TABLE_PREFIX . "author
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id a ON aut_id = a.rek_author_id
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_isi_loc ON rek_isi_loc_pid = rek_author_id_pid
            INNER JOIN " . APP_TABLE_PREFIX . "thomson_citations_cache ON tc_isi_loc = rek_isi_loc
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_author_id_pid
            INNER JOIN " . APP_TABLE_PREFIX . "auth_index2_lister ON authi_pid = rek_pid AND authi_arg_id = '11'
            LEFT JOIN fez_record_search_key_author b on rek_pid = rek_author_pid
            LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id c on b.rek_author_pid = c.rek_author_id_pid and c.rek_author_id_order = b.rek_author_order
            LEFT JOIN fez_record_search_key_journal_name on rek_pid = rek_journal_name
            WHERE (aut_org_username = " . $db->quote($author_username) . " OR aut_student_username =" . $db->quote($author_username) . ") AND tc_created > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -180 DAY)) AND tc_diff_previous IS NOT NULL AND tc_diff_previous > 0 AND rek_status = 2
            GROUP BY tc_isi_loc, tc_created
            ORDER BY tc_created DESC LIMIT 3";

        try {
            $res = $db->fetchAll($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    public function setScopusMetrics($author_username)
    {

        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT a.rek_author_id, rek_scopus_id, GROUP_CONCAT(rek_author ORDER BY b.rek_author_order SEPARATOR ';') as rek_author,
        " . APP_TABLE_PREFIX . "scopus_citations_cache.*, rek_pid,  rek_date, rek_journal_name, rek_title, rek_formatted_title,
        GROUP_CONCAT(c.rek_author_id ORDER BY c.rek_author_id_order SEPARATOR ';') as rek_author_ids,
        rek_thomson_citation_count, rek_altmetric_score
        FROM " . APP_TABLE_PREFIX . "author
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id a ON aut_id = a.rek_author_id
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_scopus_id ON rek_scopus_id_pid = rek_author_id_pid
            INNER JOIN " . APP_TABLE_PREFIX . "scopus_citations_cache ON sc_eid = rek_scopus_id
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_author_id_pid
            INNER JOIN " . APP_TABLE_PREFIX . "auth_index2_lister ON authi_pid = rek_pid AND authi_arg_id = '11'
            LEFT JOIN fez_record_search_key_author b on rek_pid = rek_author_pid
            LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id c on b.rek_author_pid = c.rek_author_id_pid and c.rek_author_id_order = b.rek_author_order
            LEFT JOIN fez_record_search_key_journal_name on rek_pid = rek_journal_name
            WHERE (aut_org_username = " . $db->quote($author_username) . " OR aut_student_username = " . $db->quote($author_username) . " ) AND sc_created > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -180 DAY)) AND sc_diff_previous IS NOT NULL  AND sc_diff_previous > 0 AND rek_status = 2
            GROUP BY sc_eid, sc_created
            ORDER BY sc_created DESC LIMIT 3";

        try {
            $res = $db->fetchAll($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;

    }

    public static function wosCitationURL($wosArticleId)
    {
        $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
        $wokUsername = (defined('WOK_USERNAME')) ? WOK_USERNAME : '';
        return $app_link_prefix . "http://gateway.isiknowledge.com/gateway/Gateway.cgi?GWVersion=2&SrcApp=resolve1&DestLinkType=CitingArticles&DestApp=WOS_CPL&KeyUT=" . $wosArticleId . "&SrcAuth=" . $wokUsername;
    }

    public static function scopusCitationURL($scopusArticleId)
    {
        $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
        return $app_link_prefix . "http://www.scopus.com/results/citedbyresults.url?sort=plf-f&cite=" . $scopusArticleId . "&src=s&sot=cite&sdt=a";
    }

    public static function wosURL($wosId)
    {
        $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
        $wokUsername = (defined('WOK_USERNAME')) ? WOK_USERNAME : '';
        return $app_link_prefix . "http://gateway.isiknowledge.com/gateway/Gateway.cgi?GWVersion=2&SrcApp=resolve1&DestLinkType=FullRecord&DestApp=WOS_CPL&KeyUT=" . $wosId . "&SrcAuth=" . $wokUsername;
    }

    public static function scopusURL($scopusArticleId)
    {
        $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
        return $app_link_prefix . "http://www.scopus.com/record/display.url?eid=" . $scopusArticleId . "&origin=inward";
    }

    public static function googleScholar($title)
    {
        return "http://scholar.google.com/scholar?q=intitle:" . $title;
    }

    public static function altmetric($altmetricDOI)
    {
        return "http://www.altmetric.com/details.php?citation_id=" . $altmetricDOI;
    }

    //11 is lister permissions. 371 is data collections. 2 is published
    public static function getDataCollections($author_username, $startYear = null, $endYear = null)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $endYear = (is_numeric($endYear)) ? $endYear + 1 : $endYear; //We plus one since it's inclusive
        $startYear = (is_numeric($startYear)) ? " AND rek_date > " . $db->quote($startYear) . " " : "";
        $endYear = (is_numeric($endYear)) ? " AND rek_date < " . $db->quote((string)$endYear) . " " : ""; //We need to typecast since the comparison is not to integer

        $stmt = "SELECT rek_pid, rek_title, GROUP_CONCAT(rek_author_id) as rek_author_id, rek_date FROM " . APP_TABLE_PREFIX . "record_search_key
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_pid = rek_author_id_pid
                INNER JOIN " . APP_TABLE_PREFIX . "auth_index2_lister ON authi_pid = rek_pid AND authi_arg_id = '11'
                INNER JOIN " . APP_TABLE_PREFIX . "author on aut_id = rek_author_id
                WHERE rek_display_type = 371 AND (aut_org_username = " . $db->quote($author_username) . " OR aut_student_username = " . $db->quote($author_username) . ") AND rek_status = 2 " . $startYear . $endYear . "
                GROUP BY(rek_pid)";

        try {
            $res = $db->fetchAll($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    //11 is lister permissions. 371 is data collections. 2 is published
    public static function getPidsWithDatacollections($author_username, $startYear = null, $endYear = null)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $endYear = (is_numeric($endYear)) ? $endYear + 1 : $endYear; //We plus one since it's inclusive
        $startYear = (is_numeric($startYear)) ? " AND B.rek_date > " . $db->quote($startYear) . " " : "";
        $endYear = (is_numeric($endYear)) ? " AND B.rek_date < " . $db->quote((string)$endYear) . " " : "";  //We need to typecast since the comparison is not to integer

        $stmt = "SELECT rek_isdatasetof as rek_pid,  B.rek_title, GROUP_CONCAT(A.rek_pid) AS rek_is_dataset_of, B.rek_date FROM " . APP_TABLE_PREFIX . "record_search_key AS A
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON A.rek_pid = rek_author_id_pid
                INNER JOIN " . APP_TABLE_PREFIX . "auth_index2_lister ON authi_pid = A.rek_pid AND authi_arg_id = '11'
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_isdatasetof ON A.rek_pid = rek_isdatasetof_pid
                INNER JOIN " . APP_TABLE_PREFIX . "author on aut_id = rek_author_id
                INNER JOIN " . APP_TABLE_PREFIX . "record_search_key AS B on rek_isdatasetof = B.rek_pid
                WHERE A.rek_display_type = 371 AND (aut_org_username = " . $db->quote($author_username) . " OR aut_student_username = " . $db->quote($author_username) . ") AND A.rek_status = 2 " . $startYear . $endYear . "
                GROUP BY(rek_isdatasetof)";

        try {
            $res = $db->fetchAll($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    public static function changeId($authorUsername, $id, $idType)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        //We'll allow either the number of the id from UQLApp or a string
        if (strtolower($idType) == 'orcid' || $idType == 1) {
            $column = 'aut_orcid_id';
        } else if (strtolower($idType) == 'rid' || $idType == 2) {
            $column = 'aut_researcher_id';
        } else if (strtolower($idType) == 'scopus' || $idType == 3) {
            $column = 'aut_scopus_id';
        } else if (strtolower($idType) == 'scholar') {
            $column = 'aut_google_scholar_id';
        } else if (strtolower($idType) == 'people') {
            $column = 'aut_people_australia_id';
        } else if (strtolower($idType) == 'publons' || $idType == 4) {
            $column = 'aut_publons_id';
        } else {
            return false;
        }

        //first check author ID exists so it doesnt try to update where author ID is '' (slow/buggy)
        $authorId = Author::getIDByUsername($authorUsername);
        if (!is_numeric($authorId)) {
          return false;
        }

        $stmt = "UPDATE  " . APP_TABLE_PREFIX . "author
            SET " . $column . " = " . $db->quote($id) . "
            WHERE aut_org_username = " . $db->quote($authorUsername) . "
            OR aut_student_username = " . $db->quote($authorUsername);

        try {
            $res = $db->exec($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        if (strtolower($idType) == 'orcid' || $idType == 1) {
            ApiResearchers::onOrcidChange(author::getIDByUsername($authorUsername), $id);
        }

        return $res;
    }

    public static function onOrcidChange($authorId, $newOrcid) {

        if (!is_numeric($authorId)) {
          return false;
        }
        if(empty($newOrcid)) {
            ApiResearchers::deleteGrants($authorId);
        }
        $pids = ApiResearchers::getCrossrefPids($authorId);

        foreach ($pids as $pid) {
            $cr = new Crossref;
            $cr->updateCrossrefFromPid($pid, "Update Crossref info with orcid's");
        }

    }

    //Get pids that have a UQ doi
    public static function getCrossrefPids($authorId)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT dcr_pid FROM fez_doi_created
            INNER JOIN fez_record_search_key_author_id ON rek_author_id_pid = dcr_pid
            WHERE rek_author_id = " . $db->quote($authorId) . "
            GROUP BY dcr_pid;";

        try {
            $res = $db->fetchCol($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    public static function deleteGrants($authorId) {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "DELETE FROM fez_author_identifier_user_grants WHERE aig_author_id =  " . $db->quote($authorId);

        try {
            $res = $db->exec($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;

    }


    public static function listId($authorUsername)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "author
            WHERE aut_org_username = " . $db->quote($authorUsername) . "
            OR aut_student_username = " . $db->quote($authorUsername);

        try {
            $res = $db->fetchRow($stmt);

            if (!$res) {
              $log->info("User " . $authorUsername . " tried to access ID List without appearing in fez_author table");
              return false;
            }
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        $idDetails = ApiResearchers::idDetails($authorUsername);

        $results = array();
        $results[] = array_merge(array('value' => $res['aut_orcid_id'], status => !empty($res['aut_orcid_id']) ? 1 : null), $idDetails[1]);
        $results[] = array_merge(array('value' => $res['aut_researcher_id'], status => !empty($res['aut_researcher_id']) ? 1 : null), $idDetails[2]);
        $results[] = array_merge(array('value' => $res['aut_scopus_id'], status => !empty($res['aut_scopus_id']) ? 1 : null), $idDetails[3]);
        $results[] = array_merge(array('value' => $res['aut_orcid_id'], status => !empty($res['aut_orcid_id']) ? 1 : null), $idDetails[4]);
        return $results;
    }

    public static function saveGrantInfo($authorUsername, $idType, $name, $status, $expires, $value, $detailsDump)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $authorId = Author::getIDByUsername($authorUsername);
        $stmt = "INSERT INTO  " . APP_TABLE_PREFIX . "author_identifier_user_grants (aig_author_id, aig_id_type, aig_name, aig_status, aig_expires, aig_details, aig_created, aig_updated, aig_details_dump)
            VALUES( " . $db->quote($authorId) . ", " . $db->quote($idType) . ", " . $db->quote($name) . ", " . $db->quote($status) . ", " . $db->quote($expires) . ", " . $db->quote($value) . ", NOW(), NOW(), " . $db->quote($detailsDump) . ") ON DUPLICATE KEY UPDATE
            aig_author_id = " . $db->quote($authorId) . ", aig_id_type = " . $db->quote($idType) . ", aig_name = " . $db->quote($name) .
            ", aig_status = " . $db->quote($status) . ", aig_expires = " . $db->quote($expires) . ", aig_details = " . $db->quote($value) . ", aig_updated = NOW()" . ", aig_details_dump = " . $db->quote($detailsDump);
        try {
            $res = $db->exec($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    //Static info about  the 5 id types such as URL's like - orcid.org
    public static function idDetails($authorUsername)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT ai_id_type AS id, ai_name AS 'name', ai_description AS description, ai_url AS url, ai_image AS image FROM " . APP_TABLE_PREFIX . "author_identifier_identifiers;";
        try {
            $res1 = $db->fetchAll($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        $authorId = Author::getIDByUsername($authorUsername);

        $stmt = "SELECT ai_id_type AS 'id', aig_name AS 'name', aig_status AS 'status', aig_expires AS 'valid_until', aig_details AS 'details', aig_created AS 'created', aig_details AS 'details'
                FROM " . APP_TABLE_PREFIX . "author_identifier_identifiers
                LEFT JOIN " . APP_TABLE_PREFIX . "author_identifier_user_grants
                ON aig_author_id = " . $db->quote($authorId) . "
                WHERE aig_id_type = ai_id_type";
        try {
            $res2 = $db->fetchAll($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }


        $results = array();
        foreach ($res1 as $ids) {
            $results[$ids['id']] = $ids;
        }

        foreach ($res2 as $grant) {
            $results[$grant['id']]['grants'][] = $grant;
        }

        return $results;
    }

    public static function authorIdFromOrcid($orcId)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT aut_id FROM " . APP_TABLE_PREFIX . "author WHERE aut_orcid_id = " . $db->quote($orcId);
        try {
            $res = $db->fetchOne($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }


    /**
     * Set the "other" account for this user.
     *
     * If they are a student other is aut_org_username, if they are staff it
     * is aut_student_username
     *
     * @param      $username
     * @param null $value
     *
     * @return bool
     */
    public static function updateUQLinkedAccount($username, $value=null) {
        $db = DB_API::get();
        $isStudent = preg_match('/^s\d+$/', $username);
        $data = [];
        $where = [];

        // the username passed in will be the one they are logged in
        // as, so we assume they want to remove the "other" username
        if ($isStudent) {
            $data['aut_org_username'] = $value;
            $where[] =  'aut_student_username = ' . $db->quote($username);
        } else {
            $data['aut_student_username'] = $value;
            $where[] =  'aut_org_username = ' . $db->quote($username);
        }

        return (1 == $db->update(APP_TABLE_PREFIX . '_author', $data, $where));
    }

    /**
     * Remove the account for this user
     *
     * If they are a student other is aut_org_username, if they are staff it
     * is aut_student_username
     *
     * @param      $username
     *
     * @return bool
     */
    public static function removeUQAccount($username) {
        $db = DB_API::get();
        $isStudent = preg_match('/^s\d+$/', $username);

        // the username passed in will be the one they are logged in
        // as, so we assume they want to remove the "other" username
        if ($isStudent) {
            $where =  'aut_student_username = ' . $db->quote($username);
        } else {
            $where =  'aut_org_username = ' . $db->quote($username);
        }

        return (1 == $db->delete(APP_TABLE_PREFIX . '_author', $where));
    }


    /**
     * Unlink the "other" account for this user
     *
     * If they are a student other is aut_org_username, if they are staff it
     * is aut_student_username
     *
     * @param $username
     */
    public static function removeUQLinkedAccounts($username) {
        self::updateUQLinkedAccount($username);
    }


    /**
     * Update the "other" username with the passed in value
     *
     * If they are a student other is aut_org_username, if they are staff it
     * is aut_student_username
     *
     * @param $username
     * @param $linkedAccount
     */
    public static function linkUQAccounts($username, $linkedAccount)
    {
        // change the ownership of any objects in fez and its subsystems
        self::moveUQAccount($username, $linkedAccount);
        // remove the old account
        self::removeUQAccount($linkedAccount);
        // add new user id to their account
        self::updateUQLinkedAccount($username, $linkedAccount);
    }


    /**
     * Reassigning ownership of any objects in fez and its subsystems
     *
     * @param $username username for account which will keep data
     * @param $movingUsername
     */
    public static function moveUQAccount($username, $movingUsername)
    {
        // collect underpants
    }


    /**

    /**
     * Returns any UQ accounts which have the same staff and student ids
     *
     * @param $username
     *
     * @return array
     */
    public static function getLinkedUQAccount($username)
    {
        $db = DB_API::get();

        $select = $db->select();

        $select->from(APP_TABLE_PREFIX . 'author', [
            'aut_org_username' => 'staff_username',
            'aut_student_username' => 'student_username'
        ])
        ->where('aut_org_username = ?', $username)
        ->orWhere('aut_student_username = ?', $username);

        $stmt = $db->query($select);
        return $stmt->fetch();
    }
}
