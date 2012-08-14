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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.language.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.thomson_doctype_mappings.php");

/**
 * Class for working with the ISI WoS REC item object
 *
 * @version 0.1
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 *
 */

class WosRecItem
{
  /**
   * Collections
   *
   * @var array
   */
  private $collections;

  /**
   * Abstract
   * 
   * @var string
   */
  private $abstract;
  
  /**
   * The ISI UT identifier
   *
   * @var string
   */
  private $ut = null;

  /**
  * The ISI Times Cited value
  *
  * @var int
  */
  private $timesCited = null;

  /**
   * The full name of the journal
   *
   * @var string
   */
  private $sourceTitle = null;
  
  /**
   *  The 20-char abbreviated name of the journal
   *
   * @var string
   */
  private $sourceAbbrev = null;
  
  /**
   * Title of the bibliographic item
   *
   * @var string
   */
  private $itemTitle = null;

  /**
   * Date published of the bibliographic item
   *
   * @var string
   */
  private $date_issued = null;

  /**
   * DOIs etc..
   *
   * @var array
   */
  private $articleNos = array();
  
  /**
   * The begin, end and number of pages as a precomposed string
   *
   * @var string
   */
  private $bibPages = null;
  
  /**
   * Begin page
   *
   * @var int
   */
  private $bibPageBegin = null;
  
  /**
   * End page
   *
   * @var int
   */
  private $bibPageEnd = null;
  
  /**
   * Page count
   *
   * @var int
   */
  private $bibPageCount = null;
  
  /**
   *  Precomposed volume, issue, special, pages and year data
   *
   * @var string
   */
  private $bibId = null;
  
  /**
   * Issue year
   *
   * @var int
   */
  private $bibIssueYear = null;
  
  /**
   * Issue month
   *
   * @var int
   */
  private $bibIssueMnth = null;

    /**
   * Issue number
   *
   * @var int
   */
  private $bibIssueNum = null;

  /**
   * Issue volume
   *
   * @var int
   */
  private $bibIssueVol = null;
  
  /**
   * The document type
   *
   * @var string
   */
  private $docType = null;
  
  /**
   *  The document type code
   *
   * @var string
   */
  private $docTypeCode = null;
  
  /**
   * Primary language
   *
   * @var string
   */
  private $primaryLang = null;
  
  /**
   * Primary language code
   *
   * @var string
   */
  private $primaryLangCode = null;
  
  /**
   * UI value
   *
   * @var string
   */
  private $issn = null;
  
  /**
   * ISBN value
   *
   * @var string
   */
  private $isbn = null;
  
  /**
   * First conference date
   *
   * @var string
   */
  private $confDate = null;
  
  /**
   * First conference title
   *
   * @var string
   */
  private $confTitle = null;
  
  /**
   * First conference city location
   *
   * @var string
   */
  private $confLocCity = null;
  
  /**
   * First conference state location
   *
   * @var string
   */
  private $confLocState = null;
  
  /**
   * Authors (both primary as secondary) Array index is important.
   *
   * @var array
   */
  private $authors = array();
    /**
   * Author IDs (both primary as secondary) Array index is important.
   *
   * @var array
   */
  private $author_ids = array();
  
  /**
   * Keywords
   *
   * @var array
   */
  private $keywords = array();
  
  /**
   * If a WoS record has been loaded 
   *
   * @var bool
   */
  private $_loaded = FALSE;
  
  /**
   * Publisher
   *
   * @var string
   */
  private $publisher = null;
    
  /**
   * Constructs a new object 
   */
  public function __construct($record = false)
  {  
    $this->_loaded = FALSE;
    if ($record) {
      $this->load($record);
    }
  }
  
  /**
   * Overloaded get method
   *
   * @param string $name
   * 
   * @return mixed
   */
  public function __get($name)
  {    
    $method = 'get'.ucfirst($name);
    if (method_exists(__CLASS__, $method)) {   
      return $this->$method();      
    } else {
      return $this->$name; 
    }    
  }
  
