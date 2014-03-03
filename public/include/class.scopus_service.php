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
// | Authors: Chris Maj <c.maj@library.uq.edu.au>                         |
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle access to the Web of Knowledge Web Services.
 *
 * @version 0.1
 * @author Chris Maj <c.maj@library.uq.edu.au>
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 *
 */

class ScopusService
{
    protected $_log;

    protected $_authToken;

    protected $_apiKey;

    protected $_db;

    protected $_recSetStart = 0;

    protected $_recSetSize = 30;

    protected $_lastSet = false;

    public function __construct($apiKey = APP_SCOPUS_API_KEY)
    {
        $this->_apiKey = $apiKey;
        $this->_log = FezLog::get();
        $this->_db = DB_API::get();
        $this->auth();
    }

    /**
     * Fetch the auth token
     * @return string
     */
    public function getAuthToken()
    {
        return $this->_authToken;
    }

    /**
     * Return the cached token
     * @return string
     */
    public function getSavedToken()
    {
        $sql = "SELECT scs_tok, unix_timestamp(scs_ts) as ts FROM "
            . APP_TABLE_PREFIX . "scopus_session LIMIT 1";
        $stmt = $this->_db->query($sql);
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

        try {
            $tr = $this->_db->query($sqlTruncate);
        } catch (Exception $e) {
            $this->_log->err($e->getMessage());
        }

        try {
            $in = $this->_db->query($sqlInsert, array($token));
        } catch (Exception $e) {
            $this->_log->err($e->getMessage());
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

        if (!$token) {
            $curlRes = $this->doCurl($params);
            $xpath = $this->getXPath($curlRes);
            $tokens = $xpath->query("//authenticate-response/authtoken");
            $token = $tokens->item(0)->nodeValue;
            $this->saveToken($token);
        }

        $this->_authToken = $token;
    }

    /**
     * Perform a search operation on ScopusAPI with an existing token
     * @param array $query
     * @return boolean
     */
    public function search(array $query)
    {
        if (!$this->_authToken) {
            //Run the auth method instead of throwing an exception??
            $this->_log->err("No authtoken is set for ScopusAPI access:"
                . __FILE__ . ":" . __LINE__);
            return false;
        }

        $params = array(
            'action' => 'search',
            'db' => 'index:SCOPUS',
            'qs' => $query
        );

        $recordData = $this->doCurl($params, 'content');
        return $recordData;
    }

    /**
     * Grab the next recordset from Scopus if
     * there is one to grab
     * @return mixed
     */
    public function getNextRecordSet($query = null)
    {
        if (!$query) {
            $query = array('query' => 'affil(University+of+Queensland)+rr=30',
                'count' => $this->_recSetSize,
                'start' => $this->_recSetStart,
                'view' => 'STANDARD',
            );
        }

        $records = $this->search($query);
        $this->_recSetStart = $this->getNextRecStart($records);

        return $records;
    }

    public function getRecordByScopusId($scopusId)
    {
        $scopusId = str_ireplace('2-s2.0-', '', $scopusId);
        $scopusId = str_ireplace('SCOPUS_ID:', '', $scopusId);
        $query = array('view' => 'FULL');
        $params = array(
            'action' => 'abstract',
            'db' => 'SCOPUS_ID:' . $scopusId,
            'qs' => $query
        );

        return $this->doCurl($params, 'content');
    }

    // Test it out on  http://www.scopus.com/search/form.url?display=advanced
    // Return false for error, 0 for no results found else return the raw $xml
    public function getRecordsBySearchQuery($search, $view = 'STANDARD', $numRecs = 5, $start = 0)
    {
        $query = array('query' => $search,
            'count' => $numRecs,
            'start' => $start,
            'view' => $view,
        );
        $xml = $this->getNextRecordSet($query);
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadXML($xml);

        $xpath = new DOMXPath($xmlDoc);

        if ($xpath->query("/service-error")->length > 0) {
            echo $xml;
            return false;
        }

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $records = $doc->getElementsByTagName('identifier');
        if ($records->length == 0) {
            return 0;
        }

        return $xml;
    }

    /**
     * Return the sequence number of the record at
     * which to start fetching the next record set.
     * @return integer
     */
    public function getRecSetStart()
    {
        return $this->_recSetStart;
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
        if ($this->_lastSet) {
            return false;
        }

        $xpath = $this->getXPath($recordSet);
        $links = $xpath->query('//default:feed/default:link');
        $nextRecStart = false;

        foreach ($links as $link) {
            $ref = $link->getAttribute('ref');
            if ($ref == "next") {
                $nextLink = $link->getAttribute('href');
                $matches = array();
                preg_match("/start=(\d+)&/", $nextLink, $matches);

                if (array_key_exists(1, $matches)) {
                    $nextRecStart = $matches[1];
                }
            }
        }

        if (!$nextRecStart) {
            $this->_lastSet = true;
            $nextRecStart = $this->_recSetStart + $this->_recSetSize;
        }

        return $nextRecStart;
    }

    /**
     * Download records from Scopus, perform
     * de-duping and enter into database.
     * This method bypasses the queueing mechanism
     * and performs deduping straight away.
     */
    public function downloadRecords()
    {
        $xml = $this->getNextRecordSet();

        $nameSpaces = array(
            'd' => 'http://www.elsevier.com/xml/svapi/abstract/dtd',
            'ce' => 'http://www.elsevier.com/xml/ani/common',
            'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
            'dc' => "http://purl.org/dc/elements/1.1/",
            'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
        );

        while ($this->_recSetStart) {
            $doc = new DOMDocument();
            $doc->loadXML($xml);
            $records = $doc->getElementsByTagName('identifier');

            foreach ($records as $record) {
                $csr = new ScopusRecItem();

                $scopusId = $record->nodeValue;
                $matches = array();
                preg_match("/^SCOPUS_ID\:(\d+)$/", $scopusId, $matches);
                $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;
                //TODO: Check for existing scopus ID, DOI, Title first, so you don't waste time on looking up the full metadata
                // Maybe there is nothing in the full metadata we do deduping on anyway?


                $iscop = new ScopusService($this->_apiKey);
                $rec = $iscop->getRecordByScopusId($scopusIdExtracted);

                $csr->load($rec, $nameSpaces);
                $csr->liken();
            }

            $xml = $this->getNextRecordSet();

        }
    }

    /**
     * Execute a cURL request on the ScopusAPI
     * @param array $params
     * @param string $utility
     */
    public function doCurl(array $params, $utility = "authenticate")
    {
        if (!array_key_exists('qs', $params) || !is_array($params['qs'])) {
            $this->_log->err("Scopus query is not a parameter item or not an array:"
                . __FILE__ . ":" . __LINE__);
            return false;
        }

        $uri = (preg_match('/.*\/$/', SCOPUS_WS_BASE_URL)) ? $utility : '/' . $utility;
        $uri .= (array_key_exists('action', $params)) ? "/" . $params['action'] : '';
        $uri .= (array_key_exists('db', $params) ? "/" . $params['db'] : '');
        $uri .= '?' . http_build_query($params['qs']);
        $uri = str_replace('%2B', '+', $uri); //Currently scopus can't handle %2B it has to be +
        $curlHandle = curl_init(SCOPUS_WS_BASE_URL . $uri);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
            'X-ELS-APIKey: ' . $this->_apiKey,
            'Accept: text/xml, application/atom+xml'
        ));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $curlResponse = curl_exec($curlHandle);

        if (!$curlResponse) {
            $this->_log->err(curl_errno($curlHandle));
        } elseif (is_numeric(strpos($curlResponse, 'service-error'))) {
            $this->_log->err($curlResponse);
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
        $rootNameSpace = $xmlDoc->lookupNamespaceUri($xmlDoc->namespaceURI);

        $xpath = new DOMXPath($xmlDoc);
        $xpath->registerNamespace('default', $rootNameSpace);

        return $xpath;
    }
}

