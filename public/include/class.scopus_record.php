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
    protected $_namespaces = array(
            'd' => 'http://www.elsevier.com/xml/svapi/abstract/dtd',
            'ce' => 'http://www.elsevier.com/xml/ani/common',
            'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
            'dc' => "http://purl.org/dc/elements/1.1/",
            'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
           );

    public function __construct($recordData=null)
    {
        $this->_log = FezLog::get();
        $this->_comparisonIdTypes = array('_scopusId', '_doi', '_pubmedId', '_title');
        $this->_doctypeExceptions = array('ip');
        $this->_primaryIdPrefix = 'scopus';
        $this->_insertCollection = APP_SCOPUS_IMPORT_COLLECTION;
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

        $affiliations = $xpath->query('//d:affiliation/d:affilname');
        foreach ($affiliations as $affiliation) {
            $this->_affiliations[] = $affiliation->nodeValue;
        }

//        if ($this->likenAffiliation()) {
            $this->_pubmedId = $this->extract('//d:pubmed-id', $xpath);
            $this->_embaseId = $xpath->query("//d:itemidlist/d:itemid[@idtype='PUI']")->item(0)->nodeValue;
            $this->_scopusCitationCount = $this->extract('//d:citedby-count', $xpath);
            $this->_issn = $this->extract('//prism:issn', $xpath);
            //scopus ISSNs don't have a - in the middle, so add it
            if (strlen($this->_issn) == 8) {
              $this->_issn = substr($this->_issn, 0, 4) . '-'.substr($this->_issn, 4, 4);
            }

            $this->_issueVolume = $this->extract('//prism:volume', $xpath);
            $date = $this->extract('//prism:coverDate', $xpath);
            $this->_issueDate = date('Y-m-d', strtotime($date));
            $scopusDocTypeExtracted = $this->extract('//prism:aggregationType', $xpath);
            $this->_scopusAggregationType = $scopusDocTypeExtracted;
//            $scopusDocTypeMatched = Record::getScopusDocTypeCodeByDescription($scopusDocTypeExtracted);

//            if($scopusDocTypeMatched)
//            {
//                $this->_scopusDocType = $scopusDocTypeMatched;
//            } else {
//              return false;
//            }

            $this->enterXdisInformation($this->_scopusDocTypeCode);
            $this->_journalTitle = $this->extract('//prism:publicationName', $xpath);
            $this->_isbn = $this->extract('//isbn', $xpath);
//            $this->_journalTitleAbbreviation = $this->extract('//prism:publicationName-abbrev', $xpath);
            $this->_languageCode = $xpath->query('//head/citation-info/citation-language/@xml:lang')->item(0)->nodeValue;
//            $this->_languageCode = $this->extract('/d:abstracts-retrieval-response/item/bibrecord/head/citation-info/citation-language', $xpath);

            $keywords = $xpath->query('//head/citation-info/author-keywords/author-keyword');
            foreach ($keywords as $keyword) {
              $this->_keywords[] = $keyword->nodeValue;
            }

            $authors = $xpath->query('/d:abstracts-retrieval-response/d:authors/d:author/ce:indexed-name');
            foreach ($authors as $author) {
                $this->_authors[] = $author->nodeValue;
            }
            // if you don't use the FULL abstract response you won't get the <authors> element so, get only the first author from <dc:creator> instead
            if (count($this->_authors) == 0) {
              $authors = $xpath->query('//dc:creator');
              foreach ($authors as $author) {
                $this->_authors[] = $author->nodeValue;
              }
            }


            $scopusId = $this->extract('//dc:identifier', $xpath);
            $matches = array();
            preg_match("/^SCOPUS_ID\:(\d+)$/", $scopusId, $matches);
            $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;
            $this->_scopusId = "2-s2.0-" . $scopusIdExtracted;
            $this->_issueNumber = $this->extract('//prism:issueIdentifier', $xpath);
            $this->_startPage = $this->extract('//prism:startingPage', $xpath);
            $this->_endPage = $this->extract('//prism:endingPage', $xpath);
            $this->_totalPages = $this->extract('//pagecount', $xpath);
            if (!is_numeric($this->_totalPages)) {
              $this->_totalPages = ($this->_endPage - $this->_startPage) + 1;
            }


            $this->_abstract = $this->extract('/d:abstracts-retrieval-response/d:coredata/dc:description/d:abstract/ce:para', $xpath);
            $this->_abstract = substr($this->_abstract, 0, strrpos($this->_abstract, '©'));
            $subjects = $xpath->query('/d:abstracts-retrieval-response/d:subject-areas/d:subject-area[@code]');
            foreach ($subjects as $subject) {
              $this->_subjects[] = Controlled_Vocab::getInternalIDByExternalID($subject->getAttribute('code'));
            }

            // This section seems to only be for embase, so commenting out for now, maybe remove later
            if ($xpath->query('//source/additional-srcinfo/conferenceinfo')->length > 0) {
                $this->_conferenceTitle = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confname')->item(0)->nodeValue;
                $this->_confenceLocationCity = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/conflocation/city-group')->item(0)->nodeValue;
                $this->_conferenceProceedingsTitle = $xpath->query('//source/sourcetitle')->item(0)->nodeValue;
                $startDay =  $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/startdate/@day')->item(0)->nodeValue;
                $startMonth = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/startdate/@month')->item(0)->nodeValue;
                $startYear = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/startdate/@year')->item(0)->nodeValue;
                $endDay =  $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/enddate/@day')->item(0)->nodeValue;
                $endMonth = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/enddate/@month')->item(0)->nodeValue;
                $endYear = $xpath->query('//source/additional-srcinfo/conferenceinfo/confevent/confdate/enddate/@year')->item(0)->nodeValue;

                //We'll only save dates if they are not incomplete
                if (!empty($startDay) && !empty($startMonth) && !empty($startYear)) {
                    $this->_conferenceDates = date('F j, Y', strtotime($startDay.'-'.$startMonth.'-'.$startYear));
                }
                if (!empty($endDay) && !empty($endMonth) && !empty($endYear)) {
                    $this->_conferenceDates .= '-'.date('F j, Y',strtotime($endDay.'-'.$endMonth.'-'.$endYear));
                }
            }

            $this->_loaded = true;
//        }
//        else
//        {
//            $this->_log->err("Problem with affiliation for: {$this->_title}; Affiliations: " . var_export($this->_affiliations, true));
//        }
    }

    /**
    * Map a log message to a second stage dedupe status code (ie ST10+)
    * @param array $searchData
    */
    public function getFuzzySearchStatus(array $searchData)
    {
        $statusMessages = array(
        1 => 'ST10 - Matched on fuzzy title, DOI, start page, end page, issue, volume. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null.',
        101 => 'ST11 - Matched on fuzzy title, DOI, start page, end page, issue, volume. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s.',
        2 => 'ST12 - Matched on fuzzy title, DOI, start page, end page, volume and issue. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null',
        102 => 'ST13 - Matched on fuzzy title, DOI, start page, end page, volume and issue. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s',
        3 => 'ST14 - Matched on fuzzy title, start page, end page, volume and issue. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null',
        103 => 'ST15 - Matched on fuzzy title, start page, end page, volume and issue. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s',
        4 => 'ST16 - Matched on fuzzy title, DOI, start page and volume. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null',
        104 => 'ST17 - Matched on fuzzy title, DOI, start page and volume. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s',
        5 => 'ST18 - Matched on fuzzy title, DOI, start page issue. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null',
        105 => 'ST19 - Matched on fuzzy title, DOI, start page issue. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s',
        6 => 'ST20 - Matched on fuzzy title, start page volume and issue. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null',
        106 => 'ST21 - Matched on fuzzy title, start page volume and issue. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s',
        7 => 'ST22 - Matched on fuzzy title, DOI and start page. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null.',
        107 => 'ST23 - Matched on fuzzy title, DOI and start page. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s.',
        8 => 'ST24 - Matched on fuzzy title, DOI. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null.',
        108 => 'ST25 - Matched on fuzzy title, DOI. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s.',
        9 => 'ST26 - Matched on fuzzy title. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null.',
        109 => 'ST27 - Matched on fuzzy title. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s.',
        10 => 'ST28 - Matched on DOI, start page, end page, issue, volume. Scopus ID in the downloaded record was %s. Scopus ID in the local record was null.',
        110 => 'ST29 - Matched on DOI, start page, end page, issue, volume. Scopus ID in the downloaded record was %s. Scopus ID in the local record was %s.',
        );

        $statuses = array();

        $scopusIdDL = ($this->_scopusId) ? $this->_scopusId : 'empty';

        foreach($searchData['data'] as $localRecord)
        {
            $scopusIdLocal = (preg_match("/2\-s2\.0\-\d+/", $localRecord['rek_scopus_id'])) ? $localRecord['rek_scopus_id'] : 'empty';

            if(is_null($localRecord['rek_scopus_id'])
                || strtolower($localRecord['rek_scopus_id']) == 'null'
                || !preg_match("/2\-s2\.0\-\d+/", $localRecord['rek_scopus_id']))
            {
                $statusMessage = sprintf($statusMessages[$searchData['state']], $scopusIdDL);
            }
            else
            {
                $statusMessage = sprintf($statusMessages[$searchData['state']+100], $scopusIdDL, $scopusIdLocal);
            }

            $statuses[] = $statusMessage . " Pid matched: " . $localRecord['rek_pid'];
        }

        return $statuses;
    }

    /**
    * Check to see if a record already resides in a import collection
    * based on Scopus ID
    */
    protected function checkImportCollections()
    {
        return Record::getPIDsByScopusID($this->_scopusId, true);
    }

    /*
     * Retrieve a value from a node list using xpath.
     * @param string $query
     * @param DOMXPath $xpath
     */
    public function extract($query, $xpath)
    {
        $nodeList = $xpath->query($query);
        if ($nodeList->length > 0) {
          $val = $nodeList->item(0)->nodeValue;
        } else {
          $val = null;
        }

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
   * Getter overload
   * @param string $propName
   */
  public function __set($propName, $value)
  {
    $this->$propName = $value;
  }

    /**
     * Double check to make sure this is really ours
     * @param string $recordData
     * @return boolean
     */
    protected function likenAffiliation()
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
            $this->_xdisSubtype = 'Article (original research)';
            $this->_xdisId = 179;
        } elseif ($docType == 'ip') {
            $this->_xdisTitle = 'Journal Article';
            $this->_xdisSubtype = 'Article (original research)';
            $this->_xdisId = 179;
        } elseif ($docType == 'bk') {
            $this->_xdisTitle = 'Book';
            $this->_xdisSubtype = 'Research book (original research)';
            $this->_xdisId = 174;
        } elseif ($docType == 'cp') {
            $this->_xdisTitle = 'Conference Paper';
            $this->_xdisSubtype = 'Fully published paper';
            $this->_xdisId = 130;
        } elseif ($docType == 'ch') {
            $this->_xdisTitle = 'Book Chapter';
            $this->_xdisSubtype = 'Research book chapter (original research)';
            $this->_xdisId = 177;
        }

    }

}