  /**
   * Overloaded set method
   *
   * @param string $name
   * @param mixed $value 
   */
  public function __set($name, $value)
  {
    $method = 'set'.ucfirst($name);
    if (method_exists(__CLASS__, $method)) {
      $this->$method($value);
    } else {
      $this->$name = $value;
    }
  }

  /**
   * Loads an object from a REC node
   *
   * @param DomNode $node
   */
  public function load($node)
  {
    $siloTc = $node->getElementsByTagName("silo_tc")->item(0);
    $this->timesCited = $siloTc->getAttribute('local_count');
    $this->abstract = $node->getElementsByTagName("abstract_text")->item(0)->nodeValue;
    $this->ut = str_ireplace("WOS:", "", $node->getElementsByTagName("UID")->item(0)->nodeValue );
    $elements = $node->getElementsByTagName("identifier");
    foreach($elements as $element) {
        if ($element->getAttribute('type') == "issn") {
         $this->issn = $element->getAttribute('value');
        }
        if ($element->getAttribute('type') == "isbn") {
            $this->isbn = $element->getAttribute('value');
        }
        if ($element->getAttribute('type') == "doi" || $element->getAttribute('type') == "xref_doi") {
         //Sometimes we have xref_doi repeated after a doi
         if (!in_array($element->getAttribute('value'), $articleNos))
         $articleNos[] = $element->getAttribute('value');
        }
    }
    if (is_array($articleNos) && count($articleNos) > 0) {
      $this->articleNos = $articleNos;
    }

    $elements = $node->getElementsByTagName("title");
    foreach($elements as $element) {
      if ($element->getAttribute('type') == "source")  {
          $this->sourceTitle = $element->nodeValue;
      }
      if ($element->getAttribute('type') == "item") {
          $this->itemTitle = $element->nodeValue;
      }
      if ($element->getAttribute('type') == "source_abbrev") {
          $this->sourceAbbrev = $element->nodeValue;
      }
    }

    $this->bibId = $node->getElementsByTagName("bib_id")->item(0)->nodeValue;

    if ($this->itemTitle == strtoupper($this->itemTitle)) {
        $this->itemTitle = Misc::smart_ucwords($this->itemTitle);
    }

    if ($this->sourceTitle == strtoupper($this->sourceTitle)) {
        $this->sourceTitle = Misc::smart_ucwords($this->sourceTitle);
    }


    
    $bibPages = $node->getElementsByTagName("page")->item(0);
    if ($bibPages) {    
      $this->bibPages = $bibPages->nodeValue;
      $this->bibPageBegin = $bibPages->getAttribute('begin');
      $this->bibPageEnd = $bibPages->getAttribute('end');
      $this->bibPageCount = $bibPages->getAttribute('page_count');
    }
    
    $this->setBibIssueYVM($node);
    $this->setDateIssued($node);

    $this->docType = $node->getElementsByTagName("doctype")->item(0)->nodeValue;
    $this->docTypeCode = Wok::getDoctype($this->docType );

    $elements = $node->getElementsByTagName("language");
    foreach($elements as $element) {
      if ($element->getAttribute('type') == "primary") {
          $this->primaryLang = $element->nodeValue;
      }
    }


    $elements = $node->getElementsByTagName("summary")->item(0);
    $elements = $elements->getElementsByTagName("name");
    foreach($elements as $element) {
        if ($element->getAttribute('role') == "author") {
            $authorTemp = $element->getElementsByTagName("display_name")->item(0)->nodeValue;
            if ($authorTemp == strtoupper($authorTemp)) {
                $authorTemp = Misc::smart_ucwords($authorTemp, 2);
            }
            $authors[] = $authorTemp;
        }
    }
    if (is_array($authors) && count($authors) > 0) {
      $this->authors = $authors;
    }

      $elements = $node->getElementsByTagName("keyword");
      foreach($elements as $element) {
          $keywordTemp = $element->nodeValue;
          if ($keywordTemp == strtoupper($keywordTemp)) {
              $keywordTemp = Misc::smart_ucwords($keywordTemp);
          }
          $keywords[] = $keywordTemp;
      }
    if (is_array($keywords) && count($keywords) > 0) {
      $this->keywords = $keywords;
    }

    $elements = $node->getElementsByTagName("publisher")->item(0);
    $this->publisher = $elements->getElementsByTagName("display_name")->item(0)->nodeValue;

    $firstConf = $node->getElementsByTagName("conference")->item(0);
    if ($firstConf) {
      $this->confDate = $firstConf->getElementsByTagName("conf_date")->item(0)->nodeValue;
      $this->confTitle = $firstConf->getElementsByTagName("conf_title")->item(0)->nodeValue;
      $this->confLocCity = $firstConf->getElementsByTagName("conf_city")->item(0)->nodeValue;
      $this->confLocState = $firstConf->getElementsByTagName("conf_state")->item(0)->nodeValue;
      if ($this->confDate == strtoupper($this->confDate)) {
          $this->confDate = Misc::smart_ucwords($this->confDate);
      }
      if ($this->confTitle == strtoupper($this->confTitle)) {
          $this->confTitle = Misc::smart_ucwords($this->confTitle);
      }
      if ($this->confLocCity == strtoupper($this->confLocCity)) {
          $this->confLocCity = Misc::smart_ucwords($this->confLocCity);
      }
      if ($this->confLocState == strtoupper($this->confLocState)) {
          $this->confLocState = Misc::smart_ucwords($this->confLocState);
      }
  }
    
    $this->_loaded = TRUE;
  }


