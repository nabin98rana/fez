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
 * of collections in the system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.status.php");

class Collection
{

    /**
     * Method used to get the details for a given collection ID.
     *
     * @access  public
     * @param   string $collection_pid The collection persistant identifier
     * @return  array The collection details
     */
    function getDetails($collection_pid)
    {
        $stmt = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
					
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on x1.xsdmf_id = r1.rmf_xsdmf_id
					and rmf_rec_pid = '".$collection_pid."'
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
     * Method used to get the parents of a given collection available in the 
     * system. 
     *
     * @access  public
     * @param   string $collection_pid The collection persistant identifier	 
     * @return  array The list of parent communities
     */
    function getParents($collection_pid)
    {

		return Record::getParents($collection_pid);
    }

    /**
      * gets the parents of a collection using the Fez index (faster than fedora query)
      * @param integer $collection_pid The collection to get the parents for.
      * @return array Associative list of communities - pid, title
      */
    function getParents2($collection_pid, $nocache=false)
    {
		static $returns;
		
        // check if this has already been found and set to a static variable		
        if (!empty($returns[$collection_pid]) && !$nocache) { 
			return $returns[$collection_pid];
		} else {
			$stmt = "SELECT r1.rmf_rec_pid, r1.rmf_varchar 
				FROM fez_record_matching_field AS r1
				INNER JOIN fez_xsd_display_matchfields AS x1
				ON r1.rmf_xsdmf_id=x1.xsdmf_id
                INNER JOIN ( SELECT r3.rmf_varchar
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3
						INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3
						ON x3.xsdmf_id = r3.rmf_xsdmf_id 
						INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s3
						ON x3.xsdmf_sek_id = s3.sek_id
						WHERE s3.sek_title = 'isMemberOf' 
						AND r3.rmf_rec_pid='$collection_pid') as s1 ON s1.rmf_varchar=r1.rmf_rec_pid
				WHERE x1.xsdmf_element='!dc:title'
				";
			$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				$res = array();
			}
			$res2 = array();
			foreach ($res as $item) {
				$res2[] = array('pid' => $item['rmf_rec_pid'], 'title' => $item['rmf_varchar']);
			}
			$returns[$collection_pid] = $res2;
			return $res2;
		}
    }


    /**
     * Method used to get the XSD Display document types the collection supports, from the Fez Index.
     *
     * @access  public
     * @param   string $collection_pid The collection persistant identifier	 	 
     * @return  array The list of parent communities
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
		  AND r3.rmf_rec_pid ='".$collection_pid."' AND r3.rmf_varchar = d3.xdis_id";

		$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
	}




    /**
     * Method used to get the list of collections available in the 
     * system.
     *
     * @access  public
     * @param   string $community_pid The parent community to get the collections from, if not set then all collection will be returned. 	 	 
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return
     * @return  array The list of collections
     */
    function getList($community_pid=false, $current_row = 0, $max = 25, $order_by="Title")
    {
        $start = $current_row * $max;
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];

