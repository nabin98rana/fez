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
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . 'class.scopus_record.php');
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
 * Class to handle records matching via WOK Web Service and Scopus Web Service.
 *
 * @package fedora & fedora_bypass
 */
class MatchingRecords
{
    /**
     * Return search result
     * For searching on WOK Web Service
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

        // Specify the Document Type & Database Edition on the search query parameter
        // API doc for Database Editions: http://science.thomsonreuters.com/tutorials/wsp_docs/soap/Guide/
        //$edition = "SCI";
        switch($dt) {
            case 'Journal Article':
                //$doc_type = '@';
                //$edition = "SCI";
                $doc_type = '';
                break;
            case 'Conference Paper':
                //$doc_type = "";
                //$edition = "ISTP";
                $doc_type = '';
                break;
            default:
                $doc_type = false;
        }

        if($doc_type !== false) {

            // Escape characters used for wrapping title on search query
            $title = $this->_escapeSearchTitle(trim($title));

            // Title query param
            $query = 'TI=("'.$title.'")';

            // Doc type query param
            if ( !empty($doc_type) ){
                $query .= ' AND DT=('.$doc_type.')';
            }

	        $depth = ''; // All subscribed years
			$num_recs = 3;
            $timeSpan = '';


            if ( defined('WOK_PASSWORD') && WOK_PASSWORD != '') {

                $databaseID = "WOS";
                $editions = array();
                $wok_ws = new WokService(FALSE);
                $response = $wok_ws->search($databaseID, $query, $editions, $timeSpan, $depth, "en", $num_recs);
                if (is_soap_fault($response)) {
                  return '<span style="color:#fff;" id="ctWos">- ERROR</span>
                    <ol><li>Error: '.$response->getMessage().'</li></ol>';
                }
                $records = new DomDocument();
                $records->loadXML($response->return->records);
                $recordsNodes = $records->getElementsByTagName('REC');
                if($records) {
                    foreach($recordsNodes as $record) {
                        $xmlRecords = new WosRecItem();
                        $xmlRecords->load($record);
                        $matches[] = $xmlRecords->returnDataEnterForm();

                    }
                }
            }
    	}

    	$matchCount = count($matches);

    	if($matchCount > 0) {
        $tpl = new Template_API();
        $tpl->setTemplate("workflow/edit_metadata_helpers/matching_records_results.tpl.html");
              $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
              $tpl->assign("app_link_prefix", $app_link_prefix);
        $tpl->assign('matches', $matches);
        $tpl->assign('rel_url', APP_RELATIVE_URL);
        $tpl->assign('found', $matchCount);
        $tpl->assign('max_results', $num_recs);
        $tpl->assign('num_wos', $matchCount);
        $tpl->assign('dupe_records', 0);

        return $tpl->getTemplateContents();
    	} else {
    		return '';
    	}
    }

    /**
     * Retrieve Scopus records based on title
     * @param string $title
     * @return string
     */
    public function search_scopus($title)
    {
    $log = FezLog::get();
		$db = DB_API::get();
		$num_recs = 5;
		$matches = array();

    // Test it out on  http://www.scopus.com/search/form.url?display=advanced

		//Grab 40 records to work with in case some of them are not really UQ's
//		$query = array('query' => "affil(University+of+Queensland)+subtype(ar,cp,bk)+title('"
		$query = array('query' => "(doctype(ar)+OR+doctype(cp)+OR+doctype(bk)+OR+doctype(ch)+OR+(doctype(re)+AND+srctype(j)))+title(\""
		    . urlencode(trim($title)) . "\")",
		             'count' => $num_recs,
		             'start' => 0,
		             'view' => 'STANDARD',
		);

		$scopusService = new ScopusService(APP_SCOPUS_API_KEY);
		$xml = $scopusService->getNextRecordSet($query);

    // check for errors in the api, if found return that to the gui

    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->loadXML($xml);

    $xpath = new DOMXPath($xmlDoc);



    if ($xpath->query("/service-error")->length > 0) {

      $statusCode = $xpath->query("//statusCode")->item(0)->nodeValue;
      $statusText = $xpath->query("//statusText")->item(0)->nodeValue;
      return '<span style="color:#fff;" id="ctScopus">- ERROR</span>
        <ol><li>Error: '.$statusCode.'<br />'.$statusText.'</li></ol>';
    }



    $doc = new DOMDocument();
		$doc->loadXML($xml);

		$records = $doc->getElementsByTagName('identifier');


		$nodeItem = 0;

		while(($nodeItem < $records->length) && (count($matches) < $num_recs)) {
		    $record = $records->item($nodeItem);

        $recordXML = $this->stripOtherChildren($xml, '//atom:feed/atom:entry', $nodeItem);

		    $csr = new ScopusRecItem();

        $subtype = $doc->getElementsByTagName('subtype')->item($nodeItem)->nodeValue;
        $csr->_scopusDocTypeCode = $subtype;
		    $scopusId = $record->nodeValue;

		    $pregMatches = array();
		    preg_match("/^SCOPUS_ID\:(\d+)$/", $scopusId, $pregMatches);
		    $scopusIdExtracted = (array_key_exists(1, $pregMatches)) ? $pregMatches[1] : null;


//		    $iscop = new ScopusService(APP_SCOPUS_API_KEY);
//		    $rec = $iscop->getRecordByScopusId($scopusIdExtracted);
//        $rec = $record;

		    $csr->load($recordXML, null);
        $csr_fields = $csr->getFields();
		    //If the record has a UQ affiliation (ie, it's loaded), then continue
		    if($csr->isLoaded())
		    {
		        $fields = new stdClass();
            $fields->docTypeCode = $csr_fields['_scopusDocTypeCode'];
		        $fields->authors = $csr_fields['_authors'];
		        $fields->title = $csr_fields['_title'];
		        $fields->sourceTitle = $csr_fields['_journalTitle'];
		        $fields->volume_number = $csr_fields['_issueVolume'];
		        $fields->issue_number = $csr_fields['_issueNumber'];
		        $fields->page_start = $csr_fields['_startPage'];
		        $fields->page_end = $csr_fields['_endPage'];
		        $fields->dateIssued = $csr_fields['_issueDate'];
		        $fields->scopusId = $scopusIdExtracted;
		        $fields->pid = null;

            $csr->setLikenAction(false);
            $likenResults = $csr->liken();

		        if($likenResults[0] == 'ST07') {
		            $fields->record_exists = 0;
		        } else {
		            $fields->record_exists = 1;
                $fields->likenStatus = $likenResults[0];
                $fields->likenMessage = preg_replace('/('.APP_PID_NAMESPACE.':[0-9]*)/', '<a href="'.APP_RELATIVE_URL.'view/$1">$1</a>', $likenResults[1]);
		        }

		        $matches[] = $fields;
		    }

		    $nodeItem++;
		}

		$matchCount = count($matches);

		if($matchCount > 0) {
		    $tpl = new Template_API();
		    $tpl->setTemplate("workflow/edit_metadata_helpers/matching_records_results.tpl.html");
            $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
            $tpl->assign("app_link_prefix", $app_link_prefix);
		    $tpl->assign('scopusMatches', $matches);
		    $tpl->assign('rel_url', APP_RELATIVE_URL);
		    $tpl->assign('num_scopus', $matchCount);

		    $tplCont = $tpl->getTemplateContents();

		    return $tplCont;
		} else {
      return '';
    }

  }


