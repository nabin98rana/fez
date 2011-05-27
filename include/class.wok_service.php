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

/**
 * Class to handle access to the Web of Knowledge Web Services.
 *
 * @version 0.1
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 *
 */

class WokService
{
  private $searchEndpoint;
  private $authEndpoint;
  private $client;
  private $sessionId;
  private $log;
  public $ready;
  
  /**
   * Constructor
   *
   * @param bool $lite TRUE to use lite search
   */
  public function __construct($lite = TRUE)
  {
    $this->log = FezLog::get();
    $this->authEndpoint = WOK_WS_BASE_URL . 'WOKMWSAuthenticate'; 
    $this->searchEndpoint = WOK_WS_BASE_URL . 'WokSearch';
    if ($lite) {
      $this->searchEndpoint .= 'Lite';
    }
    $options = array();
    $this->client = new Zend_Soap_Client($this->authEndpoint . '?wsdl', $options);
    $this->sessionId = $this->authenticate(WOK_USERNAME, WOK_PASSWORD);
    if ($this->sessionId) {
      $this->ready = TRUE;
      $this->sessionId = "\"{$this->sessionId}\""; // cursed @ symbol, can't encode else the service
                                                   // fails to recognise the session
      $this->client->setOptions($options);
      $this->client->setWsdl($this->searchEndpoint.'?wsdl');      
//    register_shutdown_function(array($this, 'closeSession'));
//      register_shutdown_function('$this->closeSession');
    } else {
      $this->ready = FALSE;
    }
  }

  /**
   * Performs a search of records from an ISI Web of Knowledge, Web Service Premium.
   *
   * @param string $databaseID Identifies the ISI Web of Knowledge resource that this request will search (default is WOS).
   * @param string $userQuery The search expression in Advanced Search format.
   * @param string $editions The editions that this search will cover. Array containing collection and edition strings as elements.
   * @param array $timeSpan The time span that this search will cover such as 2000-2002. Using begin and end array elements.
   * @param array $symbolicTimeSpan This element defines a range of load dates. Allowed values are 1week, 2week and 4week The load date is the date when a record was added to a database.
   * If symbolicTimeSpan is specified, the timeSpan parameter should be omitted, or it must be null.
   * If timeSpan and symbolicTimeSpan are both null or omitted, then the maximum publication date time span will be inferred from the editions data.
   * @param string $queryLanguage This element can take only one value: en for English.
   * @param int $count The number of results to return in the initial resultset you get back from the initial query
   * @return SimpleXMLElement The object containing records found in WoS matching the search query specified.
   */
  public function search($databaseID = "WOS", $userQuery, $editions='', $timeSpan=array(), $symbolicTimeSpan="1week", $queryLanguage="en", $count)
  {

    $editions = array("collection" => $databaseID, "edition" => "SCI");
    $search = array(
               'queryParameters' =>
                    array(
                      'databaseID' => $databaseID,
                      'userQuery' => $userQuery,
                      'editions' => $editions,
//                      'timeSpan' => $timeSpan,
                      'symbolicTimeSpan' => $symbolicTimeSpan,
                      'queryLanguage' => $queryLanguage
                    ),
               'retrieveParameters' =>
                    array(
                       'firstRecord' => 1,
                       'count' => $count
                    )
    );
    try {
      // Make SOAP request
      $this->client->setCookie(WOK_COOKIE_NAME, $this->sessionId);
      $response = $this->client->search($search);
      return $response;
    }
    catch(SoapFault $ex) {
      $this->log->err($ex);
      return FALSE;
    }
  }

  /**
   * The retrieve operation submits query returned by a previous search, citingArticles, relatedRecords, 
   * or retrieveById operation.
   *
   * @param int $queryID
   * @param int $first_record
   * @param int $count
   * @param array $fields
   * @param array $options
   * @param array $collection_fields
   * @return unknown
   */
  public function retrieve($queryId, $first_record, $count, $fields = array(), $options = array(), $collection_fields = array())
  {

      $retrieve = array(
        'queryId' => $queryId,
        'retrieveParameters' => array(
            'firstRecord' => $first_record,
            'count' => $count
          )
      );

    try {
      // Make SOAP request
      $this->client->setCookie(WOK_COOKIE_NAME, $this->sessionId);
      $response = $this->client->retrieve($retrieve);      
      return $response;
    }
    catch(SoapFault $ex) {
      $this->log->err($ex);
      return FALSE;
    }    
  }

  /**
   * The retrieveById operation returns records identified by unique identifiers.
   * The identifiers are specific to each database.
   *
   * @param array $uids Array of record identifiers. It cannot be null or contain an empty array.
   * @param string $database_id Identifies the ISI Web of Knowledge resource that this request 
   *                            will search (default is WOS)
   * @param string $query_lang This element can take only one value: en for English.
   */
  public function retrieveById($uids, $database_id = WOK_DATABASE_ID, $query_lang = 'en')
  {
    $retrieve = array(
      'databaseId' => $database_id,
      'queryLanguage' => $query_lang,
      'uids' => $uids,
      'retrieveParameters' => array(
          'firstRecord' => '1',
          'count' => count($uids)
        )
    );
    try {      
      $this->client->setCookie(WOK_COOKIE_NAME, $this->sessionId);
      $response = $this->client->retrieveById($retrieve);
      return $response->return->records;
    }
    catch(SoapFault $ex) {
      $this->log->err($ex);
      return FALSE;
    }
  }

  /**
   * The authenticate operation creates a session and obtains a session ID. 
   * Subsequent operations must incorporate this session ID.
   */
  private function authenticate($username, $password)
  {
    $options = array(
        'login' => $username,
        'password' => $password
    );
    try {
      // Make SOAP request      
      $this->client->setOptions($options);
      $this->client->setWsdl($this->authEndpoint.'?wsdl');         
      $response = $this->client->authenticate();
      
      // Unset these options on the client, will use cookie for remainder of session
      $options = array(
        'login' => null,
        'password' => null
      );
      $this->client->setOptions($options);
      return $response->return;
    }
    catch(SoapFault $ex) {
      $this->log->err($ex);
      return FALSE;
    }
  }
  
  /**
   * The authenticate operation creates a session and obtains a session ID. 
   * Subsequent operations must incorporate this session ID.
   */
  public function closeSession()
  {
    try {
      // Make SOAP request 
      $this->client->setCookie(WOK_COOKIE_NAME, $this->sessionId); 
      $this->client->closeSession();
    }
    catch(SoapFault $ex) {
      $this->log->err($ex);
      return FALSE;
    }
  }
}
