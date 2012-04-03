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
 * Mock ResearcherID service class used when testing against the 
 * Thomson Reuters batch upload/download service.
 *
 * @version 1.0
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 *
 */
//require_once '../../config.inc.php';
include_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).DIRECTORY_SEPARATOR."configs".DIRECTORY_SEPARATOR."config.inc.php");

class ResearcherId_Service_Mock
{
  /**
   * Handles service download requests 
   *
   * @return string
   */
  public function download() {
    $responseXml = '';
    
    $requestXml = file_get_contents('php://input');  
    $functions  = self::getRequestedFunction($requestXml);
    
    foreach ($functions as $function) {
      switch ($function) {
        case 'AuthorResearch.downloadRIDData':

            $tpl = new Template_API();
            $tpl_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'download-rid-data-output.xml';
            $tpl->setTemplate($tpl_file);
            $responseXml = $tpl->getTemplateContents();

//            $responseXml = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'download-rid-data-output.xml');
            $responseXml = new SimpleXMLElement($responseXml);
            break;
        case 'AuthorResearch.getDownloadStatus':

          $tpl = new Template_API();
          $tpl_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'get-download-status-output.xml';
          $tpl->setTemplate($tpl_file);
          $responseXml = $tpl->getTemplateContents();


//          $responseXml = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'get-download-status-output.xml');

          $responseXml = new SimpleXMLElement($responseXml);
          break;
      }
    }    
    return $responseXml;
  }
  
  /**
   * Handles service upload requests 
   *
   * @return string
   */
  public function upload() {
    $responseXml = '';
    
    $requestXml = file_get_contents('php://input');  
    $functions  = self::getRequestedFunction($requestXml);
    
    foreach ($functions as $function) {
      switch ($function) {
        case 'AuthorResearch.uploadRIDData':
          $responseXml = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'upload-rid-data-output.xml');
          $responseXml = new SimpleXMLElement($responseXml);  
          break;
      }
    }    
    return $responseXml;
  }
  
  /**
   * Retrieves the requested functions from the service request
   *  
   * @param $xml The XML request
   * 
   * @return array
   */  
  private static function getRequestedFunction($xml) {
    $functions = array();
    
    $reqXmlObj = new SimpleXMLElement($xml);    
    foreach ($reqXmlObj->fn as $function) {
      $attributes = $function->attributes();
      $functions[] = (string)$attributes['name'];
    }
    return $functions;
  }
}

$service = new Zend_Rest_Server();
$service->setClass('ResearcherId_Service_Mock');
$service->handle();