    /**
     * Convenience function for retrieving any data to be displayed on enter form
     * if one exists
     *
     *  @return string
     */
    public function returnDataEnterForm()
    {
        $matching->title = $this->itemTitle;
        $matching->authors = $this->authors;
        $matching->sourceTitle = $this->sourceTitle;
        $matching->volume_number = $this->bibIssueVol;
        $matching->issue_number = $this->bibIssueNum;
        $matching->page_start = $this->bibPageBegin;
        $matching->page_end = $this->bibPageEnd;
        $matching->dateIssued = $this->date_issued;
        $matching->isi_loc = $this->ut;
        // Check if exists
        $pid = Record::getPIDByIsiLoc($this->ut);
        if($pid) {
            $matching->record_exists = 1;
            $matching->pid = $pid;
        }
        $matching->isi_loc = $this->ut;
        return $matching;

    }


    /**
   * Convenience function for retrieving a DOI from the array of articleNos
   * if one exists
   *
   *  @return string
   */
  public function getDoi()
  {
    foreach ($this->articleNos as $a) {
      if (preg_match('/^DOI(.*)/', $a, $matches)) {
        return trim($matches[1]);
      }      
    }
    return FALSE;
  }



  /**
   * Bib issue number is buried in the bib_id precomposed string
   * 
   * @return mixed
   */
  public function getBibIssueNum()
  {
    preg_match('/\(([^\)]+)\):/', $this->bibId, $matches);     
    if (count($matches) == 2) {
      return $matches[1];
    } else {
      return null;
    }
  }
  
  /**
   * Sets the issue year and volume and the month if exists
   * 
   * @param DomNode $node
   * @return null
   */
  public function setBibIssueYVM($node)
  {
    $pubInfo = $node->getElementsByTagName("pub_info")->item(0);
    $coverDate = $pubInfo->getAttribute('sortdate');
    if ($coverDate) {
        preg_match('/^(\d{4}).(\d{2})/', $coverDate, $matches);
        if (count($matches) == 3) {
            if ($matches[2] != '00') {
              $this->bibIssueMnth = $matches[2];
            }
        }
    }

    $this->bibIssueYear = $pubInfo->getAttribute('pubyear');
    $this->bibIssueVol = $pubInfo->getAttribute('vol');

      $bibVol = $node->getElementsByTagName("pub_info")->item(0);
      if ($bibVol) {
        $this->bibIssueNum = $bibVol->getAttribute('issue');
      }

  }

