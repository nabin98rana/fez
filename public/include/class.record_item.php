<?php

/**
 * Base class inherited by all classses 
 * representing data imported from external 
 * sources to be processed.
 * @author Chris Maj <c.maj@library.uq.edu.au>
 * @since November 2012
 *
 */
abstract class RecordItem
{
    protected $_importAPI;
    protected $_collections;
    protected $_abstract;
    protected $_ut = null;
    protected $_pubmedId = null;
    protected $_scopusId = null;
    protected $_wokCitationCount = null;
    protected $_scopusCitationCount = null;
    protected $_sourceAbbrev = null;
    protected $_title = null;
    protected $_journalTitle = null;
    protected $_journalTitleAbbreviation = null;
    protected $_totalPages = null;
    protected $_startPage = null;
    protected $_endPage = null;
    protected $_issueDate = null;
    protected $_issueNumber = null;
    protected $_issueVolume = null;
    protected $_wokDocTypeCode = null;
    //protected $_docTypeCode = null;
    protected $_langageCode = null;
    protected $_issn = null;
    protected $_isbn = null;
    protected $_conferenceDate = null;
    protected $_conferenceTitle = null;
    protected $_confenceLocationCity = null;
    protected $_confenceLocationState = null;
    protected $_authors = array();
    protected $_author_ids = array();
    protected $_keywords = array();
    protected $_loaded = FALSE;
    protected $_publisher = null;
    protected $_doi = null;
    protected $_xdisId = null;
    protected $_xdisTitle = null;
    protected $_xdisSubtype = null;

    
    //more fields common to all child classes
    //with cooresponding liken*() methods
    
    public abstract function load($recordData);
    
    /**
     * Common xpath object used by all child classes
     * @param string $rawXML
     * @return DOMXPath
     */
    protected function getXPath($rawXML)
    {
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadXML($rawXML);
        
        $xpath = new DOMXPath($xmlDoc);
        /*$xpath->registerNamespace('prism');
        $xpath->registerNamespace('dc');*/
        
        return $xpath;
    }
    
    /**
     * Strip title and perform comparison
     */
    public function likenTitle()
    {
        //title comparison logic
        $str1 = str_replace(array('\t', '\r\n', '\n', ' '), '', strtolower($this->title));
        return $str1;
    }
    
    /**
     * Compare DOI
     */
    public function likenDoi()
    {
        //doi comparison logic
    }


    /**
     * Saves record items to Record Search Key
     *
     * @return string $pid
     */
    protected function _saveFedoraBypass($history = null)
    {
        $log = FezLog::get();

        if (!$this->_loaded) {
            $log->err($this->_importAPI.' record must be loaded before saving');
            return FALSE;
        }

        // Instantiate Record Sek class
        $recordSearchKey = new Fez_Record_Searchkey();

        if (empty($history)){
            // History message
            $history = 'Imported from '.$this->_importAPI;
        }

        // Citation Data
        if ($this->_wokCitationCount) {
            $citationData['thomson'] = $this->_wokCitationCount;
        }
        if (($this->_scopusCitationCount)) {
            $citationData['scopus'] = $this->_scopusCitationCount;
        }


        // Search key Data
        $sekData = $this->_getSekData($recordSearchKey);
        $sekData = $recordSearchKey->buildSearchKeyDataByDisplayType($sekData, $this->_xdisId);

        // Save Record
        $result = $recordSearchKey->insertRecord($sekData, $history, $citationData);

        if (!$result) {
            return false;
        }

        //assume solr need updating for new lister permissions
        if (APP_SOLR_INDEXER == "ON") {
            FulltextQueue::singleton()->add($recordSearchKey->getPid());
            FulltextQueue::singleton()->commit();
        }

        return $recordSearchKey->getPid();
    }


