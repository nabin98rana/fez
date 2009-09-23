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

include_once('config.inc.php');
include_once(APP_INC_PATH . 'class.esti_search_service.php');
include_once(APP_INC_PATH . 'class.links_amr_service.php');
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");

Auth::checkAuthentication(APP_SESSION);
if(! Auth::isAdministrator()) {
	exit;
}

$server = new Zend_Json_Server();
$server->setClass('MatchingRecords');

if ('GET' == $_SERVER['REQUEST_METHOD']) {
    
	$server->setTarget($_SERVER["SCRIPT_NAME"])
           ->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_2);
    $smd = $server->getServiceMap();
    $smd->setDojoCompatible(true);
    header('Content-Type: application/json');
    echo $smd;
    return;
}
$server->handle();


class MatchingRecords
{
    /**
     * Return search result
     *
     * @param  string $title The title to search on
     * @param  string $dt Document Type
     * @return string
     */
    public function search_wos($title, $dt)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
    	$matches = array();
    	
    	$doc_type = false;
    	switch($dt) {
    		case 'Journal Article':    			
    			$doc_type = '@';
    			break;
    		default:
    			$doc_type = false;
    	}
    	
    	if($doc_type) {
    		$query = '';
    		if(APP_ARTICLE_SEARCH_WOS_ADDRESS != '') {
	        	$query = 'OG=('.APP_ARTICLE_SEARCH_WOS_ADDRESS.') AND DT=('.$doc_type.') AND TI=('.$title.')';	
    		}
	        else {
	        	$query = 'DT=('.$doc_type.') AND TI=('.$title.')';
	        }
	        $depth = ''; // All subscribed years
			$editions = '';
			$sort = 'Relevance';
			$first_rec = 1;
			$num_recs = 3;
			
			$result = EstiSearchService::searchRetrieve('WOS', $query, $depth, $editions, $sort, $first_rec, $num_recs);
    		
			$records = @simplexml_load_string($result['records']);	
			
			if($records) {
				foreach($records->REC as $record) {
					$mods = Misc::convertEstiRecordToMods($record);
					
					if($mods) {
						// Check if exists
						$pid = Record::getPIDByIsiLoc($mods['identifier_isi_loc']);
						if($pid) {
							$mods['record_exists'] = 1;
							$mods['pid'] = $pid;
						}
						// Fix author name
						for($i=0; $i < count($mods['name']); $i++) {
							$mods['name'][$i]['namePart_personal'] = str_replace(',', '', $mods['name'][$i]['namePart_personal']);
						}
						$matches[] = $mods;
					}
				}
			}
    	}

    	if($matches > 0) {
			$tpl = new Template_API();
			$tpl->setTemplate("workflow/edit_metadata_helpers/matching_records_results.tpl.html");
			$tpl->assign('matches', $matches);
			$tpl->assign('rel_url', APP_RELATIVE_URL);
			$tpl->assign('found', $result['recordsFound']);
			$tpl->assign('max_results', $num_recs);
			$tpl->assign('dupe_records', $dupe_records);
			
			return $tpl->getTemplateContents();
    	}
    	else {
    		return '';
    	}
    }

    /**
     * Return search result
     *
     * @param  string $title The title to search on
     * @return string
     */
    public function search_repo($title)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
    	$dupe_records = array();
    	$max_results = 5;
    	$count = 0;
	
    	$dupes = DuplicatesReport::similarTitlesQuery('dummy', trim($title));

    	if(count($dupes) > 0) {
    		for($i=0; $i<count($dupes); $i++) {
    			if($dupes[$i]['relevance'] > 1) {
    				$details = Record::getDetailsLite($dupes[$i]['pid']);
    				$details[0]['relevance'] = $dupes[$i]['relevance'];
    				$dupe_records[] = $details;
    				$count++;
    			}
    			if($count >= $max_results) {
    				break;
    			}
    		}
    	}
    	
    	if($count > 0) {
			$tpl = new Template_API();
			$tpl->setTemplate("workflow/edit_metadata_helpers/matching_records_results.tpl.html");
			$tpl->assign('matches', array());
			$tpl->assign('rel_url', APP_RELATIVE_URL);
			$tpl->assign('found', $count);
			$tpl->assign('max_results', $max_results);
			$tpl->assign('dupe_records', $dupe_records);
			
			return $tpl->getTemplateContents();
    	}
    	else {
    		return '';
    	}
    }
    
    /**
     * 
     * @param $ut
     * @return string
     */
    public function add($ut) 
    {
    	$log = FezLog::get();
		$db = DB_API::get();

		$pid = '';
		$collection = APP_ARTICLE_ADD_TO_COLLECTION;
				
		if(Fedora_API::objectExists($collection)) {
	    	$records = EstiSearchService::retrieve($ut);
			
			if($records) {
				foreach($records->REC as $_record) {
					
					$mods = Misc::convertEstiRecordToMods($_record);
					$times_cited = $_record->attributes()->timescited;
	    			$pid = Record::insertFromArray($mods, $collection, 'MODS 1.0', 'Imported from WoS', $times_cited);
				}
			}
		}
		return $pid;
    }
}