  /**
   * Return the XML with only the 1 item node of the type of child specified in $keepNumber
   *
   * @param  string $title The title to search on
   * @param  string $childQuery The element tag name to find the set of children
   * @param  integer $keepNumber The number of the child to keep
   * @return string $xml The stripped xml
   */
    public function stripOtherChildren($xml, $childQuery, $keepNumber) {
      $nodeItem = 0;

      $doc = new DOMDocument();
      $doc->loadXML($xml);
      $_namespaces = array(
        'atom', "http://www.w3.org/2005/Atom",
        'd' => 'http://www.elsevier.com/xml/svapi/abstract/dtd',
        'ce' => 'http://www.elsevier.com/xml/ani/common',
        'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
        'dc' => "http://purl.org/dc/elements/1.1/",
        'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
      );

      $xpath = new DOMXPath($doc);
      foreach ($_namespaces as $name => $uri) {
        $xpath->registerNamespace($name, $uri);
      }
      $fieldNodeList = $xpath->query($childQuery);

      foreach ($fieldNodeList as $fieldNode) { // first delete all the isMemberOfs
        $parentNode = $fieldNode->parentNode;
        if ($nodeItem != $keepNumber) {
          $parentNode->removeChild($fieldNode);
        }
        $nodeItem++;
      }
      $newXML = $doc->saveXML();
      return $newXML;
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
            $wok_ws = new WokService(FALSE);
            $records = $wok_ws->retrieveById($ut);

			if($records) {
                // Param $record should only contain one publication.
                // However we set $pid as array just in case, and implode the pid as string on the return.
                $pid = array();
                $doc = new DOMDocument();
                $doc->loadXML($records);
                $xmlRecords = $doc->getElementsByTagName('REC');

                foreach ($xmlRecords as $_record){
                    // Instantiate WosRecItem object with our DOMElement $_record
                    $rec = new WosRecItem($_record);

                    // Get collections
                    $wos_collection = trim(APP_WOS_COLLECTIONS, "'");
                    if (!defined('APP_WOS_COLLECTIONS') || trim(APP_WOS_COLLECTIONS) == "") {
                        $rec->collections = array(RID_DL_COLLECTION);
                    } else {
//                        if ($aut_ids) {
//                            $rec->collections = array(RID_DL_COLLECTION);
//                        } else {
                            $rec->collections = array($wos_collection);
//                        }
                    }

                    $history = "Imported from WoS";

                    // Save WosRecItem
                    $pid[] = $rec->save($history);
                }

                $pid = implode(",", $pid);
			}
		}

