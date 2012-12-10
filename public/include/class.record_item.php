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
    /**
     * Fields pertaining to the record
     */
    protected $_importAPI;
    protected $_collections;
    protected $_abstract;
    protected $_ut = null;
    protected $_pubmedId = null;
    protected $_scopusId = null;
    protected $_embaseId = null;
    protected $_wokId = null;
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
    protected $_docSubType = null;
    //protected $_docTypeCode = null;
    protected $_languageCode = null;
    protected $_issn = null;
    protected $_isbn = null;
    protected $_conferenceDates = null;
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

    /**
     * Namespaces to use with the XPath object
     * @var array
     */
    protected $_namespaces = array();

    /**
     * We will try to do a comparison on all these
     * ids when doing de-duping if they are set
     * @var array
     */
    protected $_comparisonIdTypes = array();


    public abstract function load($recordData, $nameSpaces=null);

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

        if($this->_namespaces)
        {
            foreach($this->_namespaces as $name => $uri)
            {
                $xpath->registerNamespace($name, $uri);
            }

            $rootNameSpace = $xmlDoc->lookupNamespaceUri($xmlDoc->namespaceURI);
            $xpath->registerNamespace('default', $rootNameSpace);
        }

        return $xpath;
    }

    /**
     * Comparison of and de-duping downloaded record
     */
    public function liken()
    {
        //set an idcollection array for pids returned by id type
        $idCollection = array();
        $mirrorMirror = new ReflectionClass($this);

        //foreach of the selected id types
        //(wok,pubmed,scopus,etc) that is not null
        foreach($this->_comparisonIdTypes as $id)
        {
            //Check that a method exists for retrieving
            //a local record by that id type.
            $retrieverName = 'getPIDsBy'.$id;
            $retriever = $mirrorMirror->getMethod($retrieverName);

            if($retriever)
            {
                //Run the method and capture the pid(s)
                $pids = $this->$retrieverName();
                $pidCount = count($pids);

                //if there is only one pid returned
                if($pidCount == 1)
                {
                    //set that as the pid returned for that id in the array
                    $idCollection[$id] = $pids[0];
                }
                elseif($pidCount > 1)
                {
                    //log an error if there is more than one pid (but not if there are none)
                    $this->_log->err("Multiple matches found for $id:".__METHOD__);
                    echo "\nMultiple matches found for $id:".__METHOD__ ."\n";
                    return false;
                }
            }
        }

        //if all the pids in the idcollection array are the same
        if(count(array_unique($idCollection)) == 1)
        {
            //that's the pid for us - set it as authorative
            $collectionKey = array_keys($idCollection);
            $collectionKey = $collectionKey[0];
            $likenedPid = $idCollection[$collectionKey];
        }

        //if we have an authoritative pid
        if($likenedPid)
        {
            //do a fuzzy title match
            $rec = new Record();
            $title = $rec->getTitleFromIndex($likenedPid);

            $percentageMatch = 0;

            $downloadedTitle = RCL::normaliseTitle($this->_title);
            $localTitle = RCL::normaliseTitle($title);
            similar_text($downloadedTitle, $localTitle, $percentageMatch);
            //if the fuzzy title match is better than 80%
            if($percentageMatch >= 80)
            {
                //update the record with any data we don't have
                echo "\nUPDATING\n";
                $this->update($likenedPid);
            }
        }
        else
        {
            //save a new record
            echo  "\nSAVING\n";
            $this->save();
        }
    }

    /**
     * Fetch an array of pids by Doi
     * @param mixed $id
     * @return array
     */
    protected function getPIDsBy_doi()
    {
        $pids = array();

        if($this->_doi)
        {
            $pidSet = Record::getPIDsByDoi($this->_doi);
        }

        for($i=0;$i<count($pidSet);$i++)
        {
            $pids[] = $pidSet[$i]['rek_doi_pid'];
        }

        return $pids;
    }

    /**
    * Fetch an array of pids by ScopusId
    * @param mixed $id
    * @return array
    */
    protected function getPIDsBy_scopusId()
    {
        $pids = array();

        if($this->_scopusId)
        {
            $pidSet = Record::getPIDsByScopusID($this->_scopusId);
        }

        for($i=0;$i<count($pidSet);$i++)
        {
            $pids[] = $pidSet[$i]['rek_scopus_id'];
        }

        return $pids;
    }

    /**
    * Fetch an array of pids by PubmedId
    * @param mixed $id
    * @return array
    */
    protected function getPIDsBy_pubmedId()
    {
        //woteva

        //return array of pids
    }

    /**
    * Fetch an array of pids by WokId
    * @param mixed $id
    * @return array
    */
    protected function getPIDsBy_wokId()
    {
        //woteva

        //return array of pids
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

        if (empty($history)) {
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
            $mods['language'] = $this->_languageCode;
            $mods['genre'] = $this->_xdisTitle;
            $mods['genre_type'] = $this->_xdisSubtype;
            $mods['relatedItem']['part']['detail_issue']['number'] = $this->_issueNumber;
            $mods['relatedItem']['part']['detail_volume']['number'] = $this->_issueVolume;
            $mods['relatedItem']['part']['extent_page']['start'] = $this->_startPage;
            $mods['relatedItem']['part']['extent_page']['end'] = $this->_endPage;
            $mods['relatedItem']['part']['extent_page']['total'] = $this->_totalPages;
            if ($this->_xdisTitle == 'Conference Paper') {
                $mods['originInfo']['dateIssued'] = $this->_issueDate;
                $mods['relatedItem']['titleInfo']['title'] = $this->_title;
                $mods['relatedItem']['name'][0]['namePart_type'] = 'conference';
                $mods['relatedItem']['name'][0]['namePart'] = $this->_conferenceTitle;
                if (!empty($this->_confenceLocationCity) || !empty($this->_confenceLocationState)) {
                    $mods['relatedItem']['originInfo']['place']['placeTerm'] = $this->_confenceLocationCity . ' ' . $this->_confenceLocationState;
                }
                $mods['relatedItem']['originInfo']['dateOther'] = $this->_conferenceDates;
            } else if ($this->_xdisTitle == 'Journal Article') {
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
        var_dump($pid);
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
            "Language" => $this->_languageCode,
            "Conference Dates" => $this->_conferenceDates,
            "Conference Name" => $this->_conferenceTitle,
            "Journal Name" => $this->_journalTitle,
            "WoK Doc Type" => $this->_wokDocTypeCode,

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
        $record = new RecordObject($pid);
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

        $sekData['Language']        = $this->_languageCode;
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
            $sekData['Conference Dates']  = $this->_conferenceDates;
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
    public function comparePidTitle($pid) {
        $pidTitle =  Record::getSearchKeyIndexValue($pid, "Title", false);
        $stripedPidTitle = RCL::normaliseTitle($pidTitle);
        $stripB = RCL::normaliseTitle($rec->itemTitle);

        similar_text($stripedPidTitle, $this->_title, $percent);
        return $percent;
    }
}