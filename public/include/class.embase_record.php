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
// | Authors: Chris Maj <c.maj@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.language.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.matching_conferences.php");
include_once(APP_INC_PATH . "class.record_item.php");

/**
 * Class for working with the Embase REC item object
 *
 * @version 0.1
 * @author Aaron Brown
 *
 */


class EmbaseRecItem extends RecordItem
{
    public function load($embaseArticle, $nameSpaces=null)
    {
        $this->_importAPI = 'Embase';
        $this->_collections = array(0 => 'UQ:88095'); //APP_EMBASE_COLLECTIONS;
        $xpath = new DOMXPath($embaseArticle->parentNode->ownerDocument);
        $xpath->registerNamespace('x', 'http://www.elsevier.com/xml/ani/ani');
        //$xpath = new DOMXPath($embaseArticle);
        $this->_title = $xpath->query('x:bibrecord/x:head/x:citation-title/x:titletext', $embaseArticle)->item(0)->nodeValue;
        $this->_journalTitle = $xpath->query('x:bibrecord/x:head/x:source/x:sourcetitle', $embaseArticle)->item(0)->nodeValue;
        $this->_journalTitleAbbreviation = $xpath->query('x:bibrecord/x:head/x:source/x:sourcetitle-abbrev', $embaseArticle)->item(0)->nodeValue;
        $this->_issn = $xpath->query('x:bibrecord/x:head/x:source/x:issn', $embaseArticle)->item(0)->nodeValue;
        $this->_isbn = $xpath->query('x:bibrecord/x:head/x:source/x:isbn', $embaseArticle)->item(0)->nodeValue;
        $this->_doi = $xpath->query('x:bibrecord/x:item-info/x:itemidlist/ce:doi', $embaseArticle)->item(0)->nodeValue;
        $this->_embaseId = $xpath->query("x:bibrecord/x:item-info/x:itemidlist/x:itemid[@idtype='PUI']", $embaseArticle)->item(0)->nodeValue;
        $this->_pubmedId = $xpath->query("x:bibrecord/x:item-info/x:itemidlist/x:itemid[@idtype='MEDL']", $embaseArticle)->item(0)->nodeValue;
        $language = $xpath->query('x:bibrecord/x:head/x:citation-info/x:citation-language/@xml:lang', $embaseArticle)->item(0)->nodeValue;
        $this->_languageCode = Language::resolveAlpha3FromAlpha2($language);
        $this->_issueVolume = $xpath->query('x:bibrecord/x:head/x:source/x:volisspag/x:voliss/@volume', $embaseArticle)->item(0)->nodeValue;
        $this->_issueNumber = $xpath->query('x:bibrecord/x:head/x:source/x:volisspag/x:voliss/@issue', $embaseArticle)->item(0)->nodeValue;
        $this->_startPage = $xpath->query('x:bibrecord/x:head/x:source/x:volisspag/x:pagerange/@first', $embaseArticle)->item(0)->nodeValue;
        $this->_endPage = $xpath->query('x:bibrecord/x:head/x:source/x:volisspag/x:pagerange/@last', $embaseArticle)->item(0)->nodeValue;
        $this->_publisher = $xpath->query('x:bibrecord/x:head/x:source/x:publisher/x:publishername', $embaseArticle)->item(0)->nodeValue;

        $date = $xpath->query('x:bibrecord/x:head/x:source/x:publicationdate/x:date-text', $embaseArticle)->item(0)->nodeValue;
        if (empty($date)) {
            $issueDay =  $xpath->query('x:bibrecord/x:head/x:source/x:publicationdate/x:day', $embaseArticle)->item(0)->nodeValue;
            $issueMonth = $xpath->query('x:bibrecord/x:head/x:source/x:publicationdate/x:month', $embaseArticle)->item(0)->nodeValue;
            $issueYear = $xpath->query('x:bibrecord/x:head/x:source/x:publicationdate/x:year', $embaseArticle)->item(0)->nodeValue;
            if (!empty($issueDay)) {
                $date = $issueDay.'-'.$issueMonth.'-'.$issueYear;
            } elseif (!empty($issueMonth)) {
                $date = '1-'.$issueMonth.'-'.$issueYear;
            } else {
                $date = '1-1-'.$issueYear;
            }
        }
        $this->_issueDate = date('Y-m-d',strtotime($date));
        $authors= $xpath->query('x:bibrecord/x:head/x:author-group/x:author', $embaseArticle);
        foreach ($authors as $author) {
            //$firstName = $author->getAttribute('LastName');
            $lastName = $author->getElementsByTagName('surname')->item(0)->nodeValue;
            $initials = $author->getElementsByTagName('initials')->item(0)->nodeValue;
            $this->_authors[] = $lastName.', '.$initials;
        }

        if ($xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo', $embaseArticle)->length > 0) {
            $this->_conferenceTitle = $xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo/x:confevent/x:confname', $embaseArticle)->item(0)->nodeValue;
            $this->_confenceLocationCity = $xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo/x:confevent/x:conflocation/x:city-group', $embaseArticle)->item(0)->nodeValue;
            //$this->_confenceLocationState = $xpath->query('x:bibrecord/x:head/x:source/x:additional/x:additional-srcinfo/x:conferenceinfo/confevent/confname', $embaseArticle)->item(0)->nodeValue;

            $startDay =  $xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo/x:confevent/x:confdate/x:startdate/@day', $embaseArticle)->item(0)->nodeValue;
            $startMonth = $xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo/x:confevent/x:confdate/x:startdate/@month', $embaseArticle)->item(0)->nodeValue;
            $startYear = $xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo/x:confevent/x:confdate/x:startdate/@year', $embaseArticle)->item(0)->nodeValue;
            $endDay =  $xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo/x:confevent/x:confdate/x:enddate/@day', $embaseArticle)->item(0)->nodeValue;
            $endMonth = $xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo/x:confevent/x:confdate/x:enddate/@month', $embaseArticle)->item(0)->nodeValue;
            $endYear = $xpath->query('x:bibrecord/x:head/x:source/x:additional-srcinfo/x:conferenceinfo/x:confevent/x:confdate/x:enddate/@year', $embaseArticle)->item(0)->nodeValue;

            //We'll only save dates if they are not incomplete
            if (!empty($startDay) && !empty($startMonth) && !empty($startYear)) {
                $this->_conferenceDates = date('F j, Y', strtotime($startDay.'-'.$startMonth.'-'.$startYear));
            }
            if (!empty($endDay) && !empty($endMonth) && !empty($endYear)) {
                $this->_conferenceDates .= '-'.date('F j, Y',strtotime($endDay.'-'.$endMonth.'-'.$endYear));
            }
            $this->_xdisTitle = 'Conference Paper';
        }


        $sourceType =  $xpath->query('x:bibrecord/x:head/x:source/@type', $embaseArticle)->item(0)->nodeValue;
        if (empty($this->_xdisTitle)) {
            //Assume all other returned are Journal Articles
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article';
        }

        $this->_loaded = TRUE;
    }

