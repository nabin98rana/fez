<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class designed to handle all business logic related to the re-indexing of records in the
 * system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");


/**
  * Reindex
  */
class Reindex
{
    var $fedoraObjects = array();
    var $terms;
    var $listSession;
    var $resume = false;
    var $bgp;
    
    
    function inIndex($pid)
    {
        $stmt = "SELECT
                   rmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field  where rmf_rec_pid='$pid' ";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return (count($res) > 0) ? true : false;
        }
    }

    function getNextFedoraObject()
    {
        if (empty($this->fedoraObjects)) {
            if (!$this->resume) {
                $res = Fedora_API::callFindObjects(array('pid', 'title', 'description'), 5, $this->terms);
                $this->resume = true;
            } else {
                if (!empty($this->listSession['token'])) {
                    $res = Fedora_API::callResumeFindObjects($this->listSession['token']);
                } else {
                    $res = array();
                }
            }
            //Error_Handler::logError(print_r($res, true));
			//print_r($res['resultList']);
            $this->listSession = @$res['listSession'];
            $this->fedoraObjects = @$res['resultList']['objectFields']; // 2.2 and up!
        }
        return @array_shift($this->fedoraObjects);
    }

    /**
     * Method used to get the list of PIDs in Fedora that are not in the Fez index.
     *
     * @access  public
     * @return  array The list.
     */
    function getMissingList($page = 0, $max=10, $terms)
    {
        $this->terms = $terms;
		$start = $max * $page;
		$return = array();
		$detail = $this->getNextFedoraObject();
        for ($ii = 0; !empty($detail) && count($return) < $max ; $detail = $this->getNextFedoraObject()) {
			if (!Reindex::inIndex($detail['pid'])) {
				if (++$ii > $start) {
                    array_push($return, $detail);
                }
			}
		}

		$total_rows = count($return);		
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
        return array(
            "list" => $return,
            "info" => array(
                "current_page"  => $page,
                "start_offset"  => $start,
                "end_offset"    => $start + ($total_rows_limit),
                "total_rows"    => $total_rows,
                "total_pages"   => $total_pages,
                "previous_page" => ($page == 0) ? "-1" : ($page - 1),
                "next_page"     => ($page == $last_page) ? "-1" : ($page + 1),
                "last_page"     => $last_page
            )
        );	
	}

    function getFullList($page, $max, $terms)
    {
        $start = $page * $max;
        $this->terms = $terms;
        $start = $max * $page;
        $return = array();
        $detail = $this->getNextFedoraObject();
        
        for ($ii = 0; !empty($detail) && count($return) < $max ; $detail = $this->getNextFedoraObject()) {
            if (Reindex::inIndex($detail['pid'])) {
                if (++$ii > $start) {
                    array_push($return, $detail);
                }
            }
        }
        if (count($return) < $max) {
            $total_rows = $start + count($return);
        } else {
            $total_rows = $start+$max+1;
        }
        if (($start + $max) < $total_rows) {
            $total_rows_limit = $start + $max;
        } else {
           $total_rows_limit = $total_rows;
        }
        $total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
        return array(
            "list" => $return,
            "info" => array(
                "current_page"  => $page,
                "start_offset"  => $start,
                "end_offset"    => $start + ($total_rows_limit),
                "total_rows"    => $total_rows,
                "total_pages"   => $total_pages,
                "previous_page" => ($page == 0) ? "-1" : ($page - 1),
                "next_page"     => ($page == $last_page) ? "-1" : ($page + 1),
                "last_page"     => $last_page
            )
        );  
    }

    function reindexMissingList($params,$terms)
    {
        $this->terms = $terms;
        $ii = 0;
        for ($detail = $this->getNextFedoraObject(); !empty($detail); $detail = $this->getNextFedoraObject()) {
            if (!empty($this->bgp)) {
                $this->bgp->setProgress(++$ii);
            }
            if (!Reindex::inIndex($detail['pid'])) {
                if (!empty($this->bgp)) {
                    $this->bgp->setStatus("Adding: '{$detail['pid']}' '{$detail['title']}'");
                }
                $params['items'] = array($detail['pid']);
                Reindex::indexFezFedoraObjects($params);                
            } else {
                if (!empty($this->bgp)) {
                    $this->bgp->setStatus("Skipping: '{$detail['pid']}' '{$detail['title']}'");
                }
            }
        }
    }



    function getIndexPIDCount() {
        $stmt = "select count(distinct rmf_rec_pid) as pid_count from  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
//          return 10;
            return $res;
        }
    }


    function reindexFullList($params,$terms)
    {
        $this->terms = $terms;
        $ii = 0;
        $reindex_record_counter = 0;
        $record_count = Reindex::getIndexPIDCount();
        for ($detail = $this->getNextFedoraObject(); !empty($detail); $detail = $this->getNextFedoraObject()) {
            if (!empty($this->bgp)) {
                $this->bgp->setProgress(++$ii);
            }
            $reindex_record_counter++;

                    $bgp_details = $this->bgp->getDetails();
                    $utc_date = Date_API::getSimpleDateUTC();
                    $time_per_object = Date_API::dateDiff("s", $bgp_details['bgp_started'], $utc_date);
                    $date_new = new Date(strtotime($bgp_details['bgp_started']));
                    $time_per_object = intval($time_per_object / $reindex_record_counter);
                    //$expected_finish = Date_API::getFormattedDate($date_new->getTime());
                    $date_new->addSeconds($time_per_object*$record_count);
                    $tz = Date_API::getPreferredTimezone($bgp_details["bgp_usr_id"]);
    				$res[$key]["bgp_started"] = Date_API::getFormattedDate($res[$key]["bgp_started"], $tz);
                    $expected_finish = Date_API::getFormattedDate($date_new->getTime(), $tz);
            if (Reindex::inIndex($detail['pid'])) {
                if (!empty($this->bgp)) {
                    $this->bgp->setProgress(intval(100*$reindex_record_counter/$record_count)."%");
              $this->bgp->setStatus("Reindexing:  '{$detail['pid']}' ".$detail['title']. " (".$reindex_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");
                 //   $this->bgp->setStatus("Reindexing: '{$detail['pid']}' '{$detail['title']}'");
                }
                $params['items'] = array($detail['pid']);
                Reindex::indexFezFedoraObjects($params);                
            } else {
                if (!empty($this->bgp)) {
                    $this->bgp->setStatus("Skipping: '{$detail['pid']}'  '{$detail['title']}'");
                }
            }
        }
    }

    

    
    /**
     * Method used to reindex a batch of pids in fedora into Fez that appear to already be Fez objects
	 * eg 1. They have already got a RELS-EXT that points to an existing Fez collection
	 *    2. They have a FezMD datastream
     *
     * @access  public
     * @return  boolean
     */
    function indexFezFedoraObjects($params = array())
    {
        global $HTTP_POST_VARS;

        if (empty($params)) {
            $params = &$HTTP_POST_VARS;
        }
        $items = @$params["items"];
		$xdis_id = @$params["xdis_id"];
		$sta_id = @$params["sta_id"];		
		$community_pid = @$params["community_pid"];
		$collection_pid = @$params["collection_pid"];
        $rebuild = @$params["rebuild"];

		foreach ($items as $pid) {
    		$rebuild_this = $rebuild;
            // determine if the record is a Fez record
            if (!Fedora_API::datastreamExists($pid, 'FezMD')) {
                $relsext = Reindex::buildRELSEXT($collection_pid, $pid);
				$fezmd = Reindex::buildFezMD($xdis_id, $sta_id);			
				if (Fedora_API::datastreamExists($pid, "RELS-EXT")) {
					Fedora_API::callModifyDatastreamByValue($pid, "RELS-EXT", "A", "Relationships to other objects", $relsext, "text/xml", true);
				} else {
					Fedora_API::getUploadLocation($pid, "RELS-EXT", $relsext, "Relationships to other objects", "text/xml", "X");
				}
				if (Fedora_API::datastreamExists($pid, "FezMD")) {
					Fedora_API::callModifyDatastreamByValue($pid, "FezMD", "A", "Fez extension metadata", $fezmd, "text/xml", true);
				} else {
					Fedora_API::getUploadLocation($pid, "FezMD", $fezmd, "Fez extension metadata", "text/xml", "X");
				}
                $rebuild_this = true;  // always rebuild non-fez objects
                //Error_Handler::logError("$pid: Object was new to fez");   // Why are we logging an error? #1
			}
            if ($rebuild_this) {
                //Error_Handler::logError("$pid: rebuilding");   // Why are we logging an error? #1
                // need to rebuild presmd and image datastreams
                // get list of datastreams and iterate over them
                $ds = Fedora_API::callGetDatastreams($pid);
                // delete any fez derived datastreams that might be hanging around for no reason.  We'll 
                // recreate them later if they are still needed
                foreach ($ds as $dsKey => $dsTitle) {
                    $dsIDName = $dsTitle['ID'];
                    if ($dsTitle['controlGroup'] == "M" 
                        && (Misc::hasPrefix($dsIDName, 'preview_')
                            || Misc::hasPrefix($dsIDName, 'web_')
                            || Misc::hasPrefix($dsIDName, 'thumbnail_')
                            || Misc::hasPrefix($dsIDName, 'presmd_'))) {
                        Fedora_API::callPurgeDatastream($pid, $dsIDName);
                    } 
                }
                foreach ($ds as $dsKey => $dsTitle) {
                    $dsIDName = $dsTitle['ID'];
                    if ($dsTitle['controlGroup'] == "M" 
                        && !Misc::hasPrefix($dsIDName, 'preview_')
                        && !Misc::hasPrefix($dsIDName, 'web_')
                        && !Misc::hasPrefix($dsIDName, 'thumbnail_')
                        && !Misc::hasPrefix($dsIDName, 'presmd_') // since presmd is stored as a binary to avoid parsing by fedora at the moment.
                        ) {
                        $new_dsID = Foxml::makeNCName($dsIDName);
                        // get the datastream into a file where we can do stuff to it
                        $urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsIDName; 
                        $urlReturn = Misc::ProcessURL($urldata);
                        $handle = fopen(APP_TEMP_DIR.$new_dsID, "w");
                        fwrite($handle, $urlReturn[0]);
                        fclose($handle);
                        if ($new_dsID != $dsIDName) {
                            Error_Handler::logError("$pid: $dsIDName: need to repair dsID");
                            // delete and re-ingest - need to do this because sometimes the object made it
                            // into the repository even though its dsID is illegal.
                            Fedora_API::callPurgeDatastream($pid, $dsIDName);
                            Fedora_API::getUploadLocationByLocalRef($pid, $new_dsID, APP_TEMP_DIR.$new_dsID, $new_dsID, 
                                $dsTitle['MIMEType'], "M");
                        }
                        Record::generatePresmd($pid, $new_dsID);
                        Workflow::processIngestTrigger($pid, $new_dsID, $dsTitle['MIMEType']);
                        if (is_file(APP_TEMP_DIR.$dsIDName)) {
                            unlink(APP_TEMP_DIR.$dsIDName);
                        }
                    }
                }
            }
            Record::propagateExistingPremisDatastreamToFez($pid);
			Record::setIndexMatchingFields($pid);
		}
        return true;
    }

	function buildRELSEXT($parent_pid, $pid) {
		$xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
					  xmlns:rel="info:fedora/fedora-system:def/relations-external#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
					  <rdf:description rdf:about="info:fedora/'.$pid.'">
						<rel:isMemberOf rdf:resource="info:fedora/'.$parent_pid.'"/>
					  </rdf:description>
					</rdf:RDF>';
		return $xml;
	}
	
	function buildFezMD($xdis_id, $sta_id) {
		$created_date = Date_API::getFedoraFormattedDateUTC();
		$updated_date = $created_date;
		$ret_id = 3; // standard record type id

		$xml = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
			    	<xdis_id>'.$xdis_id.'</xdis_id>
					<sta_id>'.$sta_id.'</sta_id>
					<ret_id>'.$ret_id.'</ret_id>
					<created_date>'.$created_date.'</created_date>					  
					<updated_date>'.$updated_date.'</updated_date>
				</FezMD>';
		return $xml;
	}	
}
// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Reindex Class');
}

?>