  /**
   * Sets the issue year and volume and the month if exists
   *
   * @param DomNode $node
   * @return null
   */
  public function setDateIssued($node) {
    $this->date_issued = '';
      $pubInfo = $node->getElementsByTagName("pub_info")->item(0);
      $coverDate = $pubInfo->getAttribute('sortdate');
      preg_match('/^(\d{4}).(\d{2})/', $coverDate, $matches);
      if (count($matches) == 3) {
        if ($matches[2] == '00') {
          $this->date_issued = $matches[1];
        } else {
          $this->date_issued = $matches[1] . '-' . $matches[2];
        }
      }
    }

    /**
     * Returns an array of Search key's title & value pair, built from WOS record items.
     * 
     * @param array $dTMap
     * @param Fez_Record_Searchkey $recordSearchKey
     * @return array  
     */
    protected function _getSekData($dTMap, $recordSearchKey)
    {
        $xdis_title = $dTMap[$this->docTypeCode][0];
        $xdis_subtype = $dTMap[$this->docTypeCode][1];
        $xdis_id = $dTMap[$this->docTypeCode][2];
        
        // Build Search key data 
        $sekData = array();

        $sekData['Display Type']    = $xdis_id;
        $sekData['Genre']           = $xdis_title;
        $sekData['Genre Type']      = $xdis_subtype;
        
        $sekData['Title']           = $this->itemTitle;
        $sekData['Author']          = $this->authors;
        $sekData['ISI LOC']         = $this->ut;
        $sekData['Keywords']        = $this->keywords;
        $sekData['ISBN']            = $this->isbn;
        $sekData['ISSN']            = $this->issn;
        $sekData['DOI']            = $this->articleNos[0];
        $sekData['Publisher']       = $this->publisher;

        /// exception for conf papers that the subtype goes into genre type
        if ($xdis_title == "Conference Paper") {
            $sekData["Genre Type"] = $xdis_subtype;
        } else {
            $sekData["Subtype"] = $xdis_subtype;
        }

        //Commented out due to copyright reasons
        //$sekData['Description']     = $this->abstract;
        
        $sekData['Issue Number']    = $this->bibIssueNum;
        $sekData['Volume Number']   = $this->bibIssueVol;
        $sekData['Start Page']      = $this->bibPageBegin;
        $sekData['End Page']        = $this->bibPageEnd;
        $sekData['Total Pages']     = $this->bibPageCount;

        $sekData['Date']            = Misc::MySQLDate(array("Year" => date("Y", strtotime($this->date_issued)), "Month" => date("m", strtotime($this->date_issued))));
        
        $sekData['Language']        = Language::resolveWoSLanguage($this->primaryLang);
        $sekData['Status']          = Status::getID("Published");
        $sekData['Object Type']     = Object_Type::getID("Record");
        $sekData['Depositor']       = Auth::getUserID();
        $sekData['isMemberOf']      = $this->collections[0];
        $sekData['Created Date']    = $recordSearchKey->getVersion();
        $sekData['Updated Date']    = $recordSearchKey->getVersion();

        // Custom search keys based on Document Type
        if ($xdis_title == 'Conference Paper') {
            $sekData['Proceedings Title'] = $this->sourceTitle;
            $sekData['Conference Name']   = $this->confTitle;
            $sekData['Conference Dates']  = $this->confDate;
            $sekData['Conference Location']  = $this->confLocCity . ' ' . $this->confLocState;
        } else if ($xdis_title == 'Journal Article') {
            $sekData['Journal Name'] = $this->sourceTitle;
        }
        
        // DOI link
        $doi = $this->getDoi();
        $sekData['Link'] = 'http://dx.doi.org/' . $doi;
        
        return $sekData;
    }
  
    
    /**
     * Saves WOS record items to Record Search Key 
     * 
     * @return string $pid
     */
    protected function _saveFedoraBypass($history = null)
    {
        $log = FezLog::get();

        if (!$this->_loaded) {
            $log->err('WoS record must be loaded before saving');
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
                $log->err('WoS record must be loaded before saving');
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
            
            
            $xdis_title = $dTMap[$this->docTypeCode][0];
            $xdis_subtype = $dTMap[$this->docTypeCode][1];
            $xdis_id = $dTMap[$this->docTypeCode][2];

            if (empty($history)){
                $history = 'Imported from WoK Web Services Premium';

                if (count($this->author_ids) > 0) {
                    $aut_details = Author::getDetails($this->author_ids[0]);
                    $history .= " via Researcher ID download of " . $aut_details['aut_display_name'] . " (" .
                            $aut_details['aut_researcher_id'] . " - " . $aut_details['aut_id'] . " - " . $aut_details['aut_org_username'] . ")";
                }
            }
            // MODS

            $mods = array();
            $mods['titleInfo']['title'] = $this->itemTitle;
            if (count($this->authors) > 0) {
                $mods['name'][0]['id'] = '0';
                $mods['name'][0]['authority'] = APP_ORG_NAME;
                $mods['name'][0]['namePart_personal'] = $this->authors[0];
                $mods['name'][0]['role']['roleTerm_text'] = 'author';
                if (count($this->authors) > 1) {
                    for ($i = 1; $i < count($this->authors); $i++) {
                        $mods['name'][$i]['id'] = '0';
                        $mods['name'][$i]['authority'] = APP_ORG_NAME;
                        $mods['name'][$i]['namePart_personal'] = $this->authors[$i];
                        $mods['name'][$i]['role']['roleTerm_text'] = 'author';
                    }
                }
            }
            if (count($this->keywords) > 0) {
                for ($i = 0; $i < count($this->keywords); $i++) {
                    $mods['subject'][$i]['authority'] = 'keyword';
                    $mods['subject'][$i]['topic'] = $this->keywords[$i];
                }
            }
            $mods['identifier_isi_loc'] = $this->ut;
            $mods['identifier_isbn'] = $this->isbn;
            $mods['identifier_issn'] = $this->issn;
            $mods['identifier_doi'] = $this->articleNos[0];
            $mods['language'] = Language::resolveWoSLanguage($this->primaryLang);
            $mods['genre'] = $xdis_title;
            $mods['genre_type'] = $xdis_subtype;
            $mods['relatedItem']['part']['detail_issue']['number'] = $this->bibIssueNum;
            $mods['relatedItem']['part']['detail_volume']['number'] = $this->bibIssueVol;
            $mods['relatedItem']['part']['extent_page']['start'] = $this->bibPageBegin;
            $mods['relatedItem']['part']['extent_page']['end'] = $this->bibPageEnd;
            $mods['relatedItem']['part']['extent_page']['total'] = $this->bibPageCount;
            if ($xdis_title == 'Conference Paper') {
                $mods['originInfo']['dateIssued'] = $this->date_issued;
                $mods['relatedItem']['titleInfo']['title'] = $this->sourceTitle;
                $mods['relatedItem']['name'][0]['namePart_type'] = 'conference';
                $mods['relatedItem']['name'][0]['namePart'] = $this->confTitle;
                $mods['relatedItem']['originInfo']['place']['placeTerm'] = $this->confLocCity . ' ' . $this->confLocState;
                $mods['relatedItem']['originInfo']['dateOther'] = $this->confDate;
            } else if ($xdis_title == 'Journal Article') {
                $mods['relatedItem']['originInfo']['dateIssued'] = $this->date_issued;
                $mods['relatedItem']['name'][0]['namePart_type'] = 'journal';
                $mods['relatedItem']['name'][0]['namePart'] = $this->sourceTitle;
            }
            // Links
            $links = array();
            $doi = $this->getDoi();
            if ($doi) {
                $links[0]['url'] = 'http://dx.doi.org/' . $doi;
                $links[0]['id'] = 'link_1';
                $links[0]['created'] = date('c');
                $links[0]['name'] = 'Link to Full Text (DOI)';
            }
            $rec = new Record();
            $pid = $rec->insertFromArray($mods, $this->collections[0], "MODS 1.0", $history, 0, $links, array());
            if (is_numeric($this->timesCited)) {
                Record::updateThomsonCitationCount($pid, $this->timesCited, $this->ut);
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


    if (! $this->_loaded) {
      $log->err('WoS record must be loaded before saving');
      return FALSE;
    }
    // List of doc types we support saving
    $dTMap = Thomson_Doctype_Mappings::getList('ESTI');
    foreach ($dTMap as $map) {
      $dTMap[$map['tdm_doctype']] = array($map['xdis_title'], $map['tdm_subtype'], $map['tdm_xdis_id']);
    }
    if (! array_key_exists($this->docTypeCode, $dTMap)) {
      $log->err('Unsupported doc type: ' . $this->docType.' '.$this->docTypeCode);
      return FALSE;
    }
    $xdis_title = $dTMap[$this->docTypeCode][0];
    $xdis_subtype = $dTMap[$this->docTypeCode][1];
    $xdis_id = $dTMap[$this->docTypeCode][2];

    $searchKeyTargets = array(
      "Date" => $this->date_issued,
      "ISSN" => $this->issn,
      "ISBN" => $this->isbn,
      "DOI" => $this->articleNos[0],
      "Volume Number" => $this->bibIssueVol,
      "Start Page" => $this->bibPageBegin,
      "End Page" => $this->bibPageEnd,
      "Total Pages" => $this->bibPageCount,
      "Issue Number" => $this->bibIssueNum,
      "Language" => Language::resolveWoSLanguage($this->primaryLang),
      "Conference Dates" => $this->confDate,
      "Conference Location" => $this->confLocCity . ' ' . $this->confLocState,
      "Conference Name" => $this->confTitle,
      "Journal Name" => $this->sourceTitle,
      "WoK Doc Type" => $this->docTypeCode
    );
  /// exception for conf papers that the subtype goes into genre type
  if ($xdis_title == "Conference Paper") {
   $searchKeyTargets["Genre Type"] = $xdis_subtype;
  } else {
   $searchKeyTargets["Subtype"] = $xdis_subtype;
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

    $history = 'Filled empty metadata fields ('.implode(", ",$search_keys).') using WoK Web Services Premium';
    $record = new RecordGeneral($pid);
    $record->addSearchKeyValueList(
        $search_keys, $values, true, $history
    );

    // If this update came from a RID download, put this in the RID collection as long as it ONLY exists in the WoS collection right now
    if ($this->collections[0] == RID_DL_COLLECTION) {
        $isMemberOf = Record::getSearchKeyIndexValue($pid, "isMemberOf", false);
        if (!in_array(RID_DL_COLLECTION, $isMemberOf)) { //if it doesn't currently live in the RID collection, add it as a parent
            $res = $record->updateRELSEXT("rel:isMemberOf", RID_DL_COLLECTION, false);
            if($res >= 1) {
                $log->debug("Copied '".$pid."' into RID Download Collection ".RID_DL_COLLECTION);
            } else {
                $log->err("Copy of '".$pid."' into RID Download Collection ".RID_DL_COLLECTION." Failed");
            }
             $wos_collection = trim(APP_WOS_COLLECTIONS, "'");
            // If this record is in the WOS collection, remove it from it now that is in the RID collection
            if (in_array($wos_collection, $isMemberOf)) {
                $res = $record->removeFromCollection($wos_collection);
                if( $res ) {
                    $log->debug("Removed record '".$pid."' from collection '".$wos_collection."'");
                } else {
                    $log->err("ERROR Removing '".$pid."' from collection '".$wos_collection."'");
                }

            }
        }
    }

    if (is_numeric($this->timesCited)) {
       Record::updateThomsonCitationCount($pid, $this->timesCited, $this->ut);
    }
    return TRUE;
  }
}