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
include_once(APP_INC_PATH . 'class.wok_service.php');
include_once(APP_INC_PATH . 'class.wos_record.php');
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");

Auth::checkAuthentication(APP_SESSION);

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


/**
 * Class to handle records matching via WOK Web Service.
 * 
 * @package fedora & fedora_bypass
 */
class MatchingRecords
{
    /**
     * Return search result
     * For searching on WOK Web Service the following query parameters are used:
     *   - Journal Article
     *      params: editions = array("collection" => "WOS", "edition" => "SCI"
     *                   TI = {Requested_Title}, OG = {Uni of QLD}, "DT" = "@"
     *   - Conference Paper
     *      params: editions = array("collection" => "WOS", "edition" => "ISTP"
     *                   TI = {Requested_Title}
     *                   (No OG & DT specify)
     *
     * WOK API: http://science.thomsonreuters.com/tutorials/wsp_docs/soap/Guide/
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

        // Specify the Document Type & Database Edition on the search query parameter
        // API doc for Database Editions: http://science.thomsonreuters.com/tutorials/wsp_docs/soap/Guide/
        $edition = "SCI";
        switch($dt) {
            case 'Journal Article':
                $doc_type = '@';
                $edition = "SCI";
                break;
            case 'Conference Paper':
                $doc_type = "";
                $edition = "ISTP";
                break;
            default:
                $doc_type = false;
        }

        if($doc_type !== false) {

            // Escape characters used for wrapping title on search query
            $title = $this->_escapeSearchTitle($title);
            
            // Title query param
            $query = 'TI=("'.$title.'")';
            
// Requested by eSpace team to not restrict the search by org unit anymore
//            if(APP_ARTICLE_SEARCH_WOS_ADDRESS != '' ) {
//
//                // Specify the Organisation query param for Journal Article only
//                if ( $doc_type == '@' ){
//                    $query .= ' AND OG=('.APP_ARTICLE_SEARCH_WOS_ADDRESS.') ';
//                }
//            }

            // Doc type query param
            if ( !empty($doc_type) ){
                $query .= ' AND DT=('.$doc_type.')';
            }

	        $depth = ''; // All subscribed years
			$editions = '';
			$sort = 'Relevance';
			$first_rec = 1;
			$num_recs = 3;
            $timeSpan = '';


            if ( defined('WOK_PASSWORD') && WOK_PASSWORD != '') {

                $databaseID = "WOS";
                $editions = array("collection" => $databaseID, "edition" => $edition);

                $wok_ws = new WokService(FALSE);
                $response = $wok_ws->search($databaseID, $query, $editions, $timeSpan, $depth, "en", $num_recs);
                $records = @simplexml_load_string($response->return->records);

            } else { //try and use ESTI service if TR WS Premium not setup

                $result = EstiSearchService::searchRetrieve('WOS', $query, $depth, $editions, $sort, $first_rec, $num_recs);
                $records = @simplexml_load_string($result['records']);

            }

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
     * Saves publication found on ISI WOS.
     * 
     * @example enter_metadata.tpl.html -> Triggerred by "Add" button on Matching Records results on Journal Article titles.
     * @package fedora & fedora_bypass
     * @param string $ut 
     * @return string PID of the newly added record
     */
    public function add($ut) 
    {
    	$log = FezLog::get();
		$db = DB_API::get();

		$pid = '';
		$collection = APP_ARTICLE_ADD_TO_COLLECTION;
        
		if(Fedora_API::objectExists($collection)) {
            
            // Retrieve record from Wok web service
	    	$records = EstiSearchService::retrieve($ut);
            
			if($records) {
                
                if (APP_FEDORA_BYPASS == "ON"){
                    
                    $pid = $this->_addFedoraBypass($records);
                    
                } else {
                    foreach($records->REC as $_record) {
                        $mods = Misc::convertEstiRecordToMods($_record);
                        $times_cited = $_record->attributes()->timescited;
                        $pid = Record::insertFromArray($mods, $collection, 'MODS 1.0', 'Imported from WoS', $times_cited);
                    }
                }
			}
		}
		return $pid;
    }

    /**
     * Clean user query from invalid characters that may cause error on SOAP call.
     * 
     * @param string $userQuery
     * @return string Cleaned user query string.
     */
    protected function _escapeSearchTitle($userQuery = null)
    {
        if (empty($userQuery) && is_null($userQuery)) {
            return "";
        }

        // Escape " double quote from user entered query, 
        // as we are using double quote to wrap the query string sent to SOAP
        $search = "\"";
        $replace = "";
        $userQuery = str_replace($search, $replace, $userQuery);

        return $userQuery;
    }
      
    
    /**
     * Add a record retrieved from WOK Web Service to local database.
     * It parses XML elements retrieved from WOK and saves it to record search keys as a new PID.
     * 
     * @package fedora_bypass
     * @param SimpleXMLElement $records
     * @return string PID of the newly saved record
     */
    protected function _addFedoraBypass($records)
    {
        // Param $record should only contain one publication.
        // However we set $pid as array just in case, and implode the pid as string on the return.
        $pid = array();
        
        // Revert SimpleXML object to string, then load it as DOMDocument, as expected by WosRecItem.
        $xml = $records->asXML();
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xmlRecords = $doc->getElementsByTagName('REC');

        foreach ($xmlRecords as $_record){
            // Instantiate WosRecItem object with our DOMElement $_record
            $rec = new WosRecItem($_record);

            // Get collections
            $wos_collection = trim(APP_WOS_COLLECTIONS, "'");
            if (!defined('APP_WOS_COLLECTIONS') || trim(APP_WOS_COLLECTIONS) == "") {
                $rec->collections = array(RID_DL_COLLECTION);
            } else {
                if ($aut_ids) {
                    $rec->collections = array(RID_DL_COLLECTION);
                } else {
                    $rec->collections = array($wos_collection);
                }
            }        

            $history = "Imported from WoS";
            
            // Save WosRecItem 
            $pid[] = $rec->save($history);
        }
        
        $pid = implode(",", $pid);
        return $pid;
    }
}