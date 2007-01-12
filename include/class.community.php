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
 * Class to handle the business logic related to the administration
 * of communities in the system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");

class Community
{
    /**
     * Method used to get the default community XDIS_ID
     *
     * Developer Note: Need to make this able to be set in Administrative interface and stored in the Fez database.
     *	 
     * @access  public
     * @return  integer $community_xdis_id The XSD Display ID of a Fez community
     */
    function getCommunityXDIS_ID()
    {
		// will make this more dynamic later. (probably feed from a mysql table which can be configured in the gui admin interface).
		$community_xdis_id = 11;
		return $community_xdis_id;
    }

    /**
     * Method used to get the XSD Display document types the community supports, from the Fez Index.
     *
     * @access  public
     * @param   string $community_pid The community persistant identifier	 	 
     * @return  array The list of collections display types that can be created
     */
	function getChildXDisplayOptions($collection_pid) {
	
		$stmt = "
		SELECT d3.xdis_id, d3.xdis_title
		FROM  
		  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3,
		  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3,
		  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d3,		  
		  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s3
		WHERE x3.xsdmf_sek_id = s3.sek_id AND s3.sek_title = 'XSD Display Option' AND x3.xsdmf_id = r3.rmf_xsdmf_id 
		  AND r3.rmf_rec_pid ='".$collection_pid."' AND r3.rmf_int = d3.xdis_id";

		$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
	}

