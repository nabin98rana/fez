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
				FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS r1
				INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS x1
				ON r1.rmf_xsdmf_id=x1.xsdmf_id
                INNER JOIN ( SELECT r3.rmf_varchar
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3
						INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3
						ON x3.xsdmf_id = r3.rmf_xsdmf_id 
						INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s3
						ON x3.xsdmf_sek_id = s3.sek_id
						WHERE s3.sek_title = 'isMemberOf' 
						AND r3.rmf_rec_pid='$collection_pid') as s1 ON s1.rmf_varchar=r1.rmf_rec_pid
				WHERE match(x1.xsdmf_element) against ('\"!dc:title\"' in boolean mode)
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
							  AND match(r3.rmf_varchar) against ('\"$community_pid\"' in boolean mode)
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
//		echo $stmt;
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
				$ret_id = trim($ret_wf['object_type'][0]);
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
	  * - mainly used in NAJAX drop down lists of collections from my fez
      * @param integer $community_pid The pid of the community to restrict the list to
      * @return array Associative array of collections - (pid, title)
      */
    function getEditListAssoc($community_pid=null) {
		$list = Collection::getEditList($community_pid);
		$returnList = array();
		foreach ($list as $element) {
			$returnList[$element['pid']] = $element['title'][0];
		}
		return $returnList;	
	}

    /**
      * List the collections in a community that can be edited by the current user
	  * - mainly used in NAJAX drop down lists of collections from my fez
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

        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $order_by = 'Title';
        $order_dir = 'asc';
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
        $restrict_community = '';

		$authArray = Collection::getAuthIndexStmt(array("Creator", "Editor", "Approver"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];

	    if (!empty($community_pid)) {		
			$memberOfStmt = "
						INNER JOIN {$dbtp}record_matching_field AS r4
						  ON r4.rmf_rec_pid = r2.rmf_rec_pid
						INNER JOIN {$dbtp}xsd_display_matchfields AS x4
						  ON r4.rmf_xsdmf_id = x4.xsdmf_id and match(r4.rmf_varchar) against ('\"$community_pid\"' in boolean mode)
						INNER JOIN {$dbtp}search_key AS s4  							  
						  ON s4.sek_id = x4.xsdmf_sek_id AND s4.sek_title = 'isMemberOf' ";
		} else {
			$memberOfStmt = "";
		}

        $bodyStmtPart1 = "FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x2
                      ON r2.rmf_xsdmf_id = x2.xsdmf_id AND match(x2.xsdmf_element) against ('\"!ret_id\"' in boolean mode) and r2.rmf_varchar='2'
					

                    $authStmt

					$memberOfStmt

                    ";
        $bodyStmt = "$bodyStmtPart1
                  
                    LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r5
                    ON r5.rmf_rec_pid=r2.rmf_rec_pid
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x5
                    on r5.rmf_xsdmf_id = x5.xsdmf_id
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s5
                    on s5.sek_id = x5.xsdmf_sek_id AND s5.sek_title = '$order_by'
                    
             ";

        $countStmt = "
                    SELECT count(distinct r2.rmf_rec_pid)
                    $bodyStmtPart1
            ";

        $stmt = "SELECT  r1.*, x1.*, s1.*, k1.*, d1.* 
            FROM {$dbtp}record_matching_field AS r1
            INNER JOIN {$dbtp}xsd_display_matchfields AS x1
            ON r1.rmf_xsdmf_id = x1.xsdmf_id
            INNER JOIN (
                    SELECT distinct r2.rmf_rec_pid, r5.rmf_$data_type as sort_column
                    $bodyStmt
                    ) as display ON display.rmf_rec_pid=r1.rmf_rec_pid 
            LEFT JOIN {$dbtp}xsd_loop_subelement s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN {$dbtp}search_key k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
            LEFT JOIN {$dbtp}xsd_display d1  
            ON (d1.xdis_id = r1.rmf_varchar and k1.sek_title = 'Title')
            ORDER BY display.sort_column $order_dir, r1.rmf_rec_pid DESC ";

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
            if (!empty($result['xdis_title'])) {
                if (!is_array(@$return[$result['rmf_rec_pid']]['xdis_title'])) {
                    $return[$result['rmf_rec_pid']]['xdis_title'] = array();
                }
                if (!in_array($result['xdis_title'],$return[$result['rmf_rec_pid']]['xdis_title'])) {
                    array_push($return[$result['rmf_rec_pid']]['xdis_title'], $result['xdis_title']);
                }
            }
			if (is_numeric(@$result['sek_id'])) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$search_var = strtolower(str_replace(" ", "_", $result['sek_title']));
				if (@!is_array($return[$result['rmf_rec_pid']][$search_var])) {
					$return[$result['rmf_rec_pid']][$search_var] = array();
				}
				if (!in_array($result['rmf_'.$result['xsdmf_data_type']], 
                            $return[$result['rmf_rec_pid']][$search_var])) {
					array_push($return[$result['rmf_rec_pid']][$search_var], 
                            $result['rmf_'.$result['xsdmf_data_type']]);
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
			if (count(@$row['thumbnails']) > 0) {
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
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
		$authArray = Collection::getAuthIndexStmt(array("Creator", "Editor", "Approver"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];

	    if (!empty($collection_pid)) {		
			$memberOfStmt = "
						INNER JOIN {$dbtp}record_matching_field AS r4
						  ON r4.rmf_rec_pid = r2.rmf_rec_pid
						INNER JOIN {$dbtp}xsd_display_matchfields AS x4
						  ON r4.rmf_xsdmf_id = x4.xsdmf_id and match(r4.rmf_varchar) against ('\"$collection_pid\"' in boolean mode)
						INNER JOIN {$dbtp}search_key AS s4  							  
						  ON s4.sek_id = x4.xsdmf_sek_id AND s4.sek_title = 'isMemberOf' ";
		} else {
			$memberOfStmt = "";
		}

        $bodyStmtPart1 = "FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x2
                      ON r2.rmf_xsdmf_id = x2.xsdmf_id AND x2.xsdmf_element='!ret_id' and r2.rmf_varchar='3'
					

                    $authStmt

					$memberOfStmt

                    ";

        $countStmt = "
                    SELECT count(distinct r2.rmf_rec_pid)
                    $bodyStmtPart1
            ";
		$res = $GLOBALS["db_api"]->dbh->getCol($countStmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        return $res[0];

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
        $start = 0;
        $max = 100;
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
		$authArray = Collection::getAuthIndexStmt(array( "Editor", "Approver"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];

        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $restrict_collection = '';
        $memberOfStmt = " AND false ";
       if (!empty($collection_pid)) {
            $memberOfStmt = "
                INNER JOIN {$dbtp}record_matching_field AS r4
                ON r4.rmf_rec_pid = r2.rmf_rec_pid AND r4.rmf_varchar = '$collection_pid'
                INNER JOIN {$dbtp}xsd_display_matchfields AS x4
                ON x4.xsdmf_id = r4.rmf_xsdmf_id
                INNER JOIN {$dbtp}search_key AS s4
                ON x4.xsdmf_sek_id = s4.sek_id and s4.sek_title = 'isMemberOf'
                 ";
        }
            $objectTypestmt = "
                INNER JOIN {$dbtp}record_matching_field AS r6
                ON r6.rmf_rec_pid = r4.rmf_rec_pid  AND r6.rmf_varchar = '3' 
                INNER JOIN {$dbtp}xsd_display_matchfields x6
                ON r6.rmf_xsdmf_id = x6.xsdmf_id 
                INNER JOIN {$dbtp}search_key s6				  
                ON x6.xsdmf_sek_id = s6.sek_id 
                and  s6.sek_title = 'Object Type' 
               
                ";

        
        $bodyStmtPart1 = "FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x2
                      ON r2.rmf_xsdmf_id = x2.xsdmf_id $joinStmt

                    $authStmt

					$memberOfStmt

                    $objectTypestmt 
                    ";
        $bodyStmt = "$bodyStmtPart1

                    LEFT JOIN {$dbtp}record_matching_field r5 on r5.rmf_rec_pid = r2.rmf_rec_pid
                    inner join {$dbtp}xsd_display_matchfields x5 on r5.rmf_xsdmf_id = x5.xsdmf_id
                    left join {$dbtp}search_key s5
                    on (s5.sek_id = x5.xsdmf_sek_id and s5.sek_title = '$order_by')  
					where (r5.rmf_varchar is null) or s5.sek_title = '$order_by'
					group by r5.rmf_rec_pid
             ";

           
            $stmt = "SELECT r1.*, x1.*, s1.*, k1.*, d1.* 
            FROM {$dbtp}record_matching_field AS r1
            INNER JOIN {$dbtp}xsd_display_matchfields AS x1
            ON r1.rmf_xsdmf_id = x1.xsdmf_id
            INNER JOIN (
                    SELECT distinct r2.rmf_rec_pid, min(r5.rmf_$data_type) as sort_column
                    $bodyStmt
					order by sort_column $order_dir, r2.rmf_rec_pid desc
                    LIMIT $start, $max					
                    ) as display ON display.rmf_rec_pid=r1.rmf_rec_pid 
            LEFT JOIN {$dbtp}xsd_loop_subelement s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN {$dbtp}search_key k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
            LEFT JOIN {$dbtp}xsd_display d1  
            ON (d1.xdis_id = r1.rmf_varchar and k1.sek_title = 'Display Type')
            ORDER BY display.sort_column $order_dir, r1.rmf_rec_pid DESC ";

            Error_Handler::logError($stmt,__FILE__,__LINE__);

            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
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
		// now that there can be multiple different types of 'collection' doc types this should not be used anymore
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

        $order_dir = 'ASC';
		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;

        // this query broken into pieces to try and get some speed.

        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
//        $order_by = 'Title';
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
        $restrict_community = '';

		$authArray = Collection::getAuthIndexStmt(array("Lister", "Viewer", "Editor", "Creator"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];

        if (!empty($collection_pid)) {		
			$memberOfStmt = "
						INNER JOIN {$dbtp}record_matching_field AS r4
						  ON r4.rmf_rec_pid = r2.rmf_rec_pid
						INNER JOIN {$dbtp}xsd_display_matchfields AS x4
						  ON r4.rmf_xsdmf_id = x4.xsdmf_id and match(r4.rmf_varchar) 
                          against ('\"$collection_pid\"' in boolean mode)
						INNER JOIN {$dbtp}search_key AS s4  							  
						  ON s4.sek_id = x4.xsdmf_sek_id AND s4.sek_title = 'isMemberOf' ";
		} else {
			return array();
//			$memberOfStmt = "";
		}

        $bodyStmtPart1 = "FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x2
                      ON r2.rmf_xsdmf_id = x2.xsdmf_id AND match(x2.xsdmf_element) 
                      against ('\"!sta_id\"' in boolean mode) 
                      and r2.rmf_varchar='2' $joinStmt


                    $authStmt

					$memberOfStmt
				

                    ";
        $bodyStmt = "$bodyStmtPart1


                    LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r5 
                    on r5.rmf_rec_pid = r2.rmf_rec_pid
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x5
                    on r5.rmf_xsdmf_id = x5.xsdmf_id
                    left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s5
                    on (s5.sek_id = x5.xsdmf_sek_id and s5.sek_title = '$order_by')  
					where (r5.rmf_varchar is null) or s5.sek_title = '$order_by'
					group by r5.rmf_rec_pid
             ";

        $countStmt = "
                    SELECT count(distinct r2.rmf_rec_pid)
                    $bodyStmtPart1
            ";

        $stmt = "SELECT r1.*, x1.*, s1.*, k1.*, d1.* 
            FROM {$dbtp}record_matching_field AS r1
            INNER JOIN {$dbtp}xsd_display_matchfields AS x1
            ON r1.rmf_xsdmf_id = x1.xsdmf_id
            INNER JOIN (
                    SELECT distinct r2.rmf_rec_pid, min(r5.rmf_$data_type) as sort_column
                    $bodyStmt
					order by sort_column $order_dir, r2.rmf_rec_pid desc
                    LIMIT $start, $max					
                    ) as display ON display.rmf_rec_pid=r1.rmf_rec_pid 
            LEFT JOIN {$dbtp}xsd_loop_subelement s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN {$dbtp}search_key k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
            LEFT JOIN {$dbtp}xsd_display d1  
            ON (d1.xdis_id = r1.rmf_varchar and k1.sek_title = 'Display Type')
            ORDER BY display.sort_column $order_dir, r1.rmf_rec_pid DESC ";
        //echo $stmt; 

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$total_rows = $GLOBALS["db_api"]->dbh->getOne($countStmt);
//		echo $countStmt;
		$return = array();
		$return = Collection::makeReturnList($res);
//		print_r($return);
        $return = Collection::makeSecurityReturnList($return);
//		print_r($return);
		$hidden_rows = 0;
		$return = Auth::getIndexAuthorisationGroups($return);
		$return = Misc::cleanListResults($return);

//		$total_rows = count($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}

		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
//		$return = Misc::limitListResults($return, $start, ($start + $max));		
		// add the available workflow trigger buttons

		foreach ($return as $ret_key => $ret_wf) {
			$pid = $ret_wf['pid'];
			$record = new RecordObject($pid);
            $workflows = array();
			if ($ret_wf['isEditor']) {
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
                    "hidden_rows"     => 0
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
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1 inner join 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on r1.rmf_xsdmf_id = x1.xsdmf_id left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id) left join
 				    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 on (k1.sek_id = x1.xsdmf_sek_id)
				";				
				$stmt .= $middleStmt;
				$stmt .= 
                " WHERE
				    k1.sek_title = 'Subject'
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



	function getAuthIndexStmt($roles = array()) {
		// If the user is a Fez Administrator then don't check for security, give them everything
		$isAdministrator = Auth::isAdministrator();  
		if ($isAdministrator === true) {
			return array('authStmt' => '', 'joinStmt' => ''); // turned off for testing
		}


		$rolesStmt = "";
		if (is_array($roles)) {
			if (count($roles) == 0) {
                $roles = array('Lister','Viewer', 'Editor', 'Creator', 'Approver');
			}
            $rolesStmt = "'".implode("', '", $roles)."'";
		} else {
			return array('authStmt' => '', 'joinStmt' => '');
		}

		$dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
		$authStmt = "";
		$joinStmt = "";
        $usr_id = Auth::getUserID();
		if (is_numeric($usr_id)) {	
            $authStmt .= "INNER JOIN {$dbtp}auth_index2 ai 
                ON authi_role in ($rolesStmt) AND ai.authi_pid = r2.rmf_rec_pid   
                INNER JOIN {$dbtp}auth_rule_group_users 
                ON argu_usr_id='$usr_id' AND ai.authi_arg_id=argu_arg_id ";
			} else {
                $authStmt = " INNER JOIN {$dbtp}auth_index2 ON authi_role='Lister' AND authi_pid=r2.rmf_rec_pid 
                    INNER JOIN {$dbtp}auth_rule_group_rules ON argr_arg_id=authi_arg_id 
                    INNER JOIN {$dbtp}auth_rules ON ar_rule='public_list' AND ar_value='1' AND argr_ar_id=ar_id ";
                $joinStmt .= "";
			}		
			return array('authStmt' => $authStmt, 'joinStmt' => $joinStmt);
	}
    /**
     * Method used to get the list of records in browse view by a browsing category available in the 
     * system.
     *
     * @access  public
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @param   string $searchKey The search key the records are being browsed by eg Subject, Created Date (latest additions).
     * @param   string $getCount If 1 the query will get all the records to it can get a count, otherwise it will be restricted earlier.
     * @return  array The list of records 
     */
    function browseListing($current_row = 0, $max = 25, $searchKey="Subject", $order_by=null, $getCount=1)
    {
//		return array();
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
		$extra_order = "";
		$internal_extra_order = "";
		$termCounter = 5;
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;


		$joinNum = $termCounter + 1;
//		$joinNum = 2;
        $extra = '';
		if ($searchKey == "Subject") {				
			$terms = $_GET['parent_id'];		
			$search_data_type = "varchar";
			$restrictSQL = "AND match(r".$termCounter.".rmf_".$search_data_type.") against ('".$terms."')";
		} elseif ($searchKey == "Created Date") {
			$search_data_type = "date";
			$restrictSQL = "AND DATE_SUB(CURDATE(), INTERVAL 6 DAY) < r".$termCounter.".rmf_".$search_data_type."";
			$extra = ", DAYNAME(display.preorder) as day_name";		
//			$extra_order =  "r".$termCounter.".rmf_".$search_data_type.", ";
			$subqueryExtra = ", r".$termCounter.".rmf_".$search_data_type." as preorder";

			$extra_order =  "date(display.preorder) DESC, ";
            $order_dir = " ASC ";
			$internal_extra_order =  "date(r5.rmf_date) desc, ";
            if ($order_by == 'Created Date') {
/*                $order_dir = " DESC ";
				$internal_extra_order =  "preorder desc, ";*/
				$extra = ", DAYNAME(display.sort_column) as day_name";		
				$extra_order = "";
				$subqueryExtra = "";
				$internal_extra_order = ""; 
				$joinNum = $termCounter;
            } else {

			}
		} elseif ($searchKey == "Date") {
			$search_data_type = "date";
			$subqueryExtra = ", r".$termCounter.".rmf_".$search_data_type;
			$terms = $_GET['year'];
			$restrictSQL = "AND YEAR(r".$termCounter.".rmf_".$search_data_type.") = ".$terms."";
		} elseif ($searchKey == "Author") {
			$search_data_type = "varchar";
			$subqueryExtra = ", r".$termCounter.".rmf_".$search_data_type;
			$terms = str_replace(" ", " +", mysql_escape_string($_GET['author']));
			
			$restrictSQL = "AND match(r".$termCounter.".rmf_".$search_data_type.") against ('\"".$terms."\"' in boolean mode)";
		} else {
			$search_data_type = "varchar";		
			$subqueryExtra = ", r".$termCounter.".rmf_".$search_data_type;

		}
		$authArray = Collection::getAuthIndexStmt();
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];
        if (!empty($authStmt)) {
            $mainJoin = "ai.authi_pid";
        } else {
            $mainJoin = "r2.rmf_rec_pid";
        }


        $middleStmt .= "
		            inner join {$dbtp}record_matching_field r".$termCounter." on r".$termCounter.".rmf_rec_pid = r2.rmf_rec_pid
                    inner join {$dbtp}xsd_display_matchfields x".$termCounter." on r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id 
                    inner join {$dbtp}search_key AS s".$termCounter." on s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id
                    and s".$termCounter.".sek_title = '".$searchKey."' ".$restrictSQL;
		$termCounter++;