        //We will auto link the Author to the record if possible
        $username = Auth::getUsername();
        $isUPO = User::isUserUPO($username);
        $isAdministrator = User::isUserAdministrator($username);
        $actingUser = Auth::getActingUsername();

        //We won't automatch admins and upos if they are not acting as someone
        if (!(($isAdministrator || $isUPO) && $actingUser == $username)) {
            Author::matchToPid($pid);
        }
        return $pid;
    }

    /**
     * Add a record found in Scopus to the system
     * @param string $scopusId
     * @return string
     */
    public function addScopusRec($scopusId)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $pid = false;

        // first query the main service to get the doc type (only place to get it)
//        $query = array('query' => "(scopus-id(".$scopusId."))",
//          'count' => 1,
//          'start' => 0,
//          'view' => 'STANDARD'
//        );

        $scopusService = new ScopusService(APP_SCOPUS_API_KEY);
//        $xml = $scopusService->getNextRecordSet($query);
//
//        $doc = new DOMDocument();
//        $doc->loadXML($xml);



        $record = $scopusService->getRecordByScopusId($scopusId);

        $sri = new ScopusRecItem();
        $sri->load($record);

        if($sri->isLoaded())
        {
            $history = "Imported from Scopus";
            $pid = $sri->save($history, APP_SCOPUS_IMPORT_COLLECTION);
        }

        //We will auto link the Author to the record if possible
        $username = Auth::getUsername();
        $isUPO = User::isUserUPO($username);
        $isAdministrator = User::isUserAdministrator($username);
        $actingUser = Auth::getActingUsername();

        //We won't automatch admins and upos if they are not acting as someone
        if (!(($isAdministrator || $isUPO) && $actingUser == $username)) {
            Author::matchToPid($pid);
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

        //Escape " double quote from user entered query, as we are using double quote to wrap the query string sent to SOAP
        //Also remove some special characters http://images.webofknowledge.com/WOKRS56B5/help/WOK/hs_wildcards.html
        $search = array("\"", "?", "*");
        $replace = array("", "", " ");
        $userQuery = str_replace($search, $replace, $userQuery);

        return $userQuery;
    }
}