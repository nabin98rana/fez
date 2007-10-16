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
include_once(APP_INC_PATH . "class.fedora_direct_access.php");

/**
  * Reindex
  */
class Reindex
{
	const INDEX_TYPE_FEDORAINDEX = 1;
	const INDEX_TYPE_REINDEX = 2;
	const INDEX_TYPE_REINDEX_OBJECTS = 3;  // index specific pids
	const INDEX_TYPE_UNDELETE = 4;  


    var $fedoraObjects = array();
    var $terms;
    var $listSession;
    var $resume = false;
    var $bgp;
    
    
    function inIndex($pid)
    {
        $stmt = "SELECT
                   rek_pid
                 FROM
                    " . APP_TABLE_PREFIX . "record_search_key  where rek_pid='".$pid."' ";
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
     * Method used for retrieving core list details for a nominated PID.
     *
     * @access  public
     * @return  array The details.
     */
    function getFedoraObjectListDetails($pid)
    {
        $res = Fedora_API::callFindObjects(array('pid', 'title', 'description'), 1, $pid);
        // Probably should add some error checking here, but for now we only plan to call 
        // this function for PIDs that we are certain exist in the system.
        return @array_shift($res['resultList']['objectFields']);
    }

    /**
     * Method used for retrieving list of all PIDs in Fedora.
     *
     * @access  public
     * @return  array The PIDs.
     *
     * Note: This turns out to be prohibitively slow. We have phased this out in favour of
     * direct access to the Fedora database. See class.fedora_direct_access.php.
     */
    function getAllFedoraPIDs()
    {
        $resumeToken = "~";     // Initialisation to trigger first pass
        $fedoraPIDs = array();
        do {
            if ($resumeToken == "~") {
                $res = Fedora_API::callFindObjects(array('pid'), 100, "*");     // First time.
            } else {
                $res = Fedora_API::callResumeFindObjects($resumeToken);         // Each subsequent time.
            }
            $fedoraObjects = @$res['resultList']['objectFields'];
            $resumeToken = @$res['listSession']['token'];
            if (sizeof($fedoraObjects) > 1) {
                foreach ($fedoraObjects as $thing) {
                    array_push($fedoraPIDs, $thing['pid']);
                }
            }
        } while ($resumeToken !== null);

        //sort($fedoraPIDs);      // Probably more appropriate to sort later, if at all.
        return $fedoraPIDs;
    }

    /**
     * Method used to get the list of PIDs in Fedora that are not in the Fez index.
     *
     * @access  public
     * @return  array The list.
     */
    function getMissingList($page = 0, $max=10, $terms="*", $state = 'A')
    {
		$start = $max * $page;
		$return = array();

        // Direct Access to Fedora
        $fedoraDirect = new Fedora_Direct_Access();
        $fedoraList = $fedoraDirect->fetchAllFedoraPIDs($terms, $state);

        // Correct for Oracle-produced array key case issue reported by Kostas
		foreach ($fedoraList as $fkey => $flist) {
            $fedoraList[$fkey] = array_change_key_case($flist, CASE_LOWER);
        }

        // Extract just the PIDs
        $fedoraPIDs = array();
		foreach ($fedoraList as $flist) {
        	array_push($fedoraPIDs, $flist['pid']);
        }

        //$PIDsInFedora = Reindex::getAllFedoraPIDs(); // Old way.
        $PIDsInFez = Reindex::getPIDlist();
        $newPIDs = array_values(array_diff($fedoraPIDs, $PIDsInFez));

		if (!is_numeric($max)) {
			if ($max == "ALL") {
				return array("list" => $newPIDs);
			}
		}
        // Chop it down until we just have the required number.
        $newPIDsReduced = array();
        $listCounter = 0;
        for ($ii = 0; count($newPIDsReduced) < $max && count($newPIDsReduced) <= count($newPIDs) - 1 && $listCounter < count($newPIDs); $ii++) {
            if ($listCounter >= $start) {
                if ($newPIDs[$ii] !== null) {
                    array_push($newPIDsReduced, $newPIDs[$ii]);
                }
            }
            $listCounter++;
        }

        foreach ($newPIDsReduced as $pid) {
        	foreach ($fedoraList as &$flist) {
        		if ($flist['pid'] == $pid) {
        			array_push($return, $flist); 
        		}
            	//array_push($return, Reindex::getFedoraObjectListDetails($pid));     // Extract details ONLY for the records we're listing. // old way of doing it with fedora api - doesn't scale well
        	}
        }
		$total_rows = sizeof($newPIDs);
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
                "prev_page" => ($page == 0) ? "-1" : ($page - 1),
                "next_page"     => ($page == $last_page) ? "-1" : ($page + 1),
                "last_page"     => $last_page
            )
        );
	}

    function getFullList($page = 0, $max=10, $terms="*")
    {
		$start = $max * $page;
		$return = array();

        // Direct Access to Fedora
        $fedoraDirect = new Fedora_Direct_Access();
        $fedoraList = $fedoraDirect->fetchAllFedoraPIDs($terms);

        // Correct for Oracle-produced array key case issue reported by Kostas
		foreach ($fedoraList as &$flist) {
            $flist = array_change_key_case($flist, CASE_LOWER);
        }

        // Extract just the PIDs
        $fedoraPIDs = array();
		foreach ($fedoraList as &$flist) {
        	array_push($fedoraPIDs, $flist['pid']);
        }

        //$PIDsInFedora = Reindex::getAllFedoraPIDs(); // Old way.
        $PIDsInFez = Reindex::getPIDlist();
        $newPIDs = array_values(array_intersect($fedoraPIDs, $PIDsInFez));

        // Chop it down until we just have the required number.
        $newPIDsReduced = array();
        $listCounter = 0;
        for ($ii = 0; count($newPIDsReduced) < $max && count($newPIDsReduced) <= count($newPIDs) - 1 && $listCounter < count($newPIDs); $ii++) {
            if ($listCounter >= $start) {
                if ($newPIDs[$ii] !== null) {
                    array_push($newPIDsReduced, $newPIDs[$ii]);
                }
            }
            $listCounter++;
        }

        foreach ($newPIDsReduced as $pid) {
        	foreach ($fedoraList as &$flist) {
        		if ($flist['pid'] == $pid) {
        			array_push($return, $flist); 
        		}
            	//array_push($return, Reindex::getFedoraObjectListDetails($pid));     // Extract details ONLY for the records we're listing. // old way of doing it with fedora api - doesn't scale well
        	}
        }
		$total_rows = sizeof($newPIDs);
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
                "prev_page" => ($page == 0) ? "-1" : ($page - 1),
                "next_page"     => ($page == $last_page) ? "-1" : ($page + 1),
                "last_page"     => $last_page
            )
        );
	}
	

