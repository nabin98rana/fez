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
 * Class to handle access to the Web of Knowledge Web Services.
 *
 * @version 0.1
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 *
 */

//Move this  constant into the DB
define('SCOPUS_WS_BASE_URL', 'https://api.elsevier.com/');

class ScopusService
{
    protected $log;
    
    protected $authToken;
    
    protected $apiKey;
    
    protected $db;
    
    protected $totalCount;//maybe don't need this
    
    protected $recSetStart = 0;
    
    const REC_SET_SIZE = 30;
    
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->log = FezLog::get();
        $this->db = DB_API::get();
        $this->auth();
    }
    
    /**
     * Fetch the auth token
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }
    
    /**
     * Return the cached token
     * @return string
     */
    public function getSavedToken()
    {
        $sql = "SELECT scs_tok, unix_timestamp(ts) as ts FROM " 
            . APP_TABLE_PREFIX . "scopus_session LIMIT 1";
        $stmt = $this->db->query($sql);
        $tokenData = $stmt->fetch();
        
        //If the token is less than two hours old return it
        $token = ($tokenData && ((time() - $tokenData['ts']) < 7200)) 
                ? $tokenData['scs_tok'] 
                : false;
        
        return $token;
    }
    
    /**
     * Clear out the token table and save a new token
     * @param string $token
     */
    public function saveToken($token)
    {
        $sqlTruncate = "TRUNCATE TABLE " . APP_TABLE_PREFIX . "scopus_session";
        $sqlInsert = "INSERT INTO " . APP_TABLE_PREFIX . "scopus_session (scs_tok) VALUES (?)";
        
        try 
        {
            $tr = $this->db->query($sqlTruncate);
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
            //var_dump($e->getMessage() . __LINE__);
        }
        
        try
        {
            $in = $this->db->query($sqlInsert, array($token));
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
            //var_dump($e->getMessage() . __LINE__);
        }
        
        return ($tr && $in) ? true : false;
    }
    
    /**
     * Perform an authentication operation against ScopusAPI
     */
    public function auth()
    {
        $params = array(
            'qs' => array(
                'platform' => 'SCOPUS'
            )
        );
        
        $token = $this->getSavedToken();
        
        if(!$token)
        {
            $xpath = $this->getXPath($this->doCurl($params));
            $tokens = $xpath->query("//authenticate-response/authtoken");
            $token = $tokens->item(0)->nodeValue;
            //$token = "sat_EEA77ED44740DD3FD3CF31100080796DCF1A1E79935650ED739EBDC1824F820BFDB7098C6E81075F9163B11F5B00E99EFAFFAD382AFE5090AB92524D7B996B7D23990E24911E348FA42FAAD7DC073CF36AC5CFA18BEA99FA2EFBE959A04A62AF6B9A4963D00BE5D564CD0AA389B70ABF4C0C7B21F192688371046196F0149FD2A8CFF8F47F5925A5125DC29BE0F62EB306336E6F2A298C5E6033DEC3472B4106096D71D1D1F7EB7F487E4A5464D7F7BF3227B4506127D28D";
            $this->saveToken($token);
            //var_dump($token . " LINE: " . __LINE__);
        }
        
        $this->authToken = $token;
    }
    
    /**
     * Perform a search operation on ScopusAPI with an existing token
     * @param array $query
     * @return boolean
     */
    public function search(array $query)
    {
        if(!$this->authToken)
        {
            //Run the auth method instead of throwing an exception??
            $this->log->err("No authtoken is set for ScopusAPI access:"
                . __FILE__ . ":" . __LINE__);
            return false;
        }
        
        $params = array(
            'action' => 'search',
            'db' => 'index:SCOPUS',
            'qs' => $query
        );
        
        /*$params = array(
            'action' => 'affiliation',
            'db' => 'AFFILIATION_ID:60031004',
            'qs' => array(
                'start' => 201,
                'count' => 401,
                'view' => 'DOCUMENTS'
            )
        );*/
        
        $recordData = $this->doCurl($params, 'content');
        //return file_put_contents('/var/www/fez/tests/dat/entrezFullOutOO.txt', $recordData);
        return $recordData;
    }
    
    /**
     * Grab the next recordset from Scopus if
     * there is one to grab
     * @return mixed
     */
    public function getNextRecordSet()
    {
        $query = array('query' => 'affil(University+of+Queensland)',
                            'count' => self::REC_SET_SIZE,
                            'start' => $this->recSetStart,
                            'view' => 'STANDARD'
        );
        
        $records = $this->search($query);
        
        /*if($records)
        {
            
        }*/
        
        $this->recSetStart = $this->getNextRecStart($records);
        
        return $records;
    }
    
    /**
     * Return the starting record of the next recordset
     * to fetch from the <link> tag provided by the 
     * current record set.
     * @param string $recordSet
     * @return mixed
     */
    public function getNextRecStart($recordSet)
    {
        $xpath = $this->getXPath($recordSet);
        $links = $xpath->query('//default:feed/default:link');
        $nextRecStart = false;
        
        foreach($links as $link)
        {
            $ref = $link->getAttribute('ref');
            if($ref == "next")
            {
                $nextLink = $link->getAttribute('href');
                $matches = array();
                preg_match("/start=(\d+)&/", $nextLink, $matches);
                
                if(array_key_exists(1, $matches))
                {
                    $nextRecStart = $matches[1];
                }
            }
        }
        
        return $nextRecStart;
    }
    
    public function compare($recordSet=null)
    {
        $recordSet = ($recordSet) ? $recordSet : $this->getNextRecordSet();
        //Pull out all the <entry> tags and 
        //create a Scopus record object for
        //each one.
        
        /*$xpath = $this->getXPath($recordSet);
        $records = $xpath->query('//default:entry');*/
        
        $doc = new DOMDocument();
        $doc->loadXML($recordSet);
        $records = $doc->getElementsByTagName('entry');
        
        //$recordHandler = new ScopusRecItem();//this or a new object for each record?
        //Need to flush the object properly if it's to be reused
        
        //get_class($records->item(0));
        foreach($records as $record)
        {
            $recordHandler = new ScopusRecItem();
            $xmlDoc = new DOMDocument();
            $xmlDoc->appendChild($xmlDoc->importNode($record, true));
            $recordHandler->load($xmlDoc->saveXML());
            //var_dump($xmlDoc->saveXML());
        }
    }
    
    public function checkAffiliation($recordObject)
    {
        $xpath = $this->getXPath($recordObject);
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
    
    /**
     * Execute a cURL request on the ScopusAPI
     * @param array $params
     * @param string $utility
     */
    public function doCurl(array $params, $utility="authenticate")
    {
        if(!array_key_exists('qs', $params) || !is_array($params['qs']))
        {
            $this->log->err("Scopus query is not a parameter item or not an array:"
                . __FILE__ . ":" . __LINE__);
            return false;
        }
        
        $uri = (preg_match('/.*\/$/', SCOPUS_WS_BASE_URL)) ? $utility : '/'.$utility;
        $uri .= (array_key_exists('action', $params)) ? "/".$params['action'] : '';
        $uri .= (array_key_exists('db', $params) ? "/".$params['db'] : '');
        $uri .= '?' . http_build_query($params['qs']);
        
        $curlHandle = curl_init(SCOPUS_WS_BASE_URL . $uri);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
                'X-ELS-APIKey: ' . $this->apiKey,
                'Accept: text/xml, application/atom+xml'
        ));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $curlResponse = curl_exec($curlHandle);
        
        if(!$curlResponse)
        {
            $this->log->err(curl_errno($curlHandle));
            //var_dump(curl_errno($curlHandle));
        }
        
        curl_close($curlHandle);
        return $curlResponse;
    }
    
    /**
     * Generate an XPAth object from raw XML
     * @param string $rawXML
     * @return DOMXPath
     */
    public function getXPath($rawXML)
    {
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadXML($rawXML);
        $rootNameSpace = $xmlDoc->lookupNamespaceUri($doc->namespaceURI);
        
        $xpath = new DOMXPath($xmlDoc);
        $xpath->registerNamespace('default', $rootNameSpace);
        
        return $xpath;
    }
}

