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

//Class for crossref
class Crossref
{
    //Returns xml to upload or false if error
    private function loadXML($details, $doi, $xdis_id_name)
    {
        //crossref Data
        //define(APP_ORG_ACRONYM, 'UQ');
        foreach($details[0]['rek_author'] as $i => $author ) {
            if ($details[0]['rek_author_id'][$i]) {
                $details[0]['rek_author_firstname'][$i] = Author::getFirstname($details[0]['rek_author_id'][$i]);
                $details[0]['rek_author_lastname'][$i] = Author::getLastname($details[0]['rek_author_id'][$i]);
            } else {
                $names = Author::guessFirstLastName($author);
                $details[0]['rek_author_firstname'][$i] = $names['firstname'];
                $details[0]['rek_author_lastname'][$i] = $names['lastname'];
            }
        }

        $publishedDate = $details[0]['rek_date'];

        $tpl = new Template_API();
        if ($xdis_id_name == 'Thesis') {
            $tpl->setTemplate("workflow/crossref_4_3_3_thesis_xml.tpl.html");
        } else {
            $tpl->setTemplate("workflow/crossref_4_3_3_dataset_xml.tpl.html");
        }
        $tpl->assign("details", $details[0]);
        $uniqid = uniqid();
        $tpl->assign("uniqid", $uniqid);
        //$tpl->assign("org_acronym", APP_ORG_ACRONYM);
        $tpl->assign("published_day",date('d', strtotime($publishedDate)));
        $tpl->assign("published_month",date('m', strtotime($publishedDate)));
        $tpl->assign("published_year",date('Y', strtotime($publishedDate)));
        $tpl->assign("timestamp", time());
        $tpl->assign("doi", $doi);
        $tpl->assign("link", 'http://'.APP_HOSTNAME.'/view/'.$details[0]['rek_pid']);
        $tpl->assign("depositor_full_name", Auth::getUserFullName());
        $tpl->assign("depositor_email", Auth::getUserEmail());

        return $tpl->getTemplateContents();
    }

    //Returns xml to upload or false if error
    public function xmlForPid($pid, $doi, $xdis_id_name)
    {
        $record = new Record($pid);
        $result[0]["rek_pid"] = $pid;
        $details = $record->getSearchKeysByPIDS($result, true);

        return $this->loadXML($result, $doi, $xdis_id_name);
    }

    public function upload($xml)
    {
        $log = FezLog::get();
        $header[] = "Content-type: text/xml";
        $ch = curl_init(CROSSREF_UPLOAD_SERVICE."?operation=doMDUpload&login_id=".CROSSREF_SERVICE_USERNAME."&login_passwd=".CROSSREF_SERVICE_PASSWORD."&area=live");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (APP_HTTPS_CURL_CHECK_CERT == 'OFF') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        //Can't upload a variable as a file so -
        $tmpfname = tempnam(APP_TEMP_DIR, "crossref");
        file_put_contents($tmpfname, $xml);
        // call your cURL post, using $tmpfname as your source file
        $post = array(
            "fname"=>'@'.$tmpfname,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpStatus != '200') {
            $log->err("Crossref Upload Status: ".$httpStatus." Response: ".$response." XML: ".$xml, __FILE__, __LINE__);
        }

        unlink($tmpfname);
        if (curl_errno($ch)) {
            $log->err(array(curl_error($ch)." Crossref Upload ", __FILE__, __LINE__));
            return false;
        }

        return $response;

    }

    public function getNextDoi()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT dcr_doi_year, dcr_doi_num FROM " . APP_TABLE_PREFIX . "doi_created ORDER BY dcr_doi_year DESC, dcr_doi_num DESC LIMIT 1";

        try {
            $res = $db->fetchRow($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        $lastNumber = $res['dcr_doi_num'];
        if ($res['dcr_doi_year'] < date("Y")) {
            $lastNumber = 1;
        } else {
            $lastNumber++;
        }
        //return CROSSREF_DOI_PREFIX.'.'.date("Y").'.'.$lastNumber;
        return CROSSREF_DOI_PREFIX.'.2013.10';
    }

    //Save NEW doi's into the database
    public function saveDoi($pid, $doi, $user)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        //We will search and remove the front of the DOI including the year then return it + 1 or if it's a new year we reset the value.
        $lastNumber = preg_replace("/".preg_quote(CROSSREF_DOI_PREFIX,'/')."\.\d\d\d\d\./", '', $doi);
        preg_match("/\.\d\d\d\d\./", $doi, $matches);
        $year=substr($matches[0], 1, 4);

        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "doi_created (dcr_pid, dcr_doi_year, dcr_doi_num, dcr_creator, dcr_date) VALUES
                    (" . $db->quote($pid) .",".
                    $db->quote($year)  .",".
                    $db->quote($lastNumber)  .",".
                    $db->quote($user) .",".
                    "NOW()".
                    ")";
        try {
            $res = $db->query($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        if ($doi != $this->getNextDoi()) {
            $log->err("Warning DOI save was out of order".$doi.' '.$this->getNextDoi().' '.$user.' '.$pid);
        }
        return $res;
    }

    //If the pid has a DOI return
    public function hasDoi($pid)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT dcr_doi_year, dcr_doi_num FROM " . APP_TABLE_PREFIX . "doi_created WHERE dcr_pid = ".$db->quote($pid);

        try {
            $res = $db->fetchRow($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return (is_array($res)) ? CROSSREF_DOI_PREFIX.'.'.$res['dcr_doi_year'].'.'.$res['dcr_doi_num'] : false;
    }
}