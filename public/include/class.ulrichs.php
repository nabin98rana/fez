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
include_once(APP_INC_PATH . "ForceUTF8/Encoding.php");
class Ulrichs
{
    function getXMLFromUlrichs($searchTerm)
    {
        $log = FezLog::get();
        if (!empty($searchTerm)) {
            $uRL = ULRICHS_URL.ULRICHS_API_KEY.'/search?query='.urlencode($searchTerm);
            $ch = curl_init($uRL);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml'));
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $log->err("Ulrichs timeout or error in getXMLFromUlrichs");
                return false;
            } else {
                curl_close($ch);
                return $response;
            }
        }
        return false;
    }
    function loadXMLData($issn)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT ulr_xml FROM " . APP_TABLE_PREFIX . "ulrichs where ulr_issn =".$db->quote($issn);
        try {
            $res = $db->fetchOne($stmt);
        } catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    //This function currently assumes total records found will be one for a issn
    function saveXMLData($xml)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $xmlDoc = new DomDocument();
        $xmlDoc->loadXML($xml);
        $status = $xmlDoc->getElementsByTagName("status")->item(0)->nodeValue;
        $totalRecords = $xmlDoc->getElementsByTagName("totalRecords")->item(0)->nodeValue;
        if ($status == "Success" && $totalRecords > 0) {
            $title = $xmlDoc->getElementsByTagName("title")->item(0)->nodeValue;
            $titleId = $xmlDoc->getElementsByTagName("titleId")->item(0)->nodeValue;
            $issn = $xmlDoc->getElementsByTagName("issn")->item(0)->nodeValue;
            $openAccess = $xmlDoc->getElementsByTagName("openAccess")->item(0)->nodeValue;
            $reviewed = $xmlDoc->getElementsByTagName("reviewed")->item(0)->nodeValue;
            $country = $xmlDoc->getElementsByTagName("country")->item(0)->nodeValue;
            $publisher = $xmlDoc->getElementsByTagName("publisher")->item(0)->nodeValue;

            $stmt = "INSERT INTO
                        " . APP_TABLE_PREFIX . "ulrichs
                     (
                        ulr_title,
                        ulr_title_id,
                        ulr_issn,
                        ulr_open_access,
                        ulr_reviewed,
                        ulr_country,
                        ulr_publisher,
                        ulr_xml,
                        ulr_last_updated
                     ) VALUES (
                        " . $db->quote($title) . ",
                        " . $db->quote($titleId) . ",
                        " . $db->quote($issn) . ",
                        " . $db->quote($openAccess) . ",
                        " . $db->quote($reviewed) . ",
                        " . $db->quote($country) . ",
                        " . $db->quote($publisher) . ",
                        " . $db->quote($xml) . ",
                            now() )
                     ON DUPLICATE KEY UPDATE
                     ulr_title = " . $db->quote($title) . ",
                     ulr_title_id = " . $db->quote($titleId) . ",
                     ulr_open_access = " . $db->quote($openAccess) . ",
                     ulr_reviewed = " . $db->quote($reviewed) . ",
                     ulr_country = " . $db->quote($country) . ",
                     ulr_publisher = " . $db->quote($publisher) . ",
                     ulr_xml = " . $db->quote($xml) . ",
                     ulr_last_updated = now()";
            $stmt = Encoding::toUTF8($stmt);
            try {
                $db->exec($stmt);
            }
            catch(Exception $ex) {
                $log->err($ex);
                return false;
            }
            return true;
        } else if ($status != "Success"){
            $log->err("Ulrichs saveXMLData fail on XML:".$xml);
            return false;
        }
    }

    /* This function will get all ISSN from the pids and fill the Ulrichs table with data.
     */
    function getDataFromUlrichs($reloadAll=false) {
        $log = FezLog::get();
        $db = DB_API::get();
        $regexp = '/[0-9]{4}-[0-9]{3}[0-9X]/';
        if ($reloadAll) {
            $stmt = "SELECT DISTINCT issn FROM (SELECT rek_issn AS issn FROM " . APP_TABLE_PREFIX . "record_search_key_issn UNION SELECT jni_issn FROM " . APP_TABLE_PREFIX . "journal_issns) AS T3";
        } else {
            $stmt = "SELECT DISTINCT rek_issn AS issn FROM " . APP_TABLE_PREFIX . "record_search_key_issn
            LEFT JOIN " . APP_TABLE_PREFIX . "journal_issns
            ON rek_issn = jni_issn WHERE jni_issn IS NULL";
        }
        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        $ulrichs = new Ulrichs();

        foreach ($res as $journal) {
            preg_match_all($regexp, $journal['issn'], $matches);
            foreach ($matches[0] as $match) {
                if ($ulrichs::loadXMLData($match) && !$reloadAll) {
                    continue;
                }
                $xml = $ulrichs::getXMLFromUlrichs($match);
                if (!empty($xml)) {
                    $ulrichs::saveXMLData($xml, $match);
                }
            }
        }
    }

    //Method used to find suggested ulrichs open access info
    function openAccessStatus($issn)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (!preg_match('/[0-9]{4}-[0-9]{3}[0-9X]/', $issn)) {
            return false;
        }
        $ulrichs = new Ulrichs();
        //If it's not in the database we'll try and download it
        if (!$ulrichs::loadXMLData($issn)) {
            $xml = $ulrichs::getXMLFromUlrichs($issn);
            if (!empty($xml)) {
                $ulrichs::saveXMLData($xml, $issn);
            }
        }
        $stmt = "SELECT
                    ulr_open_access
                 FROM
                    " . APP_TABLE_PREFIX . "ulrichs
                 WHERE
                    ulr_issn = ".$db->quote($issn);
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    //Method used to find suggested ulrichs open access status
    function link($issn)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (!preg_match('/[0-9]{4}-[0-9]{3}[0-9X]/', $issn)) {
            return false;
        }
        $ulrichs = new Ulrichs();
        //If it's not in the database we'll try and download it
        if (!$ulrichs::loadXMLData($issn)) {
            $xml = $ulrichs::getXMLFromUlrichs($issn);
            if (!empty($xml)) {
                $ulrichs::saveXMLData($xml, $issn);
            }
        }
        $stmt = "SELECT
                    ulr_title_id
                 FROM
                    " . APP_TABLE_PREFIX . "ulrichs
                 WHERE
                    ulr_issn = ".$db->quote($issn);
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    //returns the Embargo and OA Status info given a issn. It returns the info with xsdmf_id so it can be inserted as values on the enter page.
    function getEmbarboStatusInfo($xsdmf_id, $issn) {
        $status = Ulrichs::openAccessStatus($issn);
        if (!$status || $status == 'false') {
            return null;
        } else {
            $xsdmf_idOAStatus = XSD_HTML_Match::getXSDMFIDByTitleXDIS_ID('Open Access Status', XSD_HTML_Match::getXDIS_IDByXSDMF_ID($xsdmf_id));
            $xsdmf_idEmbargo = XSD_HTML_Match::getXSDMFIDByTitleXDIS_ID('Embargo Days', XSD_HTML_Match::getXDIS_IDByXSDMF_ID($xsdmf_id));
            $return['embargo_xsdmf_id'] = $xsdmf_idEmbargo;
            $return['oastatus_xsdmf_id'] = $xsdmf_idOAStatus;
            $return['title_id'] = Ulrichs::getTitleId($issn);
            return $return;
        }
    }

    //Method used to find suggested ulrichs open access info
    function getTitleId($issn)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (!preg_match('/[0-9]{4}-[0-9]{3}[0-9X]/', $issn)) {
            return false;
        }
        $ulrichs = new Ulrichs();
        //If it's not in the database we'll try and download it
        if (!$ulrichs::loadXMLData($issn)) {
            $xml = $ulrichs::getXMLFromUlrichs($issn);
            if (!empty($xml)) {
                $ulrichs::saveXMLData($xml, $issn);
            }
        }
        $stmt = "SELECT
                    ulr_title_id
                 FROM
                    " . APP_TABLE_PREFIX . "ulrichs
                 WHERE
                    ulr_issn = ".$db->quote($issn);
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    function getStatusFormated($code) {
        return ($code == "on") ? "Yes" : "No";
    }

}
