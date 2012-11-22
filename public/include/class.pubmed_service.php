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

/**
 * Class to handle access to the Entrez Embase service.
 *
 * @version 0.1
 * @author Chris Maj <c.maj@library.uq.edu.au>
 *
 */

//Put this constant into the db
define('PUBMED_WS_BASE_URL', 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/');

//include_once(APP_INC_PATH . "class.embase_session.php");

class PubmedService
{
    /**
     * Fez log object
     * @var Fez_Log
     */
    protected $log;
    
    /**
     * Base URL of the service providing access to the Pubmed database
     * @var string
     */
    protected $baseURL;
    
    /**
     * Number of full records to be downloaded.
     * Set by the initial search of PubMed
     * @var integer
     */
    protected $recordCount;
    
    /**
     * The maximum number of full records to download at a time
     * @var integer
     */
    const REC_INCREMENT = 25;
    
    /**
     * Default search term to use if one is not provided.
     * @var string
     */
    const DEF_QUERY = 'university+of+queensland[affiliation]';
    
    /**
     * Set the log and base URL if one is provided
     * @param string $baseURL
     */
    public function __construct($baseURL=PUBMED_WS_BASE_URL)
    {
        $this->log = FezLog::get();
        $this->baseURL = $baseURL;
    }
    
    /**
     * Perform an initial search for records. 
     * Retrieve the total number of records found.
     * Save record ids to the Entrez history server.
     * Incrementally download full records.
     * @param string $searchTerm
     * @param boolean $debug
     * @return string
     */
    public function search($searchTerm=null, $debug=null)
    {
        $searchTerm = ($searchTerm) ? $searchTerm : self::DEF_QUERY;
        
        $params = array(
            'term' => $searchTerm,
            'usehistory' => 'y'
        );
        
        $searchResult = $this->doCurl($params);
        
        if($debug)
        {
            $this->recordCount = 200;
        }
        else
        {
            $this->setCount($searchResult);
        }
        
        $records;
        
        for($retstart=0;$retstart<$this->recordCount;$retstart+=self::REC_INCREMENT)
        {
            $records .= $this->getRecords($searchResult, $retstart);
        }
        
        return $records;
    }
    
    /**
     * Download a set of full records based on an 
     * initial search response provided.
     * @param string $searchResponse
     * @param integer $retStart
     * @return mixed
     */
    public function getRecords($searchResponse, $retStart=null)
    {
        $idDoc = new DOMDocument();
        $idDoc->preserveWhiteSpace = false;
        $idDoc->loadXML($searchResponse);
        $xp = new DOMXPath($idDoc);
        
        $webenv = $xp->query('//WebEnv');
        $qkey = $xp->query('//QueryKey');
        
        $webenv = $webenv->item(0)->nodeValue;
        $qkey = $qkey->item(0)->nodeValue;
        
        $params = array(
            'query_key' => $qkey,
            'WebEnv' => $webenv,
            'retmax' => self::REC_INCREMENT,
            'retmode' => 'xml'
        );
        
        if($retStart)
        {
            $params['retstart'] = $retStart;
        }
        
        
        return $this->doCurl($params, 'efetch');
    }
    
    /**
     * Perform a cURL request on a given Entrez utility
     * @param array $params
     * @param string $utility
     */
    protected function doCurl($params, $utility="esearch")
    {
        $params['db'] = (!array_key_exists('db', $params)) ? 'pubmed' : $params['db'];
        $paramStr = http_build_query($params);
        $url = $this->baseURL . $utility . ".fcgi?" . $paramStr;
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        
        if(!$response)
        {
            $this->log->err(curl_error($curl));
        }
        
        curl_close($curl);
        return $response;
    }
    
    /**
     * Extract a record count from an initial 
     * search response and save it into the object.
     * @param string $searchResponse
     */
    protected function setCount($searchResponse)
    {
        $countXML = new DOMDocument();
        $countXML->preserveWhiteSpace = false;
        $countXML->loadXML($searchResponse);
        $xp = new DOMXPath($countXML);
        $count = $xp->query('//Count');
        $this->recordCount = $count->item(0)->nodeValue;
    }
    
    /**
     * Return the object's record count
     * @return integer
     */
    public function getCount()
    {
        return $this->recordCount;
    }
}

