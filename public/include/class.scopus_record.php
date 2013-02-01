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
include_once(APP_INC_PATH . "class.thomson_doctype_mappings.php");
include_once(APP_INC_PATH . "class.record_import.php");
include_once(APP_INC_PATH . "class.matching_conferences.php");

class ScopusRecItem extends RecordImport
{
    /**
     * The Fez log
     * @var FezLog
     */
    protected $_log;
    
    /**
     * Affiliations for this record
     * @var array
     */
    protected $_affiliations = array();
    
    /**
     * Default namespaces for this record
     * @var array
     */
    protected $_namesSpaces = array(
            'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
            'dc' => "http://purl.org/dc/elements/1.1/",
            'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
           );
    
    public function __construct($recordData=null)
    {
        $this->_log = FezLog::get();
        $this->_comparisonIdTypes = array('_scopusId', '_doi', '_pubmedId', '_title');
        if($recordData)
        {
            $this->load($recordData);
        }
    }

    /**
     * Set all the entry fields for the object
     * @param string $entryXML
     */

    public function load($recordData, $nameSpaces=null)
    {

        if ($nameSpaces) {
            foreach ($nameSpaces as $name => $uri) {
                $this->_namespaces[$name] = $uri;
            }
        }
        
        $this->_loaded = false;
        
        $xpath = $this->getXPath($recordData);
        $this->_doi = $this->extract('//prism:doi', $xpath);
        $this->_title = $this->extract('//dc:title', $xpath);
        
        $affiliations = $xpath->query('affiliation/affilname');
        foreach ($affiliations as $affiliation) {
            $this->_affiliations[] = $affiliation->nodeValue;
        }
        
        if ($this->likenAffiliation()) {
            $this->_pubmedId = $this->extract('//pubmed-id', $xpath);
            $this->_embaseId = $xpath->query("//itemidlist/itemid[@idtype='PUI']")->item(0)->nodeValue;
            $this->_scopusCitationCount = $this->extract('//citedby-count', $xpath);
            $this->_issn = $this->extract('//prism:issn', $xpath);
            $this->_issueVolume = $this->extract('//prism:volume', $xpath);
            $date = $this->extract('//prism:coverDate', $xpath);
            $this->_issueDate = date('Y-m-d', strtotime($date));
            $scopusDocTypeExtracted = $this->extract('//prism:aggregationType', $xpath);
            $scopusDocTypeMatched = Record::getScopusDocTypeCodeByDescription($scopusDocTypeExtracted);
            
            if($scopusDocTypeMatched)
            {
                $this->_scopusDocType = $scopusDocTypeMatched;
            }
            
            $this->_scopusDocTypeCode = $xpath->query('//head/citation-info/citation-type/@code')->item(0)->nodeValue;
            $this->enterXdisInformation($this->_scopusDocTypeCode);
            $this->_journalTitle = $this->extract('//source/sourcetitle', $xpath);
            $this->_isbn = $this->extract('//source/isbn', $xpath);
            $this->_journalTitleAbbreviation = $this->extract('//source/sourcetitle-abbrev', $xpath);
            $this->_languageCode = $xpath->query('//head/citation-info/citation-language/@xml:lang')->item(0)->nodeValue;

            $authors= $xpath->query('//authors/author');
            foreach ($authors as $author) {
                $this->_authors[] = $author->getElementsByTagName('indexed-name')->item(0)->nodeValue;
            }


            $scopusId = $this->extract('//dc:identifier', $xpath);
            $matches = array();
            preg_match("/^SCOPUS_ID\:(\d+)$/", $scopusId, $matches);
            $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;
            $this->_scopusId = "2-s2.0-" . $scopusIdExtracted;
            $this->_issueNumber = $this->extract('//prism:issueIdentifier', $xpath);
            $this->_startPage = $this->extract('//prism:startingPage', $xpath);
            $this->_endPage = $this->extract('//prism:endingPage', $xpath);
            $this->_totalPages = $this->_endPage - $this->_startPage;

            if ($xpath->query('//source/additional-srcinfo/conferenceinfo')->length > 0) {
                $this->_conferenceTitle = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confname')->item(0)->nodeValue;
                $this->_confenceLocationCity = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/conflocation/city-group')->item(0)->nodeValue;

                $startDay =  $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/startdate/@day', $embaseArticle)->item(0)->nodeValue;
                $startMonth = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/startdate/@month', $embaseArticle)->item(0)->nodeValue;
                $startYear = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/startdate/@year', $embaseArticle)->item(0)->nodeValue;
                $endDay =  $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/enddate/@day', $embaseArticle)->item(0)->nodeValue;
                $endMonth = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/enddate/@month', $embaseArticle)->item(0)->nodeValue;
                $endYear = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/enddate/@year', $embaseArticle)->item(0)->nodeValue;

                //We'll only save dates if they are not incomplete
                if (!empty($startDay) && !empty($startMonth) && !empty($startYear)) {
                    $this->_conferenceDates = date('F j, Y', strtotime($startDay.'-'.$startMonth.'-'.$startYear));
                }
                if (!empty($endDay) && !empty($endMonth) && !empty($endYear)) {
                    $this->_conferenceDates .= '-'.date('F j, Y',strtotime($endDay.'-'.$endMonth.'-'.$endYear));
                }
            }

            $this->_loaded = true;
        }
        else {
            $this->_log->err("Problem with affiliation for: {$this->_title}; Affiliations: " . var_export($this->_affiliations, true));
        }
    }
    
    public function extract($query, $xpath)
    {
        $nodeList = $xpath->query($query);
        $val = $nodeList->item(0)->nodeValue;
        return $val;
    }
    
    /**
     * Getter overload
     * @param string $propName
     */
    public function __get($propName)
    {
        return $this->$propName;
    }
    
    /**
     * Double check to make sure this is really ours
     * @param string $recordData
     * @return boolean
     */
    public function likenAffiliation()
    {
        $affiliated = false;
        
        foreach($this->_affiliations as $affiliation)
        {
            if(preg_match('/(University of Queensland)|(University of Qld)/',
                                                       $affiliation))
            {
                $affiliated = true;
            }
        }
        return $affiliated;
    }
    private function enterXdisInformation($docType) {
        if ($docType == 'ar') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article';
        } elseif ($docType == 'ab') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article';
        } elseif ($docType == 'ip') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article';
        } elseif ($docType == 'bk') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article';
        } elseif ($docType == 'bz') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article';
        } elseif ($docType == 'cp') {
            $this->_xdisTitle = 'Conference Paper';
            $this->_xdisSubtype = 'Fully Published Paper';
        } elseif ($docType == 'cr') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article';
        } elseif ($docType == 'ed') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Editorial';
        } elseif ($docType == 'er') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Correction/erratum';
        } elseif ($docType == 'le') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Letter';
        } elseif ($docType == 'no') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Other (News item, press release, note, obituary, other not liste';
        } elseif ($docType == 'pr') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article';
        } elseif ($docType == 'rp') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Review of research - research literature review (NOT book review';
        } elseif ($docType == 're') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Review of research - research literature review (NOT book review';
        } elseif ($docType == 'sh') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Review of research - research literature review (NOT book review';
        }
    }

}

