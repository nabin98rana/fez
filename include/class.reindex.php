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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
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
    /**
     * Method used to get the list of PIDs in the Fez index.
     *
     * @access  public
     * @return  array The list of pids in the Fez index.
     */
    function getIndexPIDList($current_row = 0, $max = null)
    {
        if (!empty($max)) {
            if ($max == "ALL") {
                $max = 9999999;
            }
            $start = $current_row * $max;	
        }
        $stmt = "SELECT
                    distinct rmf_rec_pid
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field ";
        if (!empty($max)) {
            $stmt .= " LIMIT $start, $max";
        }
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of PIDs in Fedora that are not in the Fez index.
     *
     * @access  public
     * @return  array The list.
     */
    function getMissingList($current_row = 0, $max = 5)
    {
        $start = $current_row * $max;
		$fezIndexPIDs = Reindex::getIndexPIDList();
		$details = Fedora_API::getListObjectsXML("");
        //$details = Fedora_API::callFindObjects(array('pid', 'title', 'description'), 2000000);
        //Error_Handler::logError(print_r($details, true),__FILE__,__LINE__);
		$return = array();
		//foreach ($details["resultList"] as $detail) {
        foreach ($details as $detail) {
			if (!in_array($detail['pid'], $fezIndexPIDs)) {
				array_push($return, $detail);
			}
		}

		$total_rows = count($return);		
		$return = Misc::limitListResults($return, $start, ($start + $max)); 
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
                "current_page"  => $current_row,
                "start_offset"  => $start,
                "end_offset"    => $start + ($total_rows_limit),
                "total_rows"    => $total_rows,
                "total_pages"   => $total_pages,
                "previous_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                "last_page"     => $last_page
            )
        );	
	}

    function getFullList($current_row, $max)
    {
        $fezIndexPIDs = Reindex::getIndexPIDList();
        $details = Fedora_API::callFindObjects(array('pid', 'title', 'description'), 2000000);
        $return = array();
        foreach ($details['resultList'] as $detail) {
            if (in_array($detail['pid'], $fezIndexPIDs)) {
                array_push($return, $detail);
            }
        }
        $start = $current_row * $max;
        $total_rows = count($return);       
        $return = Misc::limitListResults($return, $start, ($start + $max));     
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
                "current_page"  => $current_row,
                "start_offset"  => $start,
                "end_offset"    => $start + ($total_rows_limit),
                "total_rows"    => $total_rows,
                "total_pages"   => $total_pages,
                "previous_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                "last_page"     => $last_page
            )
        );  
    }
    
    function getFedoraObjects($current_row, $max) 
    {
        $start = $current_row * $max;
    	$result = Fedora_API::callFindObjects(array('pid', 'title', 'description'), $max);
        for ($ii = 0; $ii < $current_row; $ii++) {
        	$result = Fedora_API::callResumeFindObjects($result['listSession']['token']);
        }
        $total_rows = $result['listSession']['completeListSize'];
        if (empty($total_rows)) {
        	if (count($result['resultList']) < $max) {
                $total_rows = $start + count($result['resultList']);
        	} else {
                $total_rows = $start + $max + 1;
            }
        }
        //Error_Handler::logError($result);
        if (($start + $max) < $total_rows) {
            $total_rows_limit = $start + $max;
        } else {
           $total_rows_limit = $total_rows;
        }
        $total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
        return array(
        "list" => $result['resultList'],
            "info" => array(
                "current_page"  => $current_row,
                "start_offset"  => $start,
                "end_offset"    => $start + ($total_rows_limit),
                "total_rows"    => $total_rows,
                "total_pages"   => $total_pages,
                "previous_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                "last_page"     => $last_page
                )
                );
       
    }

    
    /**
     * Method used to reindex a batch of pids in fedora into Fez that appear to already by Fez objects
	 * eg 1. They have already got a RELS-EXT that points to an existing Fez collection
	 *    2. They have a FezMD datastream
     *
     * @access  public
     * @return  boolean
     */
    function indexFezFedoraObjects()
    {
        global $HTTP_POST_VARS;

        $items = @$HTTP_POST_VARS["items"];
		$xdis_id = @$HTTP_POST_VARS["xdis_id"];
		$sta_id = @$HTTP_POST_VARS["sta_id"];		
		$community_pid = @$HTTP_POST_VARS["community_pid"];
		$collection_pid = @$HTTP_POST_VARS["collection_pid"];

		foreach ($items as $pid) {
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
			}
            // need to rebuild presmd and image datastreams
            // get list of datastreams and iterate over them
            $ds = Fedora_API::callGetDatastreams($pid);
            foreach ($ds as $dsKey => $dsTitle) {
                $dsIDName = $dsTitle['ID'];
                if ($dsTitle['controlGroup'] == "M" 
                    && !Misc::hasPrefix($dsIDName, 'preview_')
                    && !Misc::hasPrefix($dsIDName, 'web_')
                    && !Misc::hasPrefix($dsIDName, 'thumbnail_')
                    ) {
                    $new_dsID = Foxml::makeNCName($dsIDName);
                    // get the datastream into a file where we can do stuff to it
                    $urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsIDName; 
                    $urlReturn = Misc::ProcessURL($urldata);
                    $handle = fopen(APP_TEMP_DIR.$new_dsID, "w");
                    fwrite($handle, $urlReturn[0]);
                    fclose($handle);
                    // delete and re-ingest - need to do this because sometimes the object made it
                    // into the repository even though its dsID is illegal.
                    Fedora_API::callPurgeDatastream($pid, $dsIDName);
                    Fedora_API::getUploadLocationByLocalRef($pid, $new_dsID, APP_TEMP_DIR.$new_dsID, $new_dsID, 
                        $dsTitle['MIMEType'], "M");
                    Record::generatePresmd($pid, $new_dsID);
                    Workflow::processIngestTrigger($pid, $new_dsID, $dsTitle['MIMEType']);
                    if (is_file(APP_TEMP_DIR.$dsIDName)) {
                        unlink(APP_TEMP_DIR.$dsIDName);
                    }
                }
            }
			Record::setIndexMatchingFields($pid);
		}
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true; 
        }
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
