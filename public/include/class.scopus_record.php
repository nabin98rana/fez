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

class ScopusRecItem extends RecordItem
{
    protected $_pageRange;
    
    protected $_log;
    
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
        
        $xpath = $this->getXPath($recordData);
        
        $this->_doi = $this->extract('//entry/prism:doi', $xpath);
        
        $this->_title = $this->extract('//dc:title', $xpath);
        
        $this->_issn = $this->extract('//entry/prism:issn', $xpath);
        
        $this->_volume = $this->extract('//entry/prism:volume', $xpath);
        
        $this->_docType = $this->extract('//entry/prism:aggregationType', $xpath);
        
        $this->_docSubType = $this->extract('//entry/subtypeDescription', $xpath);
        
        $this->_scopusId = $this->extract('//entry/dc:identifier', $xpath);
        $matches = array();
        preg_match("/^SCOPUS_ID\:(\d+)$/", $this->_scopusId, $matches);
        $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;
        $this->_scopusId = $scopusIdExtracted;
        
        $this->_pageRange = $this->extract('//entry/prism:pageRange', $xpath);
        $matches = array();
        preg_match("/^(\d+)\-(\d+)$/", str_replace(array(' ', '\r\n', '\n', '\t'), '', $this->_pageRange));
        
        if(array_key_exists(1, $matches) && array_key_exists(2, $matches))
        {
            $this->_startPage = min(array($matches[1], $matches[2]));
            $this->_endPage = max(array($matches[1], $matches[2]));
        }
        
    }
    
    public function extract($query, $xpath)
    {
        $nodeList = $xpath->query($query);
        return $nodeList->item(0)->nodeValue;
    }
    
    /**
     * @see RecordItem::liken()
     */
    public function liken()
    {
        $localPidsByScopusId = array();
        $localPidsByDoi = array();
        $pid = null;
        $pidFromDoi = null;
        $pidFromScopusId = null;
        
        //try to locate by scopus id
        if($this->_scopusId)
        {
            $localPidsByScopusId = Record::getPIDByScopusID($this->_scopusId);
            
            //there should only be one result or else it's an error
            if(count($localPidsByScopusId) == 1)
            {
                $pidFromScopusId = $localPidsByScopusId['rek_scopus_id'];
            }
            else 
            {
                //log an error
                $this->_log->err("Multiple matches found for Scopus ID ({$this->_scopusId}):".__CLASS__."::".__METHOD__);
                return false;
            }
        }
        
        //try to locate by doi
        if($this->_doi)
        {
            $localPidsByDoi = Record::getPIDByDoi($this->_doi);
            
            //there should only be one result or else it's an error
            if(count($localPidsByDoi) == 1)
            {
                $pidFromDoi = $localPidsByDoi['rek_doi'];
            }
            else 
            {
                //log an error
                $this->_log->err("Multiple matches found for DOI({$this->_doi}):".__CLASS__."::".__METHOD__);
                return false;
            }
        }
        
        //if there are two returned pids (ie not null) and they are equal
        if(($pidFromDoi && $pidFromScopusId) && ($pidFromDoi == $pidFromScopusId))
        {
            //pid is one of those (doesn't matter which)
            $pid = $pidFromScopusId;
        }
        //if there are two returned pids (ie not null) and they are NOT equal
        elseif(($pidFromDoi && $pidFromScopusId) && ($pidFromDoi != $pidFromScopusId))
        {
            //log error
            return false;
        }
        //if a pid is returned only by doi
        elseif($pidFromDoi && is_null($pidFromScopusId))
        {
            $pid = $pidFromDoi;
        }
        //if a pid is returned only by scopus id
        elseif($pidFromScopusId && is_null($pidFromDoi))
        {
            $pid = $pidFromScopusId;
        }
        
        if($pid)
        {
            //we have a pid so lets try a fuzzy title match
            $rec = new Record();
            $title = $rec->getTitleFromIndex($pid);
            
            $percentageMatch = 0;
            similar_text($first, $second, $percentageMatch);
            //if the fuzzy title match is better than 80%
            if($percentageMatch >= 80)
            {
                //update the record with any data we don't have
            }
            else 
            {
                //the record does not exists; insert it as a new record
            }
        }
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
    public function likenAffiliation($recordData) //not sure if we need this
    {
        $xpath = $this->getXPath($recordData);
        $affiliated = false;
        $affiliations = $xpath->query('//entry/affiliation');
        
        foreach($affiliations as $affiliation)
        {
            if(preg_match('/University of Queensland|University of Qld/', 
                                                       $affiliation->nodeValue))
            {
                $affiliated = true;
            }
        }
        
        return $affiliated;
    }
}