/*
					inner join {$dbtp}record_matching_field r3 on r3.rmf_rec_pid = r2.rmf_rec_pid
					inner join {$dbtp}xsd_display_matchfields x3 on r3.rmf_xsdmf_id = x3.xsdmf_id
					inner join {$dbtp}search_key s3 on s3.sek_id = x3.xsdmf_sek_id and  s3.sek_title = '$order_by'
*/

		if ($searchKey == "Created Date") {
			$search_data_type = "date";
		}
		$bodyStmt = " FROM  {$dbtp}record_matching_field r2 
                    inner join {$dbtp}xsd_display_matchfields AS x2 on r2.rmf_xsdmf_id = x2.xsdmf_id and r2.rmf_varchar='2' and x2.xsdmf_element='!sta_id' $joinStmt
					
					";
		
		if	((($searchKey == 'Created Date') && ($order_by != $searchKey)) || ($searchKey != 'Created Date')) {
			$bodyStmt .= "
					inner join  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r".$termCounter." 
					on r".$termCounter.".rmf_rec_pid = r2.rmf_rec_pid 
					inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x".$termCounter."
					on r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id
					inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s".$termCounter."
					on s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id
					and s".$termCounter.".sek_title = '$order_by'  ";
		} else {
			$joinNum = 2;
		}
		$bodyStmt .= "

					$authStmt

	                $middleStmt ";
					

		
		$stmtCount = "SELECT count(distinct r2.rmf_rec_pid) as display_count
					  $bodyStmt";