    /**
     * Stores to a new record in Fez
     */
    public function save($history = null)
    {
        $pid = null;

        if (APP_FEDORA_BYPASS == 'ON') {

            // save WOS data to Record Search Keys
            $pid = $this->_saveFedoraBypass($history);

        } else {


            $log = FezLog::get();

            if (!$this->_loaded) {
                $log->err($this->_importAPI.' record must be loaded before saving');
                return FALSE;
            }

            $mods = array();
            $mods['titleInfo']['title'] = $this->_title;
            if (count($this->_authors) > 0) {
                $mods['name'][0]['id'] = '0';
                $mods['name'][0]['authority'] = APP_ORG_NAME;
                $mods['name'][0]['namePart_personal'] = $this->_authors[0];
                $mods['name'][0]['role']['roleTerm_text'] = 'author';
                if (count($this->_authors) > 1) {
                    for ($i = 1; $i < count($this->_authors); $i++) {
                        $mods['name'][$i]['id'] = '0';
                        $mods['name'][$i]['authority'] = APP_ORG_NAME;
                        $mods['name'][$i]['namePart_personal'] = $this->_authors[$i];
                        $mods['name'][$i]['role']['roleTerm_text'] = 'author';
                    }
                }
            }
            if (count($this->_keywords) > 0) {
                for ($i = 0; $i < count($this->_keywords); $i++) {
                    $mods['subject'][$i]['authority'] = 'keyword';
                    $mods['subject'][$i]['topic'] = $this->_keywords[$i];
                }
            }
            $mods['identifier_isi_loc'] = $this->_ut;
            $mods['identifier_isbn'] = $this->_isbn;
            $mods['identifier_issn'] = $this->_issn;
            $mods['identifier_doi'] = $this->_doi;
            $mods['language'] = $this->_language;
            $mods['genre'] = $this->_xdisTitle;
            $mods['genre_type'] = $this->_xdisSubtype;
            $mods['relatedItem']['part']['detail_issue']['number'] = $this->_issueNumber;
            $mods['relatedItem']['part']['detail_volume']['number'] = $this->_issueVolume;
            $mods['relatedItem']['part']['extent_page']['start'] = $this->_startPage;
            $mods['relatedItem']['part']['extent_page']['end'] = $this->_endPage;
            $mods['relatedItem']['part']['extent_page']['total'] = $this->_totalPages;
            if (_xdisTitle == 'Conference Paper') {
                $mods['originInfo']['dateIssued'] = $this->_issueDate;
                $mods['relatedItem']['titleInfo']['title'] = $this->_title;
                $mods['relatedItem']['name'][0]['namePart_type'] = 'conference';
                $mods['relatedItem']['name'][0]['namePart'] = $this->_conferenceTitle;
                if (!empty($this->_confenceLocationCity) || !empty($this->_confenceLocationState)) {
                    $mods['relatedItem']['originInfo']['place']['placeTerm'] = $this->_confenceLocationCity . ' ' . $this->_confenceLocationState;
                }
                $mods['relatedItem']['originInfo']['dateOther'] = $this->_conferenceDate;
            } else if (_xdisTitle == 'Journal Article') {
                $mods['relatedItem']['originInfo']['dateIssued'] = $this->_issueDate;
                $mods['relatedItem']['name'][0]['namePart_type'] = 'journal';
                $mods['relatedItem']['name'][0]['namePart'] = $this->_journalTitle;
            }
            // Links currently blank since only getting first DOI or link
            $links = array();
            $rec = new Record();
            $pid = $rec->insertFromArray($mods, $this->collections[0], "MODS 1.0", $history, 0, $links, array());
            if (is_numeric($this->_wokCitationCount)) {
                Record::updateThomsonCitationCount($pid, $this->_wokCitationCount, $this->ut);
            }
            if (is_numeric($this->_scopusCitationCount)) {
                Record::updateScopusCitationCount($pid, $this->_scopusCitationCount, $this->_scopusId);
            }
        }
        return $pid;
    }


