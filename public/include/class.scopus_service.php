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
 * @author Chris Maj <c.maj@library.uq.edu.au>
 *
 */

//Move this  constant into the DB
//define('SCOPUS_WS_BASE_URL', 'https://api.elsevier.com/');

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
    * Getter overload
    * @param string $propName
    */
    public function __get($propName)
    {
        return $this->$propName;
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
            $curlRes = $this->doCurl($params);
//             var_dump($curlRes);
            $xpath = $this->getXPath($this->doCurl($params));
            $tokens = $xpath->query("//authenticate-response/authtoken");
            $token = $tokens->item(0)->nodeValue;
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
        
        /***$params = array(
            'action' => 'search',
            'db' => 'index:SCOPUS',
            'qs' => $query
        );*/
        
        /*$params = array(
            'action' => 'affiliation',
            'db' => 'AFFILIATION_ID:60031004',
            'qs' => array(
                'start' => 1,
                'count' => 30,
                'view' => 'DOCUMENTS'
            )
        );*/
        
        $recordData = $this->doCurl($params, 'content');
        //return file_put_contents('/var/www/fez/tests/dat/entrezFullOutOO.txt', $recordData);
//         file_put_contents('/var/www/fez/tests/dat/scopusFullOut120601.txt', $recordData, FILE_APPEND);
        return $recordData;
    }
    
    /**
     * Grab the next recordset from Scopus if
     * there is one to grab
     * @return mixed
     */
    public function getNextRecordSet()
    {
        $query = array('query' => 'affil(University+of+Queensland)+PUBYEAR+BEF+2010',
                            'count' => self::REC_SET_SIZE,
                            'start' => $this->recSetStart,
                            'view' => 'STANDARD',
                            //'date' => '2010-2011'
                            //'view' => 'COMPLETE'
        );
        
        $records = $this->search($query);
        
        $this->recSetStart = $this->getNextRecStart($records);
        
        return $records;
    }
    
    public function getRecordByScopusId($scopusId)
    {
        $params = array(
                'action' => 'abstract',
                'db' => 'SCOPUS_ID:' . $scopusId,
                'qs' => array()
        );
    
        return $this->doCurl($params, 'content');
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
//         echo "\nNEXT REC START:"; var_dump($nextRecStart);
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
        
        $nameSpaces = array(
                'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
                'dc' => "http://purl.org/dc/elements/1.1/"
        );
        
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
            $recordHandler->load($xmlDoc->saveXML(), $nameSpaces);
            $recordHandler->liken();
            //var_dump($xmlDoc->saveXML());
        }
    }
    
    /* public function checkAffiliation($recordObject) //Moved this into the scopus_record class
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
    } */
    
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
        
//         var_dump(SCOPUS_WS_BASE_URL . $uri);
        //$uri = "content/article/SCOPUS_ID:84858076610?view=META_ABS";
        //$uri = "content/affiliation/AFFILIATION_ID:60031004?start=1&count=200&view=DOCUMENTS";
        //$uri = "content/abstract/SCOPUS_ID:34250025139";
        //http://api.elsevier.com/content/search/index:AFFILIATION?query=.
        //$uri = "content/search/index:AFFILIATION?query=University+of+Queensland";
        //$uri = "content/abstract/SCOPUS_ID:84870252763";
//         $uri = "content/search/index:SCOPUS?query=affil(University%2Bof%2BQueensland)&count=30&start=0%26date=2007-2012&view=STANDARD";
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
//             var_dump(curl_errno($curlHandle));
        }
        
        curl_close($curlHandle);
        //var_dump($curlResponse);
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

