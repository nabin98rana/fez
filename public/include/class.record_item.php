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
        $str1 = str_replace(array('\t', '\r\n', '\n', ' '), ''
                                            , strtolower($this->title));
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
            $log->err('Record must be loaded before saving');
            return FALSE;
        }

        // List of doc types we support saving
        $dTMap = Thomson_Doctype_Mappings::getList('ESTI');
        foreach ($dTMap as $map) {
            $dTMap[$map['tdm_doctype']] = array($map['xdis_title'], $map['tdm_subtype'], $map['tdm_xdis_id']);
        }
        if (!array_key_exists($this->docTypeCode, $dTMap)) {
            $log->err('Unsupported doc type: ' . $this->docType.' '.$this->docTypeCode);
            return FALSE;
        }
        $xdis_id = $dTMap[$this->docTypeCode][2];

        // Instantiate Record Sek class
        $recordSearchKey = new Fez_Record_Searchkey();

        if (empty($history)){
            // History message
            $history = 'Imported from WoK Web Services Premium';
            if (count($this->author_ids) > 0) {
                $aut_details = Author::getDetails($this->author_ids[0]);
                $history .= " via Researcher ID download of " . $aut_details['aut_display_name'] . " (" .
                    $aut_details['aut_researcher_id'] . " - " . $aut_details['aut_id'] . " - " . $aut_details['aut_org_username'] . ")";
            }
        }

        // Citation Data
        $citationData = array('thomson' => $this->timesCited);

        // Search key Data
        $sekData = $this->_getSekData($dTMap, $recordSearchKey);
        $sekData = $recordSearchKey->buildSearchKeyDataByDisplayType($sekData, $xdis_id);

        // Save Record
        $result = $recordSearchKey->insertRecord($sekData, $history, $citationData);

        if (!$result){
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
                $mods['relatedItem']['originInfo']['dateIssued'] = $this->date_issued;
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
}