    /**
     * Method used to get the basic details for a given community from the Fez Index.
     *
     * @access  public
     * @param   integer $community_pid The community persistent ID
     * @return  array $return The basic community details
     */
    function getDetails($community_pid)
    {
        $stmt = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
					
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on x1.xsdmf_id = r1.rmf_xsdmf_id
					and rmf_rec_pid = '".$community_pid."'
					inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s1 on s1.sek_id = x1.xsdmf_sek_id";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = array();

		foreach ($res as $result) {		
			if (is_numeric($result['sek_id'])) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$search_var = strtolower(str_replace(" ", "_", $result['sek_title']));
				if (@!is_array($return[$result['rmf_rec_pid']][$search_var])) {
					$return[$result['rmf_rec_pid']][$search_var] = array();
				}
				if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']][$search_var])) {
					array_push($return[$result['rmf_rec_pid']][$search_var], $result['rmf_'.$result['xsdmf_data_type']]);
					sort($return[$result['rmf_rec_pid']][$search_var]);
				}
			}
		}
		$return = array_values($return);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $return;
        }
    }

    /**
     * Method used to get the list of communities available in the 
     * system.
     *
     * @access  public
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @return  array The list of communities
     */
    function getList($current_row = 0, $max = 25, $order_by="Title")
    {
	
        $order_dir = 'ASC';
        $start = $current_row * $max;
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
        if (!$data_type) {
            $order_by = "Title";
            $sekdet = Search_Key::getDetailsByTitle($order_by);
            $data_type = $sekdet['xsdmf_data_type'];
        }
        $authStmts = Collection::getAuthIndexStmt(array("Lister","Viewer","Creator","Editor"));
		$authStmt = $authStmts['authStmt'];
        $joinStmt = $authStmts['joinStmt'];
        // body1 is the part of the query that selects which pids will be returned
        $body1 = "
            FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
            inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2  
			ON r2.rmf_xsdmf_id = x2.xsdmf_id AND match(x2.xsdmf_element) 
            against ('\"!ret_id\"' in boolean mode) and r2.rmf_int=1
            
            $authStmt

            INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS rmf
            ON rmf.rmf_rec_pid = r2.rmf_rec_pid AND rmf.rmf_int=2
            INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS xdm
            ON rmf.rmf_xsdmf_id = xdm.xsdmf_id AND xdm.xsdmf_element='!sta_id'


                    ";
         $stmt = "SELECT
            * 
            FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
            inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 
            ON r1.rmf_xsdmf_id = x1.xsdmf_id  
			inner join (
                select * from (
                    SELECT distinct r2.rmf_rec_pid, r4.rmf_rec_pid as sort_pid, r4.rmf_$data_type as sort_column 
                    $body1

                    inner JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r4
                    on rmf.rmf_rec_pid = r4.rmf_rec_pid  
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x4
                    on r4.rmf_xsdmf_id = x4.xsdmf_id 
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s4
                    on s4.sek_id = x4.xsdmf_sek_id AND s4.sek_title = '$order_by'
                    
                    union
                    SELECT distinct r2.rmf_rec_pid, r2.rmf_rec_pid as sort_pid, null as sort_column 
                    $body1
                    where not exists (
                        select * from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r4  
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x4 
                                on r4.rmf_xsdmf_id = x4.xsdmf_id
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s4 
                                on s4.sek_id = x4.xsdmf_sek_id aND s4.sek_title = '$order_by' 
                        where rmf.rmf_rec_pid = r4.rmf_rec_pid )
                ) as r2
                ORDER BY sort_column $order_dir, r2.rmf_rec_pid desc
                LIMIT $start, $max
            ) as d3 on d3.rmf_rec_pid = r1.rmf_rec_pid
            left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 
            on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
			left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key sk1 on sk1.sek_id = x1.xsdmf_sek_id
            order by d3.sort_column $order_dir, r1.rmf_rec_pid DESC 
            ";
        $countStmt = "SELECT count(distinct r2.rmf_rec_pid) $body1";
		$securityfields = Auth::getAllRoles();

        //Error_Handler::logError($stmt, __FILE__,__LINE__);
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$total_rows = $GLOBALS["db_api"]->dbh->getOne($countStmt);

		$return = array();
		$return = Collection::makeReturnList($res);
        $return = Collection::makeSecurityReturnList($return);

		$hidden_rows = count($return);
		$return = Auth::getIndexAuthorisationGroups($return);
		$return = Misc::cleanListResults($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		// add the available workflow trigger buttons
		$isAdministrator = Auth::isAdministrator();
/*		foreach ($return as $ret_key => $ret_wf) {
			$pid = $ret_wf['pid'];
			$record = new RecordObject($pid);
            $workflows = array();
			if ( ($ret_wf['isEditor'] == 1) 
                    || ($ret_wf['isCommunityAdministrator'] == 1) 
                    || $isAdministrator) {
				$xdis_id = $ret_wf['display_type'][0];
				$ret_id = $ret_wf['object_type'][0];
				$strict = false;
				$workflows = array_merge($record->getWorkflowsByTriggerAndRET_ID('Update', $ret_id, $strict),
                        $record->getWorkflowsByTriggerAndRET_ID('Export', $ret_id, $strict));
			}
			// check which workflows can be triggered			
			$workflows1 = array();
			if (is_array($workflows)) {
				foreach ($workflows as $trigger) {
                    if (WorkflowTrigger::showInList($trigger['wft_options']) 
                            && Workflow::canTrigger($trigger['wft_wfl_id'], $pid)) {
						$workflows1[] = $trigger;
					}
				}
				$workflows = $workflows1;				
			}
			$return[$ret_key]['workflows'] = $workflows;
		}*/
		$return = Collection::getWorkflows($return);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return array(
                "list" => $return,
                "info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $total_rows_limit,
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "previous_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page,
                    "hidden_rows"     => $hidden_rows - $total_rows
                )
            );
        }
    }

    /**
     * Method used to get an associative array of community ID and title
     * of all communities available in the system.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  array The list of collections
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1 inner join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on r1.rmf_xsdmf_id = x1.xsdmf_id 
					inner join  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key sk1 on sk1.sek_id = x1.xsdmf_sek_id
					inner join (
							SELECT distinct r2.rmf_rec_pid 
							FROM  
							" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
							" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
							" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2				  
							WHERE r2.rmf_xsdmf_id = x2.xsdmf_id 
							AND x2.xsdmf_sek_id = s2.sek_id 
							AND s2.sek_title = 'Object Type' 
							AND r2.rmf_int = 1
							) as o1 on o1.rmf_rec_pid = r1.rmf_rec_pid													
					";
		$returnfields = array("title");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = array();
		foreach ($res as $result) {
			if ($result['sek_title'] == "Title") {
				$return[$result['rmf_rec_pid']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $return;
        }
    }

}

if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Community Class');
}
?>
