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
 * Class to handle requests to the Links Article Match Retrieve Service
 * from Thomson Reuters(R).
 *
 * @version 0.1
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 *
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");

class LinksAmrService
{
  //const SERVICE_ENDPOINT = 'https://ws.isiknowledge.com/esti/xrpc';
  const SERVICE_ENDPOINT = 'http://ws.isiknowledge.com/cps/xrpc';
  
  const COLLECTION = 'WOS';
  
  function __construct()
  {

  }

  /**
   *
   * @param array $list 	Named list comprising the data elements to be included in the response,
   * 						and the articles for which you are requesting data
   * @param string $collection 	The product code for the ISI Web of Knowledge collection to be
   * 								searched for matching citations
   * @return mixed
   */
  public static function retrieve($list, $collection = self::COLLECTION)
  {
    $tpl = new Template_API();
    $tpl_file = "links_amr_retrieve.tpl.html";
    $tpl->setTemplate($tpl_file);
    $tpl->assign("collection", $collection);
    $tpl->assign("list", $list);
    $request_data = $tpl->getTemplateContents();

    $xml_api_data_request = new DOMDocument();
    $xml_api_data_request->loadXML($request_data);

    // Do the service request
    $response_document = new DOMDocument();
    $response_document = LinksAmrService::doServiceRequest($xml_api_data_request->saveXML());
    
    return $response_document;		
  }

  /**
   * Method used to perform a service request
   *
   * @param  string $xml Raw XML data to POST to the service
   * @return string The XML returned by the service.
   */
  private static function doServiceRequest($xml)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $config = array(
        'maxredirects' => 0,
        'timeout' => 120
    );
    $client = new Zend_Http_Client(null, $config);
    $client->setUri(self::SERVICE_ENDPOINT);
    $client->setHeaders(
        array(
          'Content-type' => 'text/xml'
        )
    );
    $response = $client->setRawData($xml, 'text/xml')->request('POST');
    
    if ($response->getStatus() == 200) {
      $response_doc = new DOMDocument();
      $response_doc->loadXML($response->getBody());  
      return $response_doc;      
    } else {
      $log->err(
          array
          (
              'An error occurred while fetching data: ' . 
              $response->getStatus() . ': ' .
              $response->getMessage() . ': '.
              self::SERVICE_ENDPOINT, __FILE__, __LINE__
          )
      );
      return false;
    }
  }
}