/*					FROM  {$dbtp}record_matching_field r2 
					inner join {$dbtp}xsd_display_matchfields x2 on r2.rmf_xsdmf_id = x2.xsdmf_id $joinStmt
					inner join {$dbtp}search_key s2 on (s2.sek_id = x2.xsdmf_sek_id AND s2.sek_title = 'Display Type')

					$authStmt

	                $middleStmt
					
					inner join {$dbtp}record_matching_field AS r4 on r4.rmf_rec_pid = r2.rmf_rec_pid
                    inner join {$dbtp}xsd_display_matchfields AS x4 on r4.rmf_xsdmf_id = x4.xsdmf_id and r4.rmf_varchar='2' and x4.xsdmf_element='!sta_id'";
*/
//		echo $stmtCount;
		$res = $GLOBALS["db_api"]->dbh->getOne($stmtCount);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        $total_rows = $res;			
			
			
        $stmt = "SELECT  r1.*, x1.*, s1.*, k1.*, d1.*  ".$extra."
                 FROM {$dbtp}record_matching_field r1
                 INNER JOIN {$dbtp}xsd_display_matchfields x1 
                 ON r1.rmf_xsdmf_id = x1.xsdmf_id 
				 INNER JOIN (
					 SELECT  distinct r2.rmf_rec_pid, r".$joinNum.".rmf_$data_type as sort_column $subqueryExtra
					$bodyStmt
					order by $internal_extra_order sort_column $order_dir, r2.rmf_rec_pid desc
					
					";
//					if ($getCount == 0) {
						$stmt .= " limit $start, $max ";
//					}
					$stmt .=
					 "


				) as display on display.rmf_rec_pid = r1.rmf_rec_pid

                 LEFT JOIN {$dbtp}xsd_loop_subelement s1 
                 ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
                 LEFT JOIN {$dbtp}search_key k1 
                 ON (k1.sek_id = x1.xsdmf_sek_id)
				LEFT JOIN {$dbtp}xsd_display d1  
				ON (d1.xdis_id = r1.rmf_varchar and k1.sek_title = 'Display Type')
                ORDER BY $extra_order display.sort_column $order_dir, r1.rmf_rec_pid DESC ";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);