/*    function getFullList($page, $max, $terms)
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
                "prev_page" => ($page == 0) ? "-1" : ($page - 1),
                "next_page"     => ($page == $last_page) ? "-1" : ($page + 1),
                "last_page"     => $last_page
            )
        );  
    }
*/

	function getDeletedList($page = 0, $max=10, $terms="*")
	{
		return $this->getMissingList($page, $max, $terms, 'D');
	}


    /**
     * Method used to return a complete list of PIDs in the Fez index.
     *
     * Developer note: CK has advised that this will need to be modified when the index table
     * restructure occurs. #PREFIX#_record_search_key is the table against which this piece of
     * SQL will need to be run. But for now, it doesn't exist, so ....
     */
    function getPIDlist()
    {
        $stmt = "SELECT rek_pid
                 FROM " . APP_TABLE_PREFIX . "record_search_key";
		$res = $GLOBALS["db_api"]->dbh->getCol($stmt);
	    if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);			
			return -1;
		}
		return $res;
//        $fezPIDs = array();      // Array for storing the processed results.
        // Step through the results.
/*        foreach ($res as $PIDarray) {
            foreach ($PIDarray as $PIDarrayVal) {
                array_push($fezPIDs, $PIDarrayVal);
            }            
        }*/
//        return $fezPIDs;
    }

    // This probably wants a re-write at some point too. But at least now we can trigger it ...
    function reindexMissingList($params, $terms)
    {
        $this->terms = $terms;
        $ii = 0;

        $reindex_record_counter = 0;
//		$list = Reindex::getMissingList(0,"ALL","*");
        // Direct Access to Fedora
        $fedoraDirect = new Fedora_Direct_Access();
        // are we doing an undelete?
        if (@$params['index_type'] == Reindex::INDEX_TYPE_UNDELETE) {
	        $fedoraList = $fedoraDirect->fetchAllFedoraPIDs($terms, 'D');
        } else {
	        $fedoraList = $fedoraDirect->fetchAllFedoraPIDs($terms);
        }

        
		$record_count = count($fedoraList);
        $bgp_details = $this->bgp->getDetails();
        // Correct for Oracle-produced array key case issue reported by Kostas
		if (APP_DB_TYPE == "oracle") {
			foreach ($fedoraList as &$flist) {
	            $flist = array_change_key_case($flist, CASE_LOWER);
	        }
			$fedoraList = $flist;
		} 
//		print_r($list); exit;
//		$list_detail = $list['list'];
		foreach ($fedoraList as $detail) {
            $reindex_record_counter++;
            $utc_date = Date_API::getSimpleDateUTC();
            $time_per_object = Date_API::dateDiff("s", $bgp_details['bgp_started'], $utc_date);
            $date_new = new Date(strtotime($bgp_details['bgp_started']));
            $time_per_object = round(($time_per_object / $reindex_record_counter), 2);
            //$expected_finish = Date_API::getFormattedDate($date_new->getTime());
            $date_new->addSeconds($time_per_object*$record_count);
            $tz = Date_API::getPreferredTimezone($bgp_details["bgp_usr_id"]);
			$res[$key]["bgp_started"] = Date_API::getFormattedDate($res[$key]["bgp_started"], $tz);
            $expected_finish = Date_API::getFormattedDate($date_new->getTime(), $tz);

	        if (!empty($this->bgp)) {
                $this->bgp->setProgress(intval(100*$reindex_record_counter/$record_count));
//	            $this->bgp->setProgress(++$ii);
	        }
	        if (!Reindex::inIndex($detail['pid'])) {
	            if (!empty($this->bgp)) {
//	                $this->bgp->setStatus("Adding: '".$detail['pid']."' '".$detail['title']."'");
                    $this->bgp->setStatus("Adding:  '".$detail['pid']."' ".$detail['title']. " (".$reindex_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");    	
	            }
	            $params['items'] = array($detail['pid']);
	            Reindex::indexFezFedoraObjects($params);                
	        } else {
	            if (!empty($this->bgp)) {
                    $this->bgp->setStatus("Skipping Because Already in Fez Index:  '".$detail['pid']."' ".$detail['title']. " (".$reindex_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");
//	                $this->bgp->setStatus("Skipping Because Already in Fez Index: '".$detail['pid']."' '".$detail['title']."'");
	            }
	        }
		}		
		
/*        for ($detail = $this->getNextFedoraObject(); !empty($detail); $detail = $this->getNextFedoraObject()) {
            if (!empty($this->bgp)) {
                $this->bgp->setProgress(++$ii);
            }
            if (!Reindex::inIndex($detail['pid'])) {
                if (!empty($this->bgp)) {
                    $this->bgp->setStatus("Adding: '".$detail['pid']."' '".$detail['title']."'");
                }
                $params['items'] = array($detail['pid']);
                Reindex::indexFezFedoraObjects($params);                
            } else {
                if (!empty($this->bgp)) {
                    $this->bgp->setStatus("Skipping: '".$detail['pid']."' '".$detail['title']."'");
                }
            }
        } */
    }

    function getIndexPIDCount() {
        $stmt = "select count(rek_pid) as pid_count from  " . APP_TABLE_PREFIX . "record_search_key";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }



    function reindexFullList($params,$terms)
    {
        $this->terms = $terms;
        $ii = 0;
        $reindex_record_counter = 0;
//        $record_count = Reindex::getIndexPIDCount();
        $bgp_details = $this->bgp->getDetails();
//		$list = Reindex::getFullList(0,9999999999,"*");
        $fedoraDirect = new Fedora_Direct_Access();
        $fedoraList = $fedoraDirect->fetchAllFedoraPIDs($terms, ""); //get all pids including deleted ones so we can remove them from the fez index if necessary
		if (APP_DB_TYPE == "oracle") {
			foreach ($fedoraList as &$flist) {
	            $flist = array_change_key_case($flist, CASE_LOWER);
	        }
			$fedoraList = $flist;
		} 
        $record_count = count($fedoraList);
//		$list_detail = $list['list'];
		//foreach ($list['list'] as $detail) {
		foreach ($fedoraList as $detail) {
            if ($detail['objectstate'] != 'A') { //check if in the index and delete if in there
            	if (Reindex::inIndex($detail['pid']) == true) {
                    $this->bgp->setStatus("Removing from Index Because Fedora State not 'A':  '".$detail['pid']."' ".$detail['title']. " (".$reindex_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");
					Record::removeIndexRecord($detail['pid']);
					continue;
				} else {
                   	$this->bgp->setStatus("Skipping Removing Non-A Fedora State object Because already not in Fez Index:  '".$detail['pid']."' ".$detail['title']. " (".$reindex_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");
            		continue;
				}
            } 
			$ii++;
            if (!empty($this->bgp)) {
                $this->bgp->setProgress($ii);
            }
            $reindex_record_counter++;
            $utc_date = Date_API::getSimpleDateUTC();
            $time_per_object = Date_API::dateDiff("s", $bgp_details['bgp_started'], $utc_date);
            $date_new = new Date(strtotime($bgp_details['bgp_started']));
            $time_per_object = round(($time_per_object / $reindex_record_counter), 2);
            //$expected_finish = Date_API::getFormattedDate($date_new->getTime());
            $date_new->addSeconds($time_per_object*$record_count);
            $tz = Date_API::getPreferredTimezone($bgp_details["bgp_usr_id"]);
			$res[$key]["bgp_started"] = Date_API::getFormattedDate($res[$key]["bgp_started"], $tz);
            $expected_finish = Date_API::getFormattedDate($date_new->getTime(), $tz);
            if (Reindex::inIndex($detail['pid']) == true) {
                if (!empty($this->bgp)) {
                    $this->bgp->setProgress(intval(100*$reindex_record_counter/$record_count));
                    $this->bgp->setStatus("Reindexing:  '".$detail['pid']."' ".$detail['title']. " (".$reindex_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");
                 //   $this->bgp->setStatus("Reindexing: '".$detail['pid']."' '".$detail['title']."'");
                }
                $params['items'] = array($detail['pid']);
                Reindex::indexFezFedoraObjects($params);
            } else {
                if (!empty($this->bgp)) {
                    $this->bgp->setProgress(intval(100*$reindex_record_counter/$record_count));
//                    $this->bgp->setStatus("Skipping: '".$detail['pid']."'  '".$detail['title']."'");
                    $this->bgp->setStatus("Skipping Because not in Fez Index:  '".$detail['pid']."' ".$detail['title']. " (".$reindex_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");
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
        $index_type = @$params["index_type"];
		

		foreach ($items as $pid) {
	        if ($index_type == Reindex::INDEX_TYPE_UNDELETE) {
				Record::markAsActive($pid, false);
				History::addHistory($pid, null, "", "", true, 'Undeleted');
			}
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
			}
            if ($rebuild_this == true) {
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
                        $handle = fopen(APP_TEMP_DIR.$new_dsID, "w");                     
                        $urlReturn = Misc::ProcessURL($urldata, false, $handle);                        
                        //fwrite($handle, $urlReturn[0]);
                        fclose($handle);                        
                        if ($new_dsID != $dsIDName) {
                            Error_Handler::logError($pid.": ".$dsIDName.": need to repair dsID");
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
			Record::setIndexMatchingFields($pid, '', $rebuild_this);
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