    /**
     * Update an existing record with additional bib data from WoK
     */
    public function update($pid)
    {
        $log = FezLog::get();
        // TODO: update an existing record

        if (!$pid) {
            $log->err('Need pid to update');
            return FALSE;
        }

        if (! $this->_loaded) {
            $log->err($this->_importAPI.' record must be loaded before saving');
            return FALSE;
        }

        $searchKeyTargets = array(
            "Date" => $this->_issueDate,
            "ISSN" => $this->_issn,
            "ISBN" => $this->_isbn,
            "DOI" => $this->_doi,
            "Volume Number" => $this->_issueVolume,
            "Start Page" => $this->_startPage,
            "End Page" => $this->_endPage,
            "Total Pages" => $this->_totalPages,
            "Issue Number" => $this->_issueNumber,
            "Language" => $this->_langageCode,
            "Conference Dates" => $this->_conferenceDate,
            "Conference Name" => $this->_conferenceTitle,
            "Journal Name" => $this->_journalTitle,
            "WoK Doc Type" => $this->wokDocTypeCode,

        );

        if (!empty($this->_confenceLocationCity) || !empty($this->_confenceLocationState)) {
            $searchKeyTargets['Conference Location'] = $this->_confenceLocationCity . ' ' . $this->_confenceLocationState;
        }
        /// exception for conf papers that the subtype goes into genre type
        if (_xdisTitle == "Conference Paper") {
            $searchKeyTargets["Genre Type"] = $this->_xdisSubtype;
        } else {
            $searchKeyTargets["Subtype"] = $this->_xdisSubtype;
        }

        $search_keys = array();
        $values = array();

        foreach ($searchKeyTargets as $skey => $svalue) {
            if (!empty($svalue)) {
                $existingValue =  Record::getSearchKeyIndexValue($pid, $skey, false);
                if (empty($existingValue)) {
                    $search_keys[] = $skey;
                    $values[] = $svalue;
                }
            }
        }

        $history = 'Filled empty metadata fields ('.implode(", ", $search_keys).') using '. $this->_importAPI;
        $record = new RecordGeneral($pid);
        $record->addSearchKeyValueList(
            $search_keys, $values, true, $history
        );

        if (is_numeric($this->_wokCitationCount)) {
            Record::updateThomsonCitationCount($pid, $this->_wokCitationCount, $this->ut);
        }
        if (is_numeric($this->_scopusCitationCount)) {
            Record::updateScopusCitationCount($pid, $this->_scopusCitationCount, $this->_scopusId);
        }
        return TRUE;
    }

    /**
     * Returns an array of Search key's title & value pair, built from WOS record items.
     *
     * @param array $dTMap
     * @param Fez_Record_Searchkey $recordSearchKey
     * @return array
     */
    protected function _getSekData($recordSearchKey)
    {
        // Build Search key data
        $sekData = array();

        $sekData['Display Type']    = $this->_xdisId;
        $sekData['Genre']           = $this->_xdisTitle;
        $sekData['Genre Type']      = $this->_xdisSubtype;

        $sekData['Title']           = $this->_title;
        $sekData['Author']          = $this->_authors;
        $sekData['ISI LOC']         = $this->_ut;
        $sekData['Keywords']        = $this->_keywords;
        $sekData['ISBN']            = $this->_isbn;
        $sekData['ISSN']            = $this->_issn;
        $sekData['DOI']            = $this->_doi;
        $sekData['Publisher']       = $this->_publisher;

        /// exception for conf papers that the subtype goes into genre type
        if ($this->_xdisTitle == "Conference Paper") {
            $sekData["Genre Type"] = $this->_xdisSubtype;
        } else {
            $sekData["Subtype"] = $this->_xdisSubtype;
        }

        //Commented out due to copyright reasons
        //$sekData['Description']     = $this->abstract;

        $sekData['Issue Number']    = $this->_issueNumber;
        $sekData['Volume Number']   = $this->_issueVolume;
        $sekData['Start Page']      = $this->_startPage;
        $sekData['End Page']        = $this->_endPage;
        $sekData['Total Pages']     = $this->_totalPages;

        $sekData['Date']            = Misc::MySQLDate(array("Year" => date("Y", strtotime($this->_issueDate)), "Month" => date("m", strtotime($this->_issueDate))));

        $sekData['Language']        = $this->_langageCode;
        $sekData['Status']          = Status::getID("Published");
        $sekData['Object Type']     = Object_Type::getID("Record");
        $sekData['Depositor']       = Auth::getUserID();
        $sekData['isMemberOf']      = $this->collections[0];
        $sekData['Created Date']    = $recordSearchKey->getVersion();
        $sekData['Updated Date']    = $recordSearchKey->getVersion();

        // Custom search keys based on Document Type
        if ($xdis_title == 'Conference Paper') {
            $sekData['Proceedings Title'] = $this->_title;
            $sekData['Conference Name']   = $this->_conferenceTitle;
            $sekData['Conference Dates']  = $this->_conferenceDate;
            if (!empty($this->_confenceLocationCity) || !empty($this->_confenceLocationState)) {
                $sekData['Conference Location']  = $this->_confenceLocationCity . ' ' . $this->_confenceLocationState;
            }
        } else if ($xdis_title == 'Journal Article') {
            $sekData['Journal Name'] = $this->_journalTitle;
        }

        return $sekData;
    }