//		echo $stmt; //return array();
		$securityfields = Auth::getAllRoles();
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {

			$return = array();
			$return = Collection::makeReturnList($res);

            $return = Collection::makeSecurityReturnList($return);
//			$hidden_rows = count($return);
			$hidden_rows = $total_rows;
			$return = Auth::getIndexAuthorisationGroups($return);
			$return = Misc::cleanListResults($return);
//			print_r($return);
//			$total_rows = count($return);
			if (($start + $max) < $total_rows) {
				$total_rows_limit = $start + $max;
			} else {
			   $total_rows_limit = $total_rows;
			}
			$total_pages = ceil($total_rows / $max);
			$last_page = $total_pages - 1;
//			$return = Misc::limitListResults($return, $start, ($start + $max));
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
			$group_field = "year(r2.rmf_".$search_data_type.")";
			$as_field = "record_year";
		} elseif ($searchKey == "Author") {
			$search_data_type = "varchar";
			$group_field = "(r2.rmf_".$search_data_type.")";
			$as_field = "record_author";
		} else {
			$search_data_type = "varchar";
			$group_field = "(r2.rmf_".$search_data_type.")";		
			$as_field = "record_author";
		}
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
		$middleStmt .= 
		" INNER JOIN 
				  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS r".$termCounter." on  r".$termCounter.".rmf_id = r1.rmf_id
                INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS x".$termCounter."
                ON r".$termCounter.".rmf_xsdmf_id = x".$termCounter.".xsdmf_id
                INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key AS s".$termCounter."  
                ON s".$termCounter.".sek_id = x".$termCounter.".xsdmf_sek_id
				AND s".$termCounter.".sek_title = '".$searchKey."' ".$restrictSQL."
		";
        $stmt = "SELECT
                    count(*) as record_count, ".$group_field." as ".$as_field."
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS r1
                    INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS x1
                    ON r1.rmf_xsdmf_id = x1.xsdmf_id 
				$middleStmt
                  INNER JOIN

                         {$dbtp}record_matching_field AS r3 on r3.rmf_rec_pid = r2.rmf_rec_pid
                         INNER JOIN {$dbtp}xsd_display_matchfields AS x3
                         ON r3.rmf_xsdmf_id = x3.xsdmf_id
                         and r3.rmf_varchar='2'
                         AND match(x3.xsdmf_element) against ('\"!sta_id\"' in boolean mode)



                        

				 GROUP BY
				 	".$group_field."
				 ORDER BY
				 	".$group_field;
