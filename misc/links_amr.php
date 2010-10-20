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

include_once('../config.inc.php');
include_once(APP_INC_PATH . 'class.links_amr_service.php');
include_once(APP_INC_PATH . "class.record.php");

$max = 50; 		// Max number of pubs to send with each service request call
$sleep = 1; 	// Number of seconds to wait for between successive service calls 

$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
$filter["searchKey".Search_Key::getID("Display Type")] = 
    XSD_Display::getXDIS_IDByTitleVersion('Journal Article', 'MODS 1.0'); // Journal Articles only

$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

for ($i=0; $i<((int)$listing['info']['total_pages']+1); $i++) {
  
  // Skip first loop - we have called getListing once already
  if ($i>0) {
    $listing = Record::getListing(
        array(), array(9,10), $listing['info']['next_page'], $max, 'Created Date', false, false, $filter
    );
  }
  
  $list = array();
  if (is_array($listing['list'])) {
     for ($j=0; $j<count($listing['list']); $j++) {	 		
       if (empty($listing['list'][$j]['rek_isi_loc'])) {
         $listing['list'][$j]['year'] = date('Y', strtotime($listing['list'][$j]['rek_date'])); 
         $list[] = $listing['list'][$j];		
       }
     }
  }
  
  if (count($list) > 0) {
    $records = LinksAmrService::retrieve($list);
        
    $xpath = new DOMXPath($records);
    $xpath->registerNamespace('lamr', 'http://www.isinet.com/xrpc41');
    $query = "/lamr:response/lamr:fn[@name='LinksAMR.retrieve'][@rc='OK']/lamr:map/lamr:map";			
    $node_list = $xpath->query($query);
    
    if (!is_null($node_list)) {
      foreach ($node_list as $_element) {
        $pid = $_element->getAttribute('name');
        $_query = $query . "[@name='$pid']/lamr:map[@name='".LinksAmrService::COLLECTION."']/lamr:val[@name='ut']";
        $_node_list = $xpath->query($_query);		
        if (!is_null($_node_list)) {	
          if ($_node_list->length > 0) { 		
            $ut = $_node_list->item(0)->nodeValue;
            
            file_put_contents('/tmp/found_uts.txt', "$pid - $ut", FILE_APPEND); // TEST
            // Update record with new UT
            /*$record = new RecordGeneral($pid);
            $search_keys = array("ISI Loc");
            $values = array($ut);
            $record->addSearchKeyValueList(
                "MODS", "Metadata Object Description Schema", $search_keys, $values, true
            );*/        				
          }
        }
      }
    }
    sleep($sleep); // Wait before using the service again		
  }
}