        // Should we restrict the list to a community.
        if ($community_pid) {
            $community_join = "	inner join (
	 						SELECT distinct r3.rmf_rec_pid 
							FROM  
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s3
							WHERE x3.xsdmf_sek_id = s3.sek_id AND s3.sek_title = 'isMemberOf' AND x3.xsdmf_id = r3.rmf_xsdmf_id 
							  AND r3.rmf_varchar = '$community_pid'
							) as com1 on com1.rmf_rec_pid = r1.rmf_rec_pid ";
        } else {
            // list all collections 
            $community_join = "";
        }
        $stmt = "SELECT
            * 
            FROM
            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
            inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 
            ON r1.rmf_xsdmf_id = x1.xsdmf_id 
           $community_join 
			inner join (
                    SELECT distinct r2.rmf_rec_pid 
                    FROM  
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2				  
                    WHERE r2.rmf_xsdmf_id = x2.xsdmf_id 
                    AND x2.xsdmf_sek_id = s2.sek_id 
                    AND s2.sek_title = 'Object Type' 
                    AND r2.rmf_varchar = '2' 
                    ) as o1 on o1.rmf_rec_pid = r1.rmf_rec_pid
			inner join (
                    SELECT distinct rmf_rec_pid FROM 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS rmf
                    INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS xdm
                    ON rmf.rmf_xsdmf_id = xdm.xsdmf_id
                    WHERE rmf.rmf_varchar=2
                    AND xdm.xsdmf_element='!sta_id'
                    ) as sta1 on sta1.rmf_rec_pid = r1.rmf_rec_pid					
             left JOIN (
                        SELECT distinct r2.rmf_rec_pid as sort_pid, 
                        r2.rmf_$data_type as sort_column
                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                        on s2.sek_id = x2.xsdmf_sek_id
                        where s2.sek_title = '$order_by'
                        ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
                left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 
            on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
            left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 
            on (k1.sek_id = x1.xsdmf_sek_id)
            order by d3.sort_column
            ";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = array();
		$return = Collection::makeReturnList($res);
        $return = Collection::makeSecurityReturnList($return);
		$hidden_rows = count($return);
		$return = Auth::getIndexAuthorisationGroups($return);		
		$return = Misc::cleanListResults($return);

		$total_rows = count($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		$return = Misc::limitListResults($return, $start, ($start + $max));
		// add the available workflow trigger buttons
		foreach ($return as $ret_key => $ret_wf) {
			$pid = $ret_wf['pid'];
			$record = new RecordObject($pid);
            $workflows = array();				
			if ($ret_wf['isEditor'] == 1) {
				$xdis_id = $ret_wf['display_type'][0];
				$ret_id = $ret_wf['object_type'][0];
				$strict = true;
				$workflows = $record->getWorkflowsByTriggerAndRET_ID('Update', $ret_id, $strict);
			}
			// check which workflows can be triggered			
			$workflows1 = array();
			if (is_array($workflows)) {
				foreach ($workflows as $trigger) {
					if (Workflow::canTrigger($trigger['wft_wfl_id'], $pid)) {
						$workflows1[] = $trigger;
					}
				}
				$workflows = $workflows1;				
			}
			$return[$ret_key]['workflows'] = $workflows;
		}

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
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
                    "last_page"     => $last_page,
                    "hidden_rows"     => $hidden_rows - $total_rows
                )
            );
        }
    }

    /**
      * List the collections in a community that can be edited by the current user
      * @param integer $community_pid The pid of the community to restrict the list to
      * @return array Associative array of collections - (pid, title)
      */
    function getEditList($community_pid=null) {
        // get list of collections that 
        // parent is community_pid
        // has ACMLs set
        //     AND user is in the roles for the ACML (group, user, combos)
        // OR parents of the collection have ACML set
        //     AND user is in the roles for the ACML
//        $returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id"); 
//        $returnfield_query = Misc::array_to_sql_string($returnfields);
        $fez_groups_sql = Misc::arrayToSQL(@$_SESSION[APP_INTERNAL_GROUPS_SESSION]);
        $ldap_groups_sql = Misc::arrayToSQL(@$_SESSION[APP_LDAP_GROUPS_SESSION]);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $order_by = 'Title';
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
        $restrict_community = '';

            $stmt = "SELECT distinct authi_pid FROM {$dbtp}auth_index WHERE
             (authi_role = 'Editor'
              OR authi_role = 'Approver')
             AND (
                 (authi_rule = '!rule!role!Fez_User' AND authi_value='".Auth::getUserID()."')
                 OR (authi_rule = '!rule!role!AD_User' AND authi_value='".Auth::getUsername()."') ";
                 if (!empty($fez_groups_sql)) {
                   $stmt .="
                   OR (authi_rule = '!rule!role!Fez_Group' AND authi_value 
                     IN ($fez_groups_sql) ) ";
                 }
                 if (!empty($ldap_groups_sql)) {
                   $stmt .= "
                   OR (authi_rule = '!rule!role!AD_Group' AND authi_value 
                     IN ($ldap_groups_sql) ) ";
                 }
                 if (Auth::isInAD())  {
                   $stmt .= "
                   OR (authi_rule = '!rule!role!in_AD' ) ";
                 }
                 if (Auth::isInDB()) {
                   $stmt .= "
                   OR (authi_rule = '!rule!role!in_Fez') ";
                 }
                 $stmt .= "
                 )";
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
            $auth_pids = $res;


        $stmt = " SELECT *
            FROM {$dbtp}record_matching_field AS r1
			INNER JOIN (
                    SELECT r2.rmf_rec_pid 
                    FROM  {$dbtp}record_matching_field r2
                    INNER JOIN {$dbtp}xsd_display_matchfields x2
                    ON r2.rmf_xsdmf_id = x2.xsdmf_id 
                    WHERE x2.xsdmf_element = '!ret_id' AND r2.rmf_varchar = '2' 
                    ) as o1 on o1.rmf_rec_pid = r1.rmf_rec_pid
			$restrict_community
             left JOIN (
                        SELECT distinct r2.rmf_rec_pid as sort_pid, 
                        r2.rmf_$data_type as sort_column
                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                        on s2.sek_id = x2.xsdmf_sek_id
                        where s2.sek_title = '$order_by'
                        ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
                INNER JOIN {$dbtp}xsd_display_matchfields AS x1 ON r1.rmf_xsdmf_id=x1.xsdmf_id
 			INNER JOIN {$dbtp}search_key as sk1 on sk1.sek_id = x1.xsdmf_sek_id
            LEFT JOIN {$dbtp}xsd_loop_subelement AS s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
            where r1.rmf_rec_pid IN (".Misc::arrayToSQL($auth_pids).")
            order by d3.sort_column
           ";
       
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        $list = Collection::makeReturnList($res);
        return $list;

    }

    /**
      * List the collections in a community 
      * @param integer $community_pid The pid of the community to restrict the list to
      * @return array Associative array of collections - (pid, title)
      */
    function getCommunityAssocList($community_pid=null) {
        // get list of collections that 
        // parent is community_pid
        // has ACMLs set
        //     AND user is in the roles for the ACML (group, user, combos)
        // OR parents of the collection have ACML set
        //     AND user is in the roles for the ACML
//        $returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id"); 
//        $returnfield_query = Misc::array_to_sql_string($returnfields);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $restrict_community = '';
        if ($community_pid) {
            $restrict_community = " INNER JOIN (
                SELECT r3.rmf_rec_pid 
                FROM  {$dbtp}record_matching_field AS r3
                INNER JOIN {$dbtp}xsd_display_matchfields AS x3
                ON x3.xsdmf_id = r3.rmf_xsdmf_id
                INNER JOIN {$dbtp}search_key AS s3
                ON x3.xsdmf_sek_id = s3.sek_id
                WHERE s3.sek_title = 'isMemberOf'   
                AND r3.rmf_varchar = '$community_pid'
                ) as com1 on com1.rmf_rec_pid = r1.rmf_rec_pid ";
        }
        $stmt = " SELECT *
            FROM {$dbtp}record_matching_field AS r1
            INNER JOIN {$dbtp}xsd_display_matchfields AS x1
            ON r1.rmf_xsdmf_id=x1.xsdmf_id
            $restrict_community
			INNER JOIN {$dbtp}search_key as sk1 on sk1.sek_id = x1.xsdmf_sek_id
			INNER JOIN (
                    SELECT r2.rmf_rec_pid 
                    FROM  {$dbtp}record_matching_field r2
                    INNER JOIN {$dbtp}xsd_display_matchfields x2
                    ON r2.rmf_xsdmf_id = x2.xsdmf_id 
                    INNER JOIN {$dbtp}search_key s2				  
                    ON x2.xsdmf_sek_id = s2.sek_id 
                    WHERE s2.sek_title = 'Object Type' 
                    AND r2.rmf_varchar = '2' 
                    ) as o1 on o1.rmf_rec_pid = r1.rmf_rec_pid
            LEFT JOIN {$dbtp}xsd_loop_subelement AS s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id)     
           ";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        $list = Collection::makeReturnList($res);
        $list = Collection::makeSecurityReturnList($list);
		return $list;
    } 
	
    function makeReturnList($res) {
		$securityfields = Auth::getAllRoles();
        $return = array();
		foreach ($res as $result) {
			if (in_array($result['xsdsel_title'], $securityfields)  && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) )  {
				if (!is_array(@$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
					$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']] = array();
				}
				if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
					array_push($return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
				}
			}
			if ($result['xsdmf_element'] == '!inherit_security') {
				if (!is_array(@$return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'])) {
					$return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'] = array();
				}
				if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'])) {
					array_push($return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'], $result['rmf_'.$result['xsdmf_data_type']]);
				}
			}
			if (@$result['sek_title'] == 'isMemberOf') {
				if (!is_array(@$return[$result['rmf_rec_pid']]['isMemberOf'])) {
					$return[$result['rmf_rec_pid']]['isMemberOf'] = array();
				}
				if (!in_array($result['rmf_varchar'], $return[$result['rmf_rec_pid']]['isMemberOf'])) {
					array_push($return[$result['rmf_rec_pid']]['isMemberOf'], $result['rmf_varchar']);
				}
			}
			// get the document type
			if (!is_array(@$return[$result['rmf_rec_pid']]['xdis_title'])) {
				$return[$result['rmf_rec_pid']]['xdis_title'] = array();
				array_push($return[$result['rmf_rec_pid']]['xdis_title'], @$result['xdis_title']);
			}
			if (is_numeric(@$result['sek_id'])) {
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
			// get thumbnails
			if ($result['xsdmf_element'] == "!datastream!ID") {
				if (is_numeric(strpos($result['rmf_varchar'], "thumbnail_"))) {
					if (!is_array(@$return[$result['rmf_rec_pid']]['thumbnails'])) {
						$return[$result['rmf_rec_pid']]['thumbnails'] = array();
					}
					array_push($return[$result['rmf_rec_pid']]['thumbnails'], $result['rmf_varchar']);
				} else {
					if (!is_array(@$return[$result['rmf_rec_pid']]['datastreams'])) {
						$return[$result['rmf_rec_pid']]['datastreams'] = array();
					}
					array_push($return[$result['rmf_rec_pid']]['datastreams'], $result['rmf_varchar']);
				}
			}
		}

		foreach ($return as $pid_key => $row) {
			//if there is only one thumbnail DS then use it
			if (count(@$row['thumbnails']) == 1) {
				$return[$pid_key]['thumbnail'] = $row['thumbnails'][0];
			} else {
				$return[$pid_key]['thumbnail'] = 0;
			}
		}
		$return = array_values($return);
		return $return;
    }

    function makeSecurityReturnList($return)
    {
		foreach ($return as $key => $row) {
            $pid = $row['pid'];
			$parentsACMLs = array();		
   			if (!is_array(@$row['FezACML']) || @$return[$key]['FezACML'][0]['!inherit_security'][0] == "on") {
				// if there is no FezACML set for this row yet, then is it will inherit from above, so show this for the form
				if (@$return[$key]['FezACML'][0]['!inherit_security'][0] == "on") {
					$parentsACMLs = $return[$key]['FezACML'];
					$return[$key]['security'] = "include";
				} else {
					$return[$key]['security'] = "inherit";
				} 
				Auth::getIndexParentACMLMemberList(&$parentsACMLs, $pid, @$row['isMemberOf']);
				$return[$key]['FezACML'] = $parentsACMLs;
			} else {
				$return[$key]['security'] = "exclude";			
			}
        }
		return $return;
    }

    /**
      * Count the records in a collection that can be edited by the current user
      * @param integer $collection_pid The pid of the collection to restrict the list to
      * @return array Associative array of records - (pid, title)
      */
    function getEditListingCount($collection_pid=null) {
        $fez_groups_sql = Misc::arrayToSQL(@$_SESSION[APP_INTERNAL_GROUPS_SESSION]);
        $ldap_groups_sql = Misc::arrayToSQL(@$_SESSION[APP_LDAP_GROUPS_SESSION]);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $restrict_collection = '';
        if ($collection_pid) {
            $stmt = " 
                SELECT r3.rmf_rec_pid 
                FROM  {$dbtp}record_matching_field AS r3
                INNER JOIN {$dbtp}xsd_display_matchfields AS x3
                ON x3.xsdmf_id = r3.rmf_xsdmf_id
                INNER JOIN {$dbtp}search_key AS s3
                ON x3.xsdmf_sek_id = s3.sek_id
                WHERE s3.sek_title = 'isMemberOf'   
                AND r3.rmf_varchar = '$collection_pid'
                ";
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
            $collection_pids = $res;
        }
        $stmt = "SELECT distinct authi_pid FROM {$dbtp}auth_index WHERE
             (authi_role = 'Editor'
              OR authi_role = 'Approver')
             AND (
                 (authi_rule = '!rule!role!Fez_User' AND authi_value='".Auth::getUserID()."')
                 OR (authi_rule = '!rule!role!AD_User' AND authi_value='".Auth::getUsername()."') ";
                 if (!empty($fez_groups_sql)) {
                   $stmt .="
                   OR (authi_rule = '!rule!role!Fez_Group' AND authi_value 
                     IN ($fez_groups_sql) ) ";
                 }
                 if (!empty($ldap_groups_sql)) {
                   $stmt .= "
                   OR (authi_rule = '!rule!role!AD_Group' AND authi_value 
                     IN ($ldap_groups_sql) ) ";
                 }
                 if (Auth::isInAD())  {
                   $stmt .= "
                   OR (authi_rule = '!rule!role!in_AD' ) ";
                 }
                 if (Auth::isInDB()) {
                   $stmt .= "
                   OR (authi_rule = '!rule!role!in_Fez') ";
                 }
                 $stmt .= "
                 )
                 ";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
        $canedit_pids = $res;
                 
        $stmt = " SELECT r2.rmf_rec_pid 
            FROM  {$dbtp}record_matching_field r2
            INNER JOIN {$dbtp}xsd_display_matchfields x2
            ON r2.rmf_xsdmf_id = x2.xsdmf_id 
            WHERE x2.xsdmf_element = '!ret_id' 
            AND r2.rmf_varchar = '3' 
            ";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
        $records_pids = $res;
        $pids = array_intersect($canedit_pids,$records_pids);
        if ($collection_pid) {
            $pids = array_intersect($pids,$collection_pids);
        }
        return count($pids);
    }

    /**
      * List the records in a collection that can be edited by the current user
      * @param integer $collection_pid The pid of the collection to restrict the list to
      * @return array Associative array of records - (pid, title)
      */
    function getEditListing($collection_pid=null) {
        // get list of collections that 
        // parent is collection_pid
        // has ACMLs set
        //     AND user is in the roles for the ACML (group, user, combos)
        // OR parents of the collection have ACML set
        //     AND user is in the roles for the ACML
        $fez_groups_sql = Misc::arrayToSQL($_SESSION[APP_INTERNAL_GROUPS_SESSION]);
        $ldap_groups_sql = Misc::arrayToSQL($_SESSION[APP_LDAP_GROUPS_SESSION]);
        $order_by = 'Title';
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $restrict_collection = '';
        if ($collection_pid) {
            $stmt = " SELECT r3.rmf_rec_pid 
                FROM  {$dbtp}record_matching_field AS r3
                INNER JOIN {$dbtp}xsd_display_matchfields AS x3
                ON x3.xsdmf_id = r3.rmf_xsdmf_id
                INNER JOIN {$dbtp}search_key AS s3
                ON x3.xsdmf_sek_id = s3.sek_id
                WHERE s3.sek_title = 'isMemberOf'   
                AND r3.rmf_varchar = '$collection_pid' ";
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
            $collection_pids = $res;
        }
        $stmt = "SELECT distinct authi_pid FROM {$dbtp}auth_index WHERE
            (authi_role = 'Editor'
             OR authi_role = 'Approver')
            AND (
                    (authi_rule = '!rule!role!Fez_User' AND authi_value='".Auth::getUserID()."')
                    OR (authi_rule = '!rule!role!AD_User' AND authi_value='".Auth::getUsername()."') ";
                    if (!empty($fez_groups_sql)) {
                    $stmt .="
                    OR (authi_rule = '!rule!role!Fez_Group' AND authi_value 
                        IN ($fez_groups_sql) ) ";
                    }
                    if (!empty($ldap_groups_sql)) {
                    $stmt .= "
                    OR (authi_rule = '!rule!role!AD_Group' AND authi_value 
                        IN ($ldap_groups_sql) ) ";
                    }
                    if (Auth::isInAD())  {
                    $stmt .= "
                    OR (authi_rule = '!rule!role!in_AD' ) ";
                    }
                    if (Auth::isInDB()) {
                    $stmt .= "
                    OR (authi_rule = '!rule!role!in_Fez') ";
                    }
                    $stmt .= "
                        )
                        ";
        if ($collection_pid) {
            if (!empty($collection_pids)) {
                $stmt .= " AND authi_pid IN (".Misc::arrayToSQL($collection_pids).") ";
            } else {
                // make it fail if there's no records in the collection
                $stmt .= " AND false ";
            }
        }
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        $auth_pids = $res;
        $object_type_pids = array();
        if (!empty($auth_pids)) {
            $stmt = "SELECT r2.rmf_rec_pid 
                FROM  {$dbtp}record_matching_field r2
                INNER JOIN {$dbtp}xsd_display_matchfields x2
                ON r2.rmf_xsdmf_id = x2.xsdmf_id 
                INNER JOIN {$dbtp}search_key s2				  
                ON x2.xsdmf_sek_id = s2.sek_id 
                WHERE s2.sek_title = 'Object Type' 
                AND r2.rmf_varchar = '3' 
                AND r2.rmf_rec_pid IN (".Misc::arrayToSQL($auth_pids).") 
                ";
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
            $object_type_pids = $res;
        }

        $res = array();
        if (!empty($object_type_pids)) {
            $stmt = " SELECT *
                FROM {$dbtp}record_matching_field AS r1
                left JOIN (
                        SELECT distinct r2.rmf_rec_pid as sort_pid, 
                        r2.rmf_$data_type as sort_column
                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                        on s2.sek_id = x2.xsdmf_sek_id
                        where s2.sek_title = '$order_by'
                        ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
                INNER JOIN {$dbtp}xsd_display_matchfields AS x1 ON r1.rmf_xsdmf_id=x1.xsdmf_id
                INNER JOIN {$dbtp}search_key as sk1 on sk1.sek_id = x1.xsdmf_sek_id
                LEFT JOIN {$dbtp}xsd_loop_subelement AS s1 ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id)     
                WHERE r1.rmf_rec_pid IN (".Misc::arrayToSQL($object_type_pids).")
                order by d3.sort_column
                ";


            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
        }
        $list = Collection::makeReturnList($res);
        return $list;	
    }


    /**
     * Method used to get the XSD Display ID of a collection object
     * system.
     *
     * Developer Note: Need to make this come from the admin interface and stored in the Fez database, rather than being hardset.	 
     *
     * @access  public
     * @return  array The list of collections
     */
    function getCollectionXDIS_ID()
    {
		// will make this more dynamic later. (probably feed from a mysql table which can be configured in the gui admin interface).
		$collection_xdis_id = 9;
		return $collection_xdis_id;
    }

    /**
     * Method used to get the list of records belonging to a specified collection available in the 
     * system.
     *
     * @access  public
     * @param   string $collection_pid The parent collection to get the records from. 	 	 
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @return  array The list of collection records with the given collection pid
     */
    function getListing($collection_pid, $current_row = 0, $max = 25, $order_by = 'Title')
    {
		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];

        // this query broken into pieces to try and get some speed.

        $stmt = "SELECT distinct r3.rmf_rec_pid 
            FROM  
            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3
            inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3
            on r3.rmf_xsdmf_id = x3.xsdmf_id 
            where x3.xsdmf_element = '!description!isMemberOf!resource'
            AND r3.rmf_varchar = '$collection_pid'";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        $coll_pids = $res;
        
        $ret_3_pids = array();
        if (!empty($coll_pids)) {
            $stmt = "SELECT distinct r2.rmf_rec_pid 
                FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                      inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                          on r2.rmf_xsdmf_id = x2.xsdmf_id 
                          where x2.xsdmf_element = '!ret_id' 
                          AND r2.rmf_varchar = '3'
                          AND r2.rmf_rec_pid IN (".Misc::arrayToSQL($coll_pids).")";
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
            $ret_3_pids = $res;
        }
        $pub_pids = array();
        if (!empty($ret_3_pids)) {
            $stmt = "SELECT distinct rmf_rec_pid FROM 
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS rmf
                        INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS xdm 
                        ON rmf.rmf_xsdmf_id = xdm.xsdmf_id AND rmf.rmf_varchar=2 AND xdm.xsdmf_element='!sta_id'                          AND rmf.rmf_rec_pid IN (".Misc::arrayToSQL($ret_3_pids).")";
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
            $pub_pids = $res;
        }

       if (!empty($pub_pids)) {
            $stmt = "   SELECT distinct r2.rmf_rec_pid
                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        where x2.xsdmf_element = '!xdis_id'
						order by r2.rmf_rec_pid";
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
            $display_pids = $res;
			$pids = array_intersect($display_pids,$pub_pids);
			$pids = Misc::arrayToSQL($pids);

            $stmt = "   SELECT distinct r2.rmf_varchar as display_id
                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        where x2.xsdmf_element = '!xdis_id'
                        and r2.rmf_rec_pid in  (".$pids.")
						order by r2.rmf_rec_pid";
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
            $display_ids = $res;
        }
//        $pids = array_intersect($display_pids,$pub_pids);
//        $pids = Misc::arrayToSQL($pids);
        if (!empty($pids)) {
            $stmt = " SELECT * 
                FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
                inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1
                on x1.xsdmf_id = r1.rmf_xsdmf_id
                inner JOIN (
                        SELECT distinct r2.rmf_rec_pid as sort_pid, 
                        r2.rmf_$data_type as sort_column
                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                        on s2.sek_id = x2.xsdmf_sek_id
                        where s2.sek_title = '$order_by'
						order by sort_pid
                        ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
                left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 
                on (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
                left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 
                on (k1.sek_id = x1.xsdmf_sek_id)
                left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1 on d1.xdis_id in (".Misc::arrayToSQL($display_ids).") and d1.xdis_id = x1.xsdmf_xdis_id
                WHERE (r1.rmf_dsid IS NULL or r1.rmf_dsid = '')			 
                and r1.rmf_rec_pid IN (".$pids.")
                ORDER BY
                d3.sort_column ";
//			echo $stmt; exit;
            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
        }

		$return = array();
		$return = Collection::makeReturnList($res);
        $return = Collection::makeSecurityReturnList($return);
		$hidden_rows = count($return);
		$return = Auth::getIndexAuthorisationGroups($return);
		$return = Misc::cleanListResults($return);

		$total_rows = count($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		$return = Misc::limitListResults($return, $start, ($start + $max));		
		// add the available workflow trigger buttons

		foreach ($return as $ret_key => $ret_wf) {
			$pid = $ret_wf['pid'];
			$record = new RecordObject($pid);
            $workflows = array();
			if ($ret_wf['isEditor']) {
				$xdis_id = $ret_wf['display_type'][0];
				$ret_id = $ret_wf['object_type'][0];
				$strict = false;
				$workflows = $record->getWorkflowsByTriggerAndRET_ID('Update', $ret_id, $strict);
			}
			// check which workflows can be triggered			
			$workflows1 = array();
			if (is_array($workflows)) {
				foreach ($workflows as $trigger) {
					if (Workflow::canTrigger($trigger['wft_wfl_id'], $pid)) {
						$workflows1[] = $trigger;
					}
				}
				$workflows = $workflows1;
			} 
			$return[$ret_key]['workflows'] = $workflows; 
		}  

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
     * Method used to get the count of a list of object IDs against the controlled vocabularies they belong to.
     *
     * @access  public
     * @param   array $tree_ids The list of Controlled Vocabulary IDs in a CV tree to search against.
     * @param   integer $parent_id The top level hierarchy CV ID of the controlled vocabulary.
     * @param   string $searchKey The search key to search for the controlled vocabulary, defaulting to the Subject.
     * @return  array The list of collection records with the given collection pid
     */
    function getCVCountSearch($treeIDs, $parent_id=false, $searchKey="Subject")
    {
		// get the count of everything in the tree, but the display will only show what is need at each branch
		if (empty($treeIDs)) {
			return array();
		}
		$termCounter = 2;
		$stringIDs = implode("', '", Misc::array_flatten($treeIDs));
		$middleStmt = 
		" INNER JOIN (
				SELECT distinct r".$termCounter.".rmf_rec_pid 
				FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r".$termCounter.",
					  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x".$termCounter.",
					  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s".$termCounter."  							  
				WHERE r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id AND s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id AND s".$termCounter.".sek_title = '".$searchKey."' AND r".$termCounter.".rmf_varchar in ('".$stringIDs."') 
				) as r".$termCounter." on r1.rmf_rec_pid = r".$termCounter.".rmf_rec_pid
		";
        $stmt = "SELECT
                    r1.rmf_varchar, count(distinct r1.rmf_rec_pid) as cv_count
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id) left join
 				    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 on (k1.sek_id = x1.xsdmf_sek_id)
				";				
				$stmt .= $middleStmt;
				$stmt .= 
                " WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id and k1.sek_title = 'Subject'
				 GROUP BY
				 	r1.rmf_varchar";
		$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			$parent_count = 0;
			foreach ($res as $key => $data) {
				if (is_numeric($data)) {
					$parent_count += $data;
				}
				Collection::fillTree(&$res, $treeIDs, $key, $data);
			}
			if (is_numeric($parent_id)) {
				$res[$parent_id] = $parent_count;
			}

			return $res;

        }

    }

    /**
     * Method used to add up all the 
     *
     * Developer Note: This is a recursive function to find the count of all the objects with the given controlled vocabulary IDs
     *	 
     * @access  public
     * @param   array $res The list of all the controlled vocabularies with they given counts. Passed by reference recursively.
     * @param   array $tree_ids  The list of Controlled Vocabulary IDs in a CV tree to search against.
     * @param   integer $key The current controlled vocabulary key to search for.
     * @param   integer $data The count of the controlled vocabulary currently being searched for.
     * @return  void (Returns $res by reference).
     */
	function fillTree(&$res, $treeIDs, $key, $data) {
		foreach ($treeIDs as $tkey => $tdata) {		
			if (is_array($tdata)) {
				if (Misc::in_multi_array($key, $treeIDs[$tkey])) {
					if (!empty($res[$tkey])) {
						$res[$tkey] += $data;
					} else {
						$res[$tkey] = $data;
					}
					Collection::fillTree(&$res, $tdata, $key, $data);
				}
			}
		}
	
	}

    /**
     * Method used to get the list of records in browse view by a browsing category available in the 
     * system.
     *
     * @access  public
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @param   string $searchKey The search key the records are being browsed by eg Subject, Created Date (latest additions).
     * @return  array The list of records 
     */
    function browseListing($current_row = 0, $max = 25, $searchKey="Subject", $order_by=null)
    {
        if (empty($order_by)) {
            $order_by = $searchKey;
            if (empty($order_by)) {
                $order_by = 'Title';
            }
        }
		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
        $order_dir = ' asc ';
		$restrictSQL = "";
		$middleStmt = "";
		$termCounter = 2;
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;

        $extra = '';
		if ($searchKey == "Subject") {				
			$terms = $_GET['parent_id'];		
			$search_data_type = "varchar";
			$restrictSQL = "AND r".$termCounter.".rmf_".$search_data_type." = '".$terms."'";
		} elseif ($searchKey == "Created Date") {
			$search_data_type = "date";
			$restrictSQL = "AND DATE_SUB(CURDATE(), INTERVAL 6 DAY) < r".$termCounter.".rmf_".$search_data_type."";
			$extra = ", DAYNAME(r".$termCounter.".rmf_date) as day_name";		
            $middleStmt .= " 
                LEFT JOIN (
                        SELECT distinct r".$termCounter.".rmf_".$search_data_type.",  r".$termCounter.".rmf_rec_pid
                        FROM  {$dbtp}record_matching_field r".$termCounter."
                        INNER JOIN {$dbtp}xsd_display_matchfields x".$termCounter."
                        ON r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id 
                        INNER JOIN {$dbtp}search_key s".$termCounter."  							  
                        ON s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id 
                        WHERE s".$termCounter.".sek_title = '".$searchKey."' ".$restrictSQL."
                        ) AS r".$termCounter." 
                ON r1.rmf_rec_pid = r".$termCounter.".rmf_rec_pid ";
			$termCounter++;
			$restrictSQL = "AND DATE_SUB(CURDATE(), INTERVAL 6 DAY) < r".$termCounter.".rmf_".$search_data_type."";
            if ($order_by == 'Created Date') {
                $order_dir = " DESC ";
            }
		} elseif ($searchKey == "Date") {
			$terms = $_GET['year'];
			$search_data_type = "date";
			$restrictSQL = "AND YEAR(r".$termCounter.".rmf_".$search_data_type.") = ".$terms."";
		} elseif ($searchKey == "Author") {
			$terms = mysql_escape_string($_GET['author']);
			$search_data_type = "varchar";
			$restrictSQL = "AND r".$termCounter.".rmf_".$search_data_type." = '".$terms."'";
		} else {
			$search_data_type = "varchar";		
		}
		$middleStmt .= " 
            INNER JOIN (
                    SELECT distinct r".$termCounter.".rmf_rec_pid 
                    FROM  {$dbtp}record_matching_field AS r".$termCounter."
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x".$termCounter."
                    ON r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id 
                    INNER JOIN {$dbtp}search_key AS s".$termCounter."
                    ON s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id
                    WHERE s".$termCounter.".sek_title = '".$searchKey."' ".$restrictSQL."
				) AS r".$termCounter." 
            ON r1.rmf_rec_pid = r".$termCounter.".rmf_rec_pid ";
        $stmt = "SELECT * ".$extra."
                 FROM {$dbtp}record_matching_field AS r1
                 INNER JOIN {$dbtp}xsd_display_matchfields AS x1 
                 ON r1.rmf_xsdmf_id = x1.xsdmf_id 
                 LEFT JOIN {$dbtp}xsd_display AS d1
                 ON (x1.xsdmf_xdis_id = d1.xdis_id) 
                 LEFT JOIN {$dbtp}xsd_loop_subelement AS s1 
                 ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
                 LEFT JOIN {$dbtp}search_key AS k1 
                 ON (k1.sek_id = x1.xsdmf_sek_id)
                 JOIN {$dbtp}xsd_display AS d4 				 
                 $middleStmt
	             INNER JOIN (
				SELECT distinct r2.rmf_rec_pid, r2.rmf_varchar as display_id
				FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
				WHERE r2.rmf_xsdmf_id = x2.xsdmf_id 
				AND s2.sek_id = x2.xsdmf_sek_id 
				AND s2.sek_title = 'Display Type' 
				) as d2
            on r1.rmf_rec_pid = d2.rmf_rec_pid and d2.display_id = d4.xdis_id			
                left JOIN (
                        SELECT distinct r2.rmf_rec_pid as sort_pid, 
                        r2.rmf_$data_type as sort_column
                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                        on s2.sek_id = x2.xsdmf_sek_id
                        where s2.sek_title = '$order_by'
                        ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
                  WHERE r1.rmf_rec_pid IN (
                         SELECT rmf_rec_pid FROM 
                         {$dbtp}record_matching_field AS rmf
                         INNER JOIN {$dbtp}xsd_display_matchfields AS xdm
                         ON rmf.rmf_xsdmf_id = xdm.xsdmf_id
                         WHERE rmf.rmf_varchar=2
                         AND xdm.xsdmf_element='!sta_id'
                         )
                 ORDER BY d3.sort_column $order_dir";
		$securityfields = Auth::getAllRoles();
//		$returnfields = array("day_name", "created_date", "updated_date", "file_downloads", "xdis_title", "title", "date", "type", "description", "identifier", "creator", "ret_id", "xdis_id", "sta_id", "Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {

			$return = array();
			$return = Collection::makeReturnList($res);
            $return = Collection::makeSecurityReturnList($return);
			$hidden_rows = count($return);
			$return = Auth::getIndexAuthorisationGroups($return);
			$return = Misc::cleanListResults($return);
			$total_rows = count($return);
			if (($start + $max) < $total_rows) {
				$total_rows_limit = $start + $max;
			} else {
			   $total_rows_limit = $total_rows;
			}
			$total_pages = ceil($total_rows / $max);
			$last_page = $total_pages - 1;
			$return = Misc::limitListResults($return, $start, ($start + $max));
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
     * Method used to get the list of records in browse view year (date). This needed to be differently setup to browseListing for dates, authors etc.
     *
     * @access  public
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @param   string $searchKey The search key the records are being browsed by  Date (Year) or Author
     * @return  array The list of records 
     */
    function listByAttribute($current_row = 0, $max = 25, $searchKey="Date",$order_by="Title")
    {
		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
		$restrictSQL = "";
		$middleStmt = "";
		$termCounter = 2;
		if ($searchKey == "Subject") {				
			$terms = $_GET['parent_id'];		
			$search_data_type = "varchar";
		} elseif ($searchKey == "Date") {
			$search_data_type = "date";
			$group_field = "year(r1.rmf_".$search_data_type.")";
			$as_field = "record_year";
		} elseif ($searchKey == "Author") {
			$search_data_type = "varchar";
			$group_field = "(r1.rmf_".$search_data_type.")";
			$as_field = "record_author";
		} else {
			$search_data_type = "varchar";
			$group_field = "(r1.rmf_".$search_data_type.")";		
			$as_field = "record_author";
		}
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
		$middleStmt .= 
		" INNER JOIN (
				SELECT distinct r".$termCounter.".rmf_id 
				FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS r".$termCounter."
                INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS x".$termCounter."
                ON r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id
                INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key AS s".$termCounter."  
                ON s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id
				WHERE  s".$termCounter.".sek_title = '".$searchKey."' ".$restrictSQL."
				) AS r".$termCounter." 
                ON r1.rmf_id = r".$termCounter.".rmf_id
		";
        $stmt = "SELECT
                    count(*) as record_count, ".$group_field." as ".$as_field."
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS r1
                    INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS x1
                    ON r1.rmf_xsdmf_id = x1.xsdmf_id 
                    LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement AS s1
                    ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
                    LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key AS k1
                    ON (k1.sek_id = x1.xsdmf_sek_id)
				$middleStmt
                left JOIN (
                        SELECT distinct r2.rmf_rec_pid as sort_pid, 
                        r2.rmf_$data_type as sort_column
                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                        on s2.sek_id = x2.xsdmf_sek_id
                        where s2.sek_title = '$order_by'
                        ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
                  WHERE r1.rmf_rec_pid IN (
                         SELECT rmf_rec_pid FROM 
                         {$dbtp}record_matching_field AS rmf
                         INNER JOIN {$dbtp}xsd_display_matchfields AS xdm
                         ON rmf.rmf_xsdmf_id = xdm.xsdmf_id
                         WHERE rmf.rmf_varchar=2
                         AND xdm.xsdmf_element='!sta_id'
                         )
				 GROUP BY
				 	".$group_field."
				 ORDER BY
				 	d3.sort_column ";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		foreach ($res as $key => $row) {
			if (trim($row[$as_field]) != "") {
				$return[$key][$as_field] = $row[$as_field];
				$return[$key]['record_count'] = $row['record_count'];
			}
		}
		$hidden_rows = count($return);
		$total_rows = count($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		$return = Misc::limitListResults($return, $start, ($start + $max));
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
     * Method used to get the statistics by a specified searchKey like Author or Title of the paper
     *
     * @access  public
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @param   string $searchKey The search key of the stats eg Title or Author, or any other search key
     * @return  array The list of records 
     */
    function statsByAttribute($current_row = 0, $max = 25, $searchKey="Author")
    {
		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;
		$restrictSQL = "";
		$middleStmt = "";
		$extra = "";
		$termCounter = 3;
		if ($searchKey == "Title") {
			$data_type = "varchar";
			$extra = ", r1.rmf_rec_pid as pid";
			$group_field = "r1.rmf_".$data_type.", r1.rmf_rec_pid";
			$as_field = "record_title";
		} elseif ($searchKey == "Author") {
			$data_type = "varchar";
			$group_field = "(r1.rmf_".$data_type.")";
			$as_field = "record_author";
		} else {
			$data_type = "varchar";
			$group_field = "(r1.rmf_".$data_type.")";		
			$as_field = "record_author";
		}
		$middleStmt .= 
		" INNER JOIN (
				SELECT distinct r".$termCounter.".rmf_id 
				FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r".$termCounter.",
					  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x".$termCounter.",
					  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s".$termCounter."  							  
				WHERE r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id AND s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id AND s".$termCounter.".sek_title = '".$searchKey."' ".$restrictSQL."
				) as r".$termCounter." on r1.rmf_id = r".$termCounter.".rmf_id";
		$termCounter++;
		// only get the record level objects
		$middleStmt .= 
		" INNER JOIN (
				SELECT distinct r".$termCounter.".rmf_rec_pid 
				FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r".$termCounter.",
					  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x".$termCounter.",
					  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s".$termCounter."  							  
				WHERE r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id AND s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id AND s".$termCounter.".sek_title = 'Object Type' AND r".$termCounter.".rmf_varchar='3'
				) as r".$termCounter." on r1.rmf_rec_pid = r".$termCounter.".rmf_rec_pid";
		$termCounter++;
		$middleStmt .= 
		" LEFT JOIN ( 
		   		SELECT distinct r".$termCounter.".rmf_id 
				FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r".$termCounter.", 
					 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x".$termCounter.",
					 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s".$termCounter." 
				WHERE r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id AND s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id AND s".$termCounter.".sek_title = 'File Downloads' ) 
				as r".$termCounter." on r".$termCounter.".rmf_id = r2.rmf_id 
		";
        $stmt = "SELECT
                   r1.rmf_".$data_type." as ".$as_field.$extra.", IFNULL(sum(r2.rmf_int),0) as file_downloads
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1 INNER JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 ON (r1.rmf_xsdmf_id = x1.xsdmf_id) left JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2 ON (r1.rmf_rec_pid = r2.rmf_rec_pid) left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id) left join
 				    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 on (k1.sek_id = x1.xsdmf_sek_id)
				";
				$stmt .= $middleStmt;
				$stmt .= 
                "  GROUP BY
				    ".$group_field."
				  ORDER BY
				     file_downloads DESC, r1.rmf_".$data_type."
				  LIMIT 0,50"; // only get the top 50
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = $res;
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return array(
                "list" => $return,
                "info" => array(
                    "current_page"  => 0,
                    "start_offset"  => 0,
                    "end_offset"    => 50,
                    "total_rows"    => 50,
                    "total_pages"   => 1,
                    "previous_page" => -1,
                    "next_page"     => -1,
                    "last_page"     => 0,
                    "hidden_rows"     => 0
                )
            );
        }
    }


    /**
     * Method used to perform advanced searching of objects in Fez. Gets the search criteria from a querystring 'list'.
     *
     * @access  public
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @return  array The list of Fez objects matching the search criteria
     */
    function advSearchListing($current_row = 0, $max = 25, $order_by = 'Title')
    {
		$terms = $_GET['list'];

		if (empty($terms)) {
			return array();
		}

        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];

		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;
		$middleStmt = "";
		$foundValue = false;
		$termCounter = 2;

        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;

        foreach ($terms as $tkey => $tdata) {
            if (!empty($tdata) && ($tdata != "-1")) {
				if (is_array($tdata)) {
					 foreach ($tdata as $tsubkey => $tsubdata) {
						if (!empty($tsubdata) && ($tsubdata != "-1")) {
							$middleStmt .= 
								" INNER JOIN 
								(
								 SELECT distinct r{$termCounter}.rmf_rec_pid 
								 FROM  {$dbtp}record_matching_field AS r{$termCounter}
								 INNER JOIN {$dbtp}xsd_display_matchfields AS x{$termCounter}
								 ON r{$termCounter}.rmf_xsdmf_id = x{$termCounter}.xsdmf_id 
								 INNER JOIN {$dbtp}search_key AS s{$termCounter} 
								 ON s{$termCounter}.sek_id = x{$termCounter}.xsdmf_sek_id 
								 WHERE s{$termCounter}.sek_id = {$tkey} 
								 AND r{$termCounter}.rmf_varchar like '%{$tsubdata}%' 
								) AS r{$termCounter} 
							ON r1.rmf_rec_pid = r{$termCounter}.rmf_rec_pid
								";
							$termCounter++;
						}
					 }
				} else {
					$middleStmt .= 
						" INNER JOIN 
						(
						 SELECT distinct r{$termCounter}.rmf_rec_pid 
						 FROM  {$dbtp}record_matching_field AS r{$termCounter}
						 INNER JOIN {$dbtp}xsd_display_matchfields AS x{$termCounter}
						 ON r{$termCounter}.rmf_xsdmf_id = x{$termCounter}.xsdmf_id 
						 INNER JOIN {$dbtp}search_key AS s{$termCounter} 
						 ON s{$termCounter}.sek_id = x{$termCounter}.xsdmf_sek_id 
						 WHERE s{$termCounter}.sek_id = {$tkey} 
						 AND r{$termCounter}.rmf_varchar like '%{$tdata}%' 
						) AS r{$termCounter} 
					ON r1.rmf_rec_pid = r{$termCounter}.rmf_rec_pid
						";
					$termCounter++;
				}
				$foundValue = true;
			}
		}

		if ($foundValue == false) {
			return array();
		}

        $stmt = "SELECT * 
            FROM {$dbtp}record_matching_field r1
            INNER JOIN {$dbtp}xsd_display_matchfields x1 
            ON r1.rmf_xsdmf_id = x1.xsdmf_id 
            LEFT JOIN {$dbtp}xsd_loop_subelement s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN {$dbtp}search_key k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
            JOIN {$dbtp}xsd_display d1 			
            $middleStmt
			 INNER JOIN (
			SELECT distinct r2.rmf_rec_pid, r2.rmf_varchar as display_id
			FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
			" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
			" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
			WHERE r2.rmf_xsdmf_id = x2.xsdmf_id 
			AND s2.sek_id = x2.xsdmf_sek_id 
			AND s2.sek_title = 'Display Type' 
			) as d2
            on r1.rmf_rec_pid = d2.rmf_rec_pid and d2.display_id = d1.xdis_id
            left JOIN (
                    SELECT distinct r2.rmf_rec_pid as sort_pid, 
                    r2.rmf_$data_type as sort_column
                    FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                    on r2.rmf_xsdmf_id = x2.xsdmf_id 
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                    on s2.sek_id = x2.xsdmf_sek_id
                    where s2.sek_title = '$order_by'
                    ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
            WHERE
            r1.rmf_rec_pid IN (
                    SELECT rmf_rec_pid FROM 
                    {$dbtp}record_matching_field AS rmf
                    INNER JOIN {$dbtp}xsd_display_matchfields AS xdm
                    ON rmf.rmf_xsdmf_id = xdm.xsdmf_id
                    WHERE rmf.rmf_varchar=2
                    AND xdm.xsdmf_element='!sta_id'
                    )
            ORDER BY d3.sort_column ";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = array();
		$return = Collection::makeReturnList($res);
        $return = Collection::makeSecurityReturnList($return);
		$hidden_rows = count($return);
		$return = Auth::getIndexAuthorisationGroups($return);
		$return = Misc::cleanListResults($return);
		$total_rows = count($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		$return = Misc::limitListResults($return, $start, ($start + $max));
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
     * Method used to perform basic searching on searchkeys specified to be searched in a simple search.
     *
     * @access  public
     * @param   string $terms The list of search terms.
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @return  array The list of Fez objects matching the search criteria
     */
    function searchListing($terms, $current_row = 0, $max = 25, $order_by='Title')
    {
		if (empty($terms)) {
			return array();
		}

		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;

        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];

		$termArray = explode(" ", $terms);
        $termLike = '';
		foreach ($termArray as $key => $data) {
			if ($termLike == "") {
				$termLike = "(";
			} else {
				$termLike .= " OR ";
			}
			$termLike .= "r2.rmf_varchar like '%".$data."%' ";
			
		}
		$termLike .= ") AND ";
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT * 
            FROM {$dbtp}record_matching_field AS r1
            INNER JOIN {$dbtp}xsd_display_matchfields AS x1 
            ON r1.rmf_xsdmf_id = x1.xsdmf_id 
            LEFT JOIN {$dbtp}xsd_loop_subelement AS s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN {$dbtp}search_key AS k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
			join {$dbtp}xsd_display AS d1 
            INNER JOIN (
                    SELECT distinct r2.rmf_rec_pid 
                    FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x2
                    ON r2.rmf_xsdmf_id = x2.xsdmf_id 
                    INNER JOIN {$dbtp}search_key AS s2  							  
                    ON s2.sek_id = x2.xsdmf_sek_id 
                    WHERE $termLike s2.sek_simple_used = 1
                    ) AS r2 
            ON r1.rmf_rec_pid = r2.rmf_rec_pid
			INNER JOIN (
			SELECT distinct r2.rmf_rec_pid, r2.rmf_varchar as display_id
			FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
			" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
			" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
			WHERE r2.rmf_xsdmf_id = x2.xsdmf_id 
			AND s2.sek_id = x2.xsdmf_sek_id 
			AND s2.sek_title = 'Display Type' 
			) as d2
            on r1.rmf_rec_pid = d2.rmf_rec_pid and d2.display_id = d1.xdis_id						
		
            left JOIN (
                    SELECT distinct r2.rmf_rec_pid as sort_pid, 
                    r2.rmf_$data_type as sort_column
                    FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                    on r2.rmf_xsdmf_id = x2.xsdmf_id 
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                    on s2.sek_id = x2.xsdmf_sek_id
                    where s2.sek_title = '$order_by'
                    ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
	
            WHERE r1.rmf_rec_pid IN (
                    SELECT rmf_rec_pid FROM 
                    {$dbtp}record_matching_field AS rmf
                    INNER JOIN {$dbtp}xsd_display_matchfields AS xdm
                    ON rmf.rmf_xsdmf_id = xdm.xsdmf_id
                    WHERE rmf.rmf_varchar=2
                    AND xdm.xsdmf_element='!sta_id'
                    )
            ORDER BY d3.sort_column";
		$securityfields = Auth::getAllRoles();
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		
		$return = array();
		foreach ($res as $result) {
			if (in_array($result['xsdsel_title'], $securityfields) 
                    && ($result['xsdmf_element'] != '!rule!role!name') 
                    && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
                // need to array_push because there can be multiple groups/users for a role
				$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']][] 
                    = $result['rmf_'.$result['xsdmf_data_type']]; 
			}
			if ($result['sek_title'] == 'isMemberOf') {
                $return[$result['rmf_rec_pid']]['isMemberOf'][] = $result['rmf_varchar'];
			}
			// get the document type
			if (!is_array(@$return[$result['rmf_rec_pid']]['xdis_title'])) {
				$return[$result['rmf_rec_pid']]['xdis_title'] = array();
				array_push($return[$result['rmf_rec_pid']]['xdis_title'], $result['xdis_title']);
			}			
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
			if ($result['xsdmf_element'] == "!datastream!ID") {
				if (is_numeric(strpos($result['rmf_varchar'], "thumbnail_"))) {
					$return[$result['rmf_rec_pid']]['thumbnails'][] = $result['rmf_varchar'];
				} else {
					$return[$result['rmf_rec_pid']]['datastreams'][] = $result['rmf_varchar'];
				}
			}
		}
		
		foreach ($return as $pid_key => $row) {
			//if there is only one thumbnail DS then use it
			if (count(@$row['thumbnails']) == 1) {
				$return[$pid_key]['thumbnail'] = $row['thumbnails'][0];
			} else {
				$return[$pid_key]['thumbnail'] = 0;
			}

			if (!is_array(@$row['FezACML'])) {
				$parentsACMLs = array();
				Auth::getIndexParentACMLMemberList(&$parentsACMLs, $pid_key, @$row['isMemberOf']);
				$return[$pid_key]['FezACML'] = $parentsACMLs;
			}
		}
		
		$return = array_values($return);
		$hidden_rows = count($return);
		$return = Auth::getIndexAuthorisationGroups($return);
		$return = Misc::cleanListResults($return);

		$total_rows = count($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		$return = Misc::limitListResults($return, $start, ($start + $max));
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
     * Method used to get the count of records in a collection.
     *
     * @access  public
     * @param   integer $collection_pid The collection pid to search for the .
     * @return  integer $res The count of the records in the collection, 0 if none were found.
     */
    function getCount($collection_pid)
    {
        // Member of Collections, Fedora Records RELS-EXT Display, /RDF/description/isMemberOf/resource
		$dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT count(distinct(r3.rmf_rec_pid))
                FROM  {$dbtp}record_matching_field AS r3
                INNER JOIN {$dbtp}xsd_display_matchfields AS x3
                ON x3.xsdmf_id = r3.rmf_xsdmf_id
                INNER JOIN {$dbtp}search_key AS s3
                ON x3.xsdmf_sek_id = s3.sek_id
                WHERE s3.sek_title = 'isMemberOf'   
                AND r3.rmf_varchar = '$collection_pid'
                ) as com1 on com1.rmf_rec_pid = r1.rmf_rec_pid";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return 0;
        }
        return $res;
    }


    /**
     * Method used to get an associative array of collection ID and title
     * of all collections available in the system.
     *
     * @access  public
     * @return  array $return The list of collections
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on x1.xsdmf_id = r1.rmf_xsdmf_id
				    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key sk1 on sk1.sek_id = x1.xsdmf_sek_id
					inner join (
							SELECT distinct r2.rmf_rec_pid 
							FROM  
							" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
							" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
							" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2				  
							WHERE r2.rmf_xsdmf_id = x2.xsdmf_id 
							AND x2.xsdmf_sek_id = s2.sek_id 
							AND s2.sek_title = 'Object Type' 
							AND r2.rmf_varchar = '2' 
							) as o1 on o1.rmf_rec_pid = r1.rmf_rec_pid							
					";
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

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Collection Class');
}
?>