    //The default returned values, data is sparce
    public function loadDefault($embaseArticle, $nameSpaces=null)
    {
        $this->_importAPI = 'Embase';
        $this->_collections = 'UQ:88095'; //APP_EMBASE_COLLECTIONS;
        $xpath = new DOMXPath($embaseArticle->parentNode->ownerDocument);
        
        $this->_title = $xpath->query('bib:cardfields/bib:Title', $embaseArticle)->item(0)->nodeValue;
        $this->_journalTitle = $xpath->query('bib:cardfields/bib:JournalTitle', $embaseArticle)->item(0)->nodeValue;
        $this->_journalTitleAbbreviation = $xpath->query('bib:cardfields/bib:JournalTitleAbbrev', $embaseArticle)->item(0)->nodeValue;
        $this->_issueNumber = $xpath->query('bib:cardfields/bib:Issue', $embaseArticle)->item(0)->nodeValue;
        $this->_issueVolume = $xpath->query('bib:cardfields/bib:Volume', $embaseArticle)->item(0)->nodeValue;
        $this->_issn = $xpath->query('bib:cardfields/bib:ISSN', $embaseArticle)->item(0)->nodeValue;
        $this->_isbn = $xpath->query('bib:cardfields/bib:ISBN', $embaseArticle)->item(0)->nodeValue;
        $this->_embaseId= $xpath->query('bib:cardfields/bib:UID', $embaseArticle)->item(0)->nodeValue;
        $this->_doi = $xpath->query('bib:cardfields/bib:Fulltext/bib:DOI', $embaseArticle)->item(0)->nodeValue;
        $this->_startPage = $xpath->query('bib:cardfields/bib:Pagination/bib:startpage', $embaseArticle)->item(0)->nodeValue;
        $this->_endPage = $xpath->query('bib:cardfields/bib:Pagination/bib:endpage', $embaseArticle)->item(0)->nodeValue;

        $issueDay =  $xpath->query('bib:cardfields/bib:PubDate/bib:day', $embaseArticle)->item(0)->nodeValue;
        $issueMonth = $xpath->query('bib:cardfields/bib:PubDate/bib:month', $embaseArticle)->item(0)->nodeValue;
        $issueYear = $xpath->query('bib:cardfields/bib:PubDate/bib:year', $embaseArticle)->item(0)->nodeValue;
        $this->_issueDate = strtotime($issueDay.'-'.$issueMonth.'-'.$issueYear);


        $authors= $xpath->query('bib:cardfields/bib:Authors/bib:author', $embaseArticle);
        foreach ($authors as $author) {
            //$firstName = $author->getAttribute('LastName');
            $lastName = $author->getElementsByTagName('bib:lastname')->item(0)->nodeValue;
            $initials = $author->getElementsByTagName('bib:initials')->item(0)->nodeValue;
            $this->_authors[] = $lastName.', '.$initials;
        }

        //Assume all returned are Journal Articles
        $this->_xdisTitle = 'Journal Article';
        $this->_xdisSubtype = 'Article';

        $this->_loaded = TRUE;
    }


    public function returnDoi()
    {
        return $this->_doi;
    }
}
