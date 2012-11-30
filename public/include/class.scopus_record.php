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
    protected $_pageRange;
    
    protected $_log;
    
    protected $_affiliations = array();
    
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
            
            $this->_docSubType = $this->extract('//subtypeDescription', $xpath);
            
            $this->_scopusId = $this->extract('//dc:identifier', $xpath);
            
            $matches = array();
            preg_match("/^SCOPUS_ID\:(\d+)$/", $this->_scopusId, $matches);
            $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;
            $this->_scopusId = $scopusIdExtracted;
            
            $this->_pageRange = $this->extract('//prism:pageRange', $xpath);
            $matches = array();
            preg_match("/^(\d+)\-(\d+)$/", str_replace(array(' ', '\r\n', '\n', '\t'), '', $this->_pageRange));
            
            if(array_key_exists(1, $matches) && array_key_exists(2, $matches))
            {
                $this->_startPage = min(array($matches[1], $matches[2]));
                $this->_endPage = max(array($matches[1], $matches[2]));
            }
            
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
            $localPidsByScopusId = Record::getPIDsByScopusID($this->_scopusId);
            
            //there should only be one result or else it's an error
            $pidCount = count($localPidsByScopusId);
            if($pidCount == 1)
            {
                $pidFromScopusId = $localPidsByScopusId[0]['rek_scopus_id'];
            }
            elseif($pidCount > 1)
            {
                //log an error
                $this->_log->err("Multiple matches found for Scopus ID ({$this->_scopusId}):".__METHOD__);
                return false;
            }
        }
        
        //try to locate by doi
        if($this->_doi)
        {
            $localPidsByDoi = Record::getPIDsByDoi($this->_doi);
            
            //there should only be one result or else it's an error
            $pidCount = count($localPidsByDoi);
            if($pidCount == 1)
            {
                $pidFromDoi = $localPidsByDoi[0]['rek_doi_pid'];
            }
            elseif($pidCount > 1)
            {
                //log an error
                $this->_log->err("Multiple matches found for DOI({$this->_doi}):".__METHOD__);
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
            $this->_log->err("Multiple returned pids are unequal:".__METHOD__);
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
            
            $downloadedTitle = RCL::normaliseTitle($this->_title);
            $localTitle = RCL::normaliseTitle($title);
            similar_text($downloadedTitle, $localTitle, $percentageMatch);
            //if the fuzzy title match is better than 80%
            if($percentageMatch >= 80)
            {
                //update the record with any data we don't have
                $this->update($pid);
            }
        }
        else 
        {
            //the record does not exists; insert it as a new record
            $this->save();
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
    public function likenAffiliation()
    {
        $affiliated = false;
        foreach($this->_affiliations as $affiliation)
        {
            if(preg_match('/University of Queensland|University of Qld/', 
                                                       $affiliation))
            {
                $affiliated = true;
            }
        }
        
        return $affiliated;
    }
}

