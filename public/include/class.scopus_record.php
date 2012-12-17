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
include_once(APP_INC_PATH . "class.record_item.php");
include_once(APP_INC_PATH . "class.matching_conferences.php");

class ScopusRecItem extends RecordItem
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
     * Perform de-duping using these ids
     * which also have cooresponding methods
     * @var array
     */
    protected $_comparisonIdTypes = array('_scopusId', '_doi');
    
    public function __construct($recordData=null, $xmlNs=null)
    {
        $this->_log = FezLog::get();
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
        if($nameSpaces)
        {
            foreach($nameSpaces as $name => $uri)
            {
                $this->_namespaces[$name] = $uri;
            }
        }
        
        $this->_loaded = false;
        
        $xpath = $this->getXPath($recordData);
        
        $this->_doi = $this->extract('//prism:doi', $xpath);
        
        $this->_title = $this->extract('//dc:title', $xpath);
        
        //$this->_xdisTitle = 'Journal Article';
        
        $affiliations = $xpath->query('//default:affiliation/default:affilname');
        foreach($affiliations as $affiliation)
        {
            $this->_affiliations[] = $affiliation->nodeValue;
        }
        
        if($this->likenAffiliation())
        {
            
            $this->_issn = $this->extract('//prism:issn', $xpath);
            
            $this->_volume = $this->extract('//prism:volume', $xpath);
            
            $this->_docType = $this->extract('//prism:aggregationType', $xpath);
            
            $this->_journalTitle = $this->extract('//prism:publicationName', $xpath);
            
            $this->_docSubType = $this->extract('//default:subtype', $xpath);
            
//             $xdisTitle = $this->extract('//prism:aggregationType', $xpath);
            
//             if($xdisTitle == 'Journal')
//             {
//                 $xdisTitle = 'Journal Article';
//             }
            
            $this->_xdisTitle = 'Journal Article';
            
            $this->_authors[] = $this->extract('//dc:creator', $xpath);
            
            $scopusId = $this->extract('//dc:identifier', $xpath);
            $matches = array();
            preg_match("/^SCOPUS_ID\:(\d+)$/", $scopusId, $matches);
            $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;
            $this->_scopusId = "2-s2.0-" . $scopusIdExtracted;
            
            $pageRange = $this->extract('//prism:pageRange', $xpath);
            $pageRange = preg_replace('/[a-zA-Z]/', '', $pageRange);
            $matches = array();
            preg_match("/^(\d+)\-(\d+)$/", str_replace(array(' ', '\r\n', '\n', '\t'), '', $pageRange), $matches);
            
            if(array_key_exists(1, $matches) && array_key_exists(2, $matches))
            {
                $this->_startPage = min(array($matches[1], $matches[2]));
                $this->_endPage = max(array($matches[1], $matches[2]));
            }
            
            $this->_totalPages = $this->_endPage - $this->_startPage;
            
            $this->_loaded = true;
        }
        else 
        {
            $this->_log->err("Problem with affiliation for: {$this->_title}; Affiliations: " . var_export($this->_affiliations, true));
        }
    }
    
    public function extract($query, $xpath)
    {
        $nodeList = $xpath->query($query);
        return $nodeList->item(0)->nodeValue;
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
}