    public function returnPidWithSameDoi()
    {
        if (empty($this->_doi)) {
            return '';
        }
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key_doi
                WHERE rek_doi = ".$db->quote($this->_doi);
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }
        return $res;
    }

    public function findPidWithBestTitleMatch()
    {
        if (empty($this->_title)) {
            return '';
        }

        $dupes = DuplicatesReport::similarTitlesQuery('dummy', trim($this->_title));
        return $dupes[0]['pid'];

        /*
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT rek_pid, rek_title FROM " . APP_TABLE_PREFIX . "record_search_key";
        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }
        $bestPid = '';
        $bestPidMatchPercent=$minPercent;
        foreach ($res as $titles) {
            similar_text($titles['rek_title'], $this->_title, $percent);
            if ($percent >= $bestPidMatchPercent) {
                $bestPid = $titles['rek_pid'];
                $bestPidMatchPercent = $percent;
            }
        }
        return $bestPid;
        */
    }

    public function matchOnPageInfo($pid)
    {
        $matches = 0;

        $startPage = Record::getSearchKeyIndexValue($pid, "Start Page", false);
        $endPage = Record::getSearchKeyIndexValue($pid, "End Page", false);
        $totalPages = Record::getSearchKeyIndexValue($pid, "Total Pages", false);
        $issueVolume = Record::getSearchKeyIndexValue($pid, "Volume Number", false);
        $issueNumber = Record::getSearchKeyIndexValue($pid, "Issue Number", false);

        //We'll go through each variable and if both populated check if they match, if any fail we consider the match failed

        if (!empty($this->_startPage)) {
            if ($this->_startPage == $startPage) {
                $matches++;
            } else if (!empty($startPage)) {
                return false;
            }
        }
        if (!empty($this->_endPage)) {
            if ($this->_endPage == $endPage) {
                $matches++;
            } else if (!empty($endPage)) {
                return false;
            }
        }
        if (!empty($this->_endPage)) {
            if ($this->_endPage == $totalPages) {
                $matches++;
            } else if (!empty($totalPages)) {
                return false;
            }
        }
        if (!empty($this->_issueNumber)) {
            if ($this->_issueNumber == $issueNumber) {
                $matches++;
            } else if (!empty($issueNumber)) {
                return false;
            }
        }
        if (!empty($this->_issueVolume)) {
            if ($this->_issueVolume == $issueVolume) {
                $matches++;
            } else if (!empty($issueVolume)) {
                return false;
            }
        }

        return $matches;
    }
}