//		echo $stmt;
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
    function statsByAttribute($current_row = 0, $max = 50, $searchKey="Author")
    {

		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;
		$restrictSQL = "";
		$middleStmt = "";
		$extra = "";
		$termCounter = 3;
		$as_field = "";
		if ($searchKey == "Title") {
			$data_type = "varchar";
			$extra = ", r2.rmf_rec_pid ";
			$group_field = "r4.rmf_".$data_type.", r2.rmf_rec_pid";
		} elseif ($searchKey == "Author") {
			$data_type = "varchar";
			$group_field = "(r4.rmf_".$data_type.")";
			$as_field = "record_author";
		} else {
			$data_type = "varchar";
			$group_field = "(r4.rmf_".$data_type.")";		
		}

		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;

        // this query broken into pieces to try and get some speed.

        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $order_by = 'File Downloads';
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
        $restrict_community = '';

		$authArray = Collection::getAuthIndexStmt(array("Lister", "Viewer", "Editor", "Creator"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];
		$order_dir = "DESC";


			$memberOfStmt = "
						INNER JOIN {$dbtp}record_matching_field AS r4
						  ON r4.rmf_rec_pid = r2.rmf_rec_pid
						INNER JOIN {$dbtp}xsd_display_matchfields AS x4
						  ON r4.rmf_xsdmf_id = x4.xsdmf_id 
						INNER JOIN {$dbtp}search_key AS s4  							  
						  ON s4.sek_id = x4.xsdmf_sek_id AND s4.sek_title = '$searchKey' ";


        $bodyStmtPart1 = "FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x2
                      ON r2.rmf_xsdmf_id = x2.xsdmf_id AND x2.xsdmf_element='!sta_id' and r2.rmf_varchar='2'


                    $authStmt

					$memberOfStmt
				

                    ";
        $bodyStmt = "$bodyStmtPart1
                  
                    LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r5
                    ON r5.rmf_rec_pid=r2.rmf_rec_pid
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x5
                    on r5.rmf_xsdmf_id = x5.xsdmf_id
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s5
                    on s5.sek_id = x5.xsdmf_sek_id AND s5.sek_title = 'File Downloads'
                    group by $group_field
             ";

        $countStmt = "
                    SELECT count(distinct r2.rmf_rec_pid)
                    $bodyStmtPart1
            ";
	
		$innerStmt = "
                    SELECT distinct r4.rmf_varchar $as_field $extra, IFNULL(sum(r5.rmf_$data_type),0) as sort_column
                    $bodyStmt
					order by sort_column $order_dir, r2.rmf_rec_pid desc
                    LIMIT $start, $max					
					";
		if ($searchKey == "Title") {
			$stmt = "SELECT display.sort_column, r1.*, x1.*, s1.*, k1.*, d1.* 
				FROM {$dbtp}record_matching_field AS r1
				INNER JOIN {$dbtp}xsd_display_matchfields AS x1
				ON r1.rmf_xsdmf_id = x1.xsdmf_id
				INNER JOIN (
							$innerStmt
						) as display ON display.rmf_rec_pid=r1.rmf_rec_pid 
				LEFT JOIN {$dbtp}xsd_loop_subelement s1 
				ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
				LEFT JOIN {$dbtp}search_key k1 
				ON (k1.sek_id = x1.xsdmf_sek_id)
				LEFT JOIN {$dbtp}xsd_display d1  
				ON (d1.xdis_id = r1.rmf_varchar and k1.sek_title = 'Display Type')
				ORDER BY display.sort_column $order_dir, r1.rmf_rec_pid DESC ";
		} else {
			$stmt = $innerStmt;
		}
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);

		if ($searchKey != "Title") {
			return array(
                "list" => $res,
                "info" => array()
				);
		}

		$total_rows = $GLOBALS["db_api"]->dbh->getOne($countStmt);

		$return = array();
		$return = Collection::makeReturnList($res);
        $return = Collection::makeSecurityReturnList($return);
		$hidden_rows = 0;
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
		foreach ($return as $ret_key => $ret_wf) {
			$pid = $ret_wf['pid'];
			$record = new RecordObject($pid);
            $workflows = array();
			if ($ret_wf['isEditor']) {
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
								 AND match (r{$termCounter}.rmf_varchar) against ('*{$tsubdata}*' IN BOOLEAN MODE) 
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
						 AND match (r{$termCounter}.rmf_varchar) against ('*{$tdata}*' IN BOOLEAN MODE)
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
    function searchListing($terms, $current_row = 0, $max = 25, $order_by='Relevance')
    {
		if (empty($terms)) {
			return array();
		}
        if (empty($order_by)) {
            $order_by = $searchKey;
            if (empty($order_by)) {
                $order_by = 'Relevance';
            }
        }

		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;

		$authArray = Collection::getAuthIndexStmt();
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];
		$termCounter = 100;
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];

//		$termArray = explode(" ", $terms);
//        $termLike = '';
/*		foreach ($termArray as $key => $data) {
			if ($termLike == "") {
				$termLike = "(";
			} else {
				$termLike .= " OR ";
			}
			$termLike .= " match (r2.rmf_varchar) against ('*".$data."*' IN BOOLEAN MODE) ";
			
		}
		$termLike .= ") ";*/
		$termLike = " match (r2.rmf_varchar) against ('*".$terms."*' IN BOOLEAN MODE) ";
		$termRelevance = " match (r2.rmf_varchar) against ('".$terms."') as Relevance";
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;


        $bodyStmtPart1 = "FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x2
                      ON r2.rmf_xsdmf_id = x2.xsdmf_id $joinStmt AND $termLike 
                    INNER JOIN {$dbtp}search_key AS s2  							  
                      ON s2.sek_id = x2.xsdmf_sek_id AND s2.sek_simple_used = 1

                    $authStmt

                    INNER JOIN {$dbtp}record_matching_field AS r4
                      ON r4.rmf_rec_pid=r2.rmf_rec_pid AND r4.rmf_varchar='2'
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x4
                      ON r4.rmf_xsdmf_id = x4.xsdmf_id AND x4.xsdmf_element='!sta_id'

                    ";

		$sortColumn = "";
		if ($order_by == "Relevance") {
	        $order_dir = ' desc ';
			$orderRelevance = " Relevance DESC ";
			$bodyStmt = $bodyStmtPart1;
			$sortColumn = "";
			$sortBy = "";			
			$sortFinal = "display.Relevance".$order_dir;
		} else {
			$sortColumn = " r5.rmf_$data_type as sort_column,";
	        $order_dir = ' asc ';
			$sortBy = " sort_column ".$order_dir;
			$sortFinal = "display.sort_column".$order_dir;

			$bodyStmt = "$bodyStmtPart1
					  
						LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r5
						ON r5.rmf_rec_pid=r4.rmf_rec_pid
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x5
						on r5.rmf_xsdmf_id = x5.xsdmf_id
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s5
						on s5.sek_id = x5.xsdmf_sek_id AND s5.sek_title = '$order_by'
						
				 ";
		}
        $countStmt = "
                    SELECT count(distinct r2.rmf_rec_pid)
                    $bodyStmtPart1
            ";
        //echo $countStmt;

        
        $stmt = "SELECT  r1.*, x1.*, s1.*, k1.*, d1.*, display.Relevance
            FROM {$dbtp}record_matching_field AS r1
            INNER JOIN {$dbtp}xsd_display_matchfields AS x1
            ON r1.rmf_xsdmf_id = x1.xsdmf_id
            INNER JOIN (
                    SELECT distinct r2.rmf_rec_pid, $sortColumn $termRelevance
                    $bodyStmt
					order by $orderRelevance $sortBy 
                    LIMIT $start, $max
                    ) as display ON display.rmf_rec_pid=r1.rmf_rec_pid 
            LEFT JOIN {$dbtp}xsd_loop_subelement s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN {$dbtp}search_key k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
            LEFT JOIN {$dbtp}xsd_display d1  
            ON (d1.xdis_id = r1.rmf_varchar and k1.sek_title = 'Display Type')
            ORDER BY $sortFinal ";
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
			if (!empty($result['Relevance']) && empty($return[$result['rmf_rec_pid']]['Relevance'])) {
                $return[$result['rmf_rec_pid']]['Relevance'] = round($result['Relevance'], 2);
			}
			if ($result['sek_title'] == 'isMemberOf') {
                $return[$result['rmf_rec_pid']]['isMemberOf'][] = $result['rmf_varchar'];
			}
			// get the document type
			if (!empty($result['xdis_title'])) {
                if (!is_array(@$return[$result['rmf_rec_pid']]['xdis_title'])) {
                    $return[$result['rmf_rec_pid']]['xdis_title'] = array();
                }
                if (!in_array($result['xdis_title'], $return[$result['rmf_rec_pid']]['xdis_title'])) {
                    array_push($return[$result['rmf_rec_pid']]['xdis_title'], $result['xdis_title']);
                }
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
		$return = Auth::getIndexAuthorisationGroups($return);
		$return = Misc::cleanListResults($return);

		$total_rows = $GLOBALS["db_api"]->dbh->getOne($countStmt);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		//$return = Misc::limitListResults($return, $start, ($start + $max));
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
                    "hidden_rows"     => 0
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
