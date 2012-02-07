<?php
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
// | Authors: Elvi Shu <e.shu@library.uq.edu.au>                          |
// +----------------------------------------------------------------------+

/**
 * This script is used for retrieving Profile & Publications XML content 
 * on existing RID Jobs with status 'Done'.
 */
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.researcherid.php");
include_once(APP_INC_PATH . "class.misc.php");

$jobs = ResearcherID::getJobs(0, 5, array('by' => 'rij_timefinished', 'order' => 'desc'), array('key'=>'rij_status', 'val'=>'DONE'));

foreach ($jobs['list'] as $job) {
    
    // Load XML from rij_lastresponse
    $response = DOMDocument::loadXML($job['rij_lastresponse']);

    $responseXML = processDownloadResponse($response);
    
    echo '<br/>'.$job['rij_id'].' =  '.$responseXML['profileLink'];
    
    if ($responseXML !== false) {
        ResearcherID::updateJobResponseXML($job['rij_ticketno'], $responseXML);
    }

//    break; // test one record;
}


function processDownloadResponse($response_document)
{
    $log = FezLog::get();
    $db = DB_API::get();

    $xpath = new DOMXPath($response_document);
    $xpath->registerNamespace('rid', 'http://www.isinet.com/xrpc41');
    $download_response;
    $return = true;

    $query = "/rid:response/rid:fn[@name='AuthorResearch.getDownloadStatus']/rid:map/rid:val[@name='Response']";
    $elements = $xpath->query($query);
    if (!is_null($elements)) {
      foreach ($elements as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
          $download_response = $node->nodeValue;
        }
      }
    }
    if ($download_response) {
      $xml_dl_response = new SimpleXMLElement($download_response);
       
      foreach ($xml_dl_response->outputfile as $output_file) {

        $type = $output_file->attributes()->type;
        $url = $output_file->url;
        $result = false;

        switch($type) {
          case 'profile':
            $profileXML = processDownloadedProfiles($url);
            $profileLink = $url;  
            break;
          case 'publication':
            $publicationsXML = processDownloadedPublications($url);
            $publicationsLink = $url;
            break;
        }
        
        if ($publicationsXML !== false && $profileXML !== false){
            $result = true;
        }
        
        $return = (! $return) ? false: $result; // ignore result if we have already had a previous fail
        // which will ensure this job is processed again
      }
    }
    
    if ($return === true){
        $return = array('profileXML' => $profileXML, 'profileLink' => $profileLink, 'publicationsXML' => $publicationsXML, 'publicationsLink' => $publicationsLink);
    }
    
    return $return;
}



/**
* Processes the downloaded profiles.
*
* @access  public
* @param   string $url The URL to retrieve the profiles data from
* @return bool True if response processing is successful else false
*/
function processDownloadedProfiles($url)
{
    $log = FezLog::get();
    $db = DB_API::get();

    $urlData = Misc::processURL($url);
    $profile = $urlData[0];
    if (!$profile) {
      $log->err("wasn't able to pull down RID Profile url $url:".print_r($urlData, true));
      return false;
    }

    return $profile;
}

/**
* Processes the downloaded publications.
*
* @access  public
* @param   string $url The URL to retrieve the publications data from
* @return bool True if response processing is successful else false
*/
function processDownloadedPublications($url)
{
    $log = FezLog::get();
    $db = DB_API::get();

    $urlData = Misc::processURL($url);
    $publications = $urlData[0];

    if (!$publications) {
      $log->err("wasn't able to pull down RID url $url:".print_r($urlData, true));
      return false;
    }

    return $publications;
}
  
