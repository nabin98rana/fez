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
    $this->abstract = $node->getElementsByTagName("abstract")->item(0)->nodeValue;
    $this->ut = $node->getElementsByTagName("ut")->item(0)->nodeValue;
    $this->issn = $node->getElementsByTagName("issn")->item(0)->nodeValue;
    $this->sourceTitle = $node->getElementsByTagName("source_title")->item(0)->nodeValue;
    $this->sourceAbbrev = $node->getElementsByTagName("source_abbrev")->item(0)->nodeValue;
    $this->itemTitle = $node->getElementsByTagName("item_title")->item(0)->nodeValue;
    $this->bibId = $node->getElementsByTagName("bib_id")->item(0)->nodeValue;
    
    $articleNo = $node->getElementsByTagName("article_no");
    foreach ($articleNo as $n) {
      $articleNos[] = $n->nodeValue;
    }
    if (is_array($articleNos) && count($articleNos) > 0) {
      $this->articleNos = $articleNos;
    }
    
    $bibPages = $node->getElementsByTagName("bib_pages")->item(0);
    if ($bibPages) {    
      $this->bibPages = $bibPages->nodeValue;
      $this->bibPageBegin = $bibPages->getAttribute('begin');
      $this->bibPageEnd = $bibPages->getAttribute('end');
      $this->bibPageCount = $bibPages->getAttribute('pages');
    }
    
    $this->setBibIssueYVM($node);
    
    $docType = $node->getElementsByTagName("doctype")->item(0);
    if ($docType) {
      $this->docType = $docType->nodeValue;
      $this->docTypeCode = $docType->getAttribute('code');
    }
    
    $primaryLang = $node->getElementsByTagName("primarylang")->item(0);
    if ($primaryLang) {
      $this->primaryLang = $primaryLang->nodeValue;
      $this->primaryLangCode = $primaryLang->getAttribute('code');
    }
    
    $authors[] = $node->getElementsByTagName("primaryauthor")->item(0)->nodeValue;
    $author = $node->getElementsByTagName("author");
    foreach ($author as $a) {
      $authors[] = $a->nodeValue;
    }
    if (is_array($authors) && count($authors) > 0) {
      $this->authors = $authors;
    }
    
    $keyword = $node->getElementsByTagName("keyword");
    foreach ($keyword as $k) {
      $keywords[] = $k->nodeValue;
    }
    if (is_array($keywords) && count($keywords) > 0) {
      $this->keywords = $keywords;
    }
    
    $firstConf = $node->getElementsByTagName("conference")->item(0);
    if ($firstConf) {
      $this->confDate = $firstConf->getElementsByTagName("conf_date")->item(0)->nodeValue;
      $this->confTitle = $firstConf->getElementsByTagName("conf_title")->item(0)->nodeValue;
      $this->confLocCity = $firstConf->getElementsByTagName("conf_city")->item(0)->nodeValue;
      $this->confLocState = $firstConf->getElementsByTagName("conf_state")->item(0)->nodeValue;
    }
    
    $this->_loaded = TRUE;
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
    $item = $node->getElementsByTagName("item")->item(0);
    if ($item) {
      $coverDate = $item->getAttribute('coverdate');
      preg_match('/^(\d{4})(\d{2})/', $coverDate, $matches);
      if (count($matches) == 3) {
        if ($matches[2] != '00') {
          $this->bibIssueMnth = $matches[2];
        }
      }
      
      $bibIssue = $node->getElementsByTagName("bib_issue")->item(0);
      if ($bibIssue) {
        $this->bibIssueYear = $bibIssue->getAttribute('year');
        $this->bibIssueVol = $bibIssue->getAttribute('vol');
      }
    }    
  }
  
  /**
   * Stores to a new record in Fez
   */
  public function save()
  {
    $log = FezLog::get();
    
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
      $log->err('Unsupported doc type: '.$this->docTypeCode);
      return FALSE;
    }    
    $xdis_title = $dTMap[$this->docTypeCode][0];
    $xdis_subtype = $dTMap[$this->docTypeCode][1];
    $xdis_id = $dTMap[$this->docTypeCode][2];
    $collection = RID_DL_COLLECTION;
    $history = 'Imported from WoK Web Services Premium';
    // MODS
    $mods = array();
    $mods['titleInfo']['title'] = $this->itemTitle;
    if (count($this->authors) > 0) {
      $mods['name'][0]['id'] = '0';
      $mods['name'][0]['authority'] = APP_ORG_NAME;
      $mods['name'][0]['namePart_personal'] = $this->authors[0];
      $mods['name'][0]['role']['roleTerm_text'] = 'author';
      if (count($this->authors) > 1) {
        for ($i=1; $i<count($this->authors); $i++) {
          $mods['name'][$i]['id'] = '0';
          $mods['name'][$i]['authority'] = APP_ORG_NAME;
          $mods['name'][$i]['namePart_personal'] = $this->authors[$i];
          $mods['name'][$i]['role']['roleTerm_text'] = 'author';
        }
      }
    }
    if (count($this->keywords) > 0) {        
      for ($i=0; $i<count($this->keywords); $i++) {
        $mods['subject'][$i]['authority'] = 'keyword';
        $mods['subject'][$i]['topic'] = $this->keywords[$i];
      }
    }
    $mods['identifier_isi_loc'] = $this->ut;
    $mods['identifier_isbn'] = $this->isbn;
    $mods['language'] = Language::resolveWoSLanguage($this->primaryLang);
    $mods['genre'] = $xdis_title;
    $mods['genre_type'] = $xdis_subtype;
    $mods['relatedItem']['part']['detail_issue']['number'] = $this->bibIssueNum;
    $mods['relatedItem']['part']['detail_volume']['number'] = $this->bibIssueVol;
    $mods['relatedItem']['part']['extent_page']['start'] = $this->bibPageBegin;
    $mods['relatedItem']['part']['extent_page']['end'] = $this->bibPageEnd;
    $mods['relatedItem']['part']['extent_page']['total'] = $this->bibPageCount;        
    if ($xdis_title == 'Conference Paper') {
      $mods['originInfo']['dateIssued'] = $date_issued;     
      $mods['relatedItem']['titleInfo']['title'] = $this->sourceTitle;
      $mods['relatedItem']['name'][0]['namePart_type'] = 'conference';
      $mods['relatedItem']['name'][0]['namePart'] = $this->confTitle;
      $mods['relatedItem']['originInfo']['place']['placeTerm'] =  $this->confLocCity . ' ' . $this->confLocState;
      $mods['relatedItem']['originInfo']['dateOther'] = $this->confDate;  
    } else if ($xdis_title == 'Journal Article') {
      $mods['relatedItem']['originInfo']['dateIssued'] = $date_issued;
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
    // TODO: ingest object and return PID of created object

    $rec = new Record();
    $pid = $rec->insertFromArray($mods, $collection, "1.0", $history, 0, $links, array());
    return $pid;
  }
  
  /**
   * Update an existing record with additional bib data from WoK
   */
  public function update()
  {
    $log = FezLog::get();
    // TODO: update an existing record
    return TRUE;
  }
}