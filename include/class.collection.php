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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
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
include_once(APP_INC_PATH . "class.group.php");
include_once(APP_INC_PATH . "class.statistics.php");
include_once(APP_INC_PATH . "class.fulltext_index.php");

class Collection
{


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
	 * List the collections in a community that can be edited by the current user
	 * - mainly used in NAJAX drop down lists of collections from my fez
	 * @param integer $community_pid The pid of the community to restrict the list to
	 * @return array Associative array of collections - (pid, title)
	 */
	function getEditListAssoc($community_pid=null)
	{
		$roles = explode(',',APP_EDITOR_ROLES);
		$options = array();
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 2; // collections only
		if (!empty($community_pid)) {
			$options["searchKey".Search_Key::getID("isMemberOf")] = $community_pid; //
		}
		$list = Record::getListing($options, $roles, 0, 1000, "Title", true);
		$list = $list['list'];
		$returnList = array();
		foreach ($list as $element) {
			$returnList[$element['rek_pid']] = $element['rek_title'];
		}
		return $returnList;
	}

	function getCreatorListAssoc($community_pid=null)
	{
		$options = array();
		$roles = explode(',',APP_CREATOR_ROLES);
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 2; // collections only
		if (!empty($community_pid)) {
			$options["searchKey".Search_Key::getID("isMemberOf")] = $community_pid; //
		}
		$list = Record::getListing($options, $roles, 0, 1000, "Title", true);
		$list = $list['list'];
		$returnList = array();
		foreach ($list as $element) {
			$returnList[$element['rek_pid']] = $element['rek_title'];
		}
		return $returnList;
	}

	/**
	 * List the collections in a community that can be edited by the current user
	 * - mainly used in NAJAX drop down lists of collections from my fez
	 * @param integer $community_pid The pid of the community to restrict the list to
	 * @return array Associative array of collections - (pid, title)
	 */
	function getEditList($community_pid=null, $roles = array("Creator", "Editor", "Approver", "Community_Administrator"))
	{
		// get list of collections that
		// parent is community_pid
		// has ACMLs set
		//     AND user is in the roles for the ACML (group, user, combos)
		// OR parents of the collection have ACML set
		//     AND user is in the roles for the ACML
		$options = array();
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 2; // collections only
		if (!empty($community_pid)) {
			$options["searchKey".Search_Key::getID("isMemberOf")] = $community_pid; //
		}
		$list = Record::getListing($options, $roles, 0, 10000);
		return $list;


	}

	/**
	 * Search collection titles for possible matches to the search term where the
	 * current user has create rights.
	 */
	function suggestCreateList($search)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$roles = explode(',',APP_CREATOR_ROLES);
		$dbtp =  APP_TABLE_PREFIX;
		$authArray = Collection::getAuthIndexStmt($roles);
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];
		$stmt = "SELECT r2.rek_pid, r2.rek_title FROM ".$dbtp."record_search_key AS r2 " .
		$authStmt."WHERE rek_title LIKE ".$db->quote($search."%")." AND rek_object_type = 2";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	/**
	 * List the collections in a community
	 * @param integer $community_pid The pid of the community to restrict the list to
	 * @return array Associative array of collections - (pid, title)
	 */
	function getCommunityAssocList($community_pid=null)
	{
		// get list of collections that
		// parent is community_pid
		// has ACMLs set
		//     AND user is in the roles for the ACML (group, user, combos)
		// OR parents of the collection have ACML set
		//     AND user is in the roles for the ACML
		//        $returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id");
		//        $returnfield_query = Misc::array_to_sql_string($returnfields);
		$roles = array("Editor", "Approver");
		$options = array();
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("isMemberOf")] = $community_pid; //
		$list = Record::getListing($options, $roles, 0, 1000, "Title", true);
		$list = $list['list'];
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
		$log = FezLog::get();
		$db = DB_API::get();

		// get the count of everything in the tree, but the display will only show what is need at each branch
		if (empty($treeIDs)) {
			return array();
		}
		$termCounter = 2;
		$dbtp =  APP_TABLE_PREFIX;
		$subjectList = XSD_HTML_Match::getXSDMF_IDsBySekTitle('Subject');
		$authArray = Collection::getAuthIndexStmt(array("Lister", "Viewer", "Editor", "Creator"), "r2.rek_subject_pid");
		$authStmt = $authArray['authStmt'];
		$stringIDs = Misc::array_flatten($treeIDs);
		$stmt = "SELECT ".APP_SQL_CACHE." r".$termCounter.".rek_subject, count(distinct r".$termCounter.".rek_subject_pid)
				FROM  ".$dbtp."record_search_key_subject r".$termCounter."
                INNER JOIN ".$dbtp."record_search_key r1 on r1.rek_pid = r2.rek_subject_pid and r1.rek_status = 2
                ".$authStmt."
				WHERE r".$termCounter.".rek_subject IN (".Misc::arrayToSQLBindStr($stringIDs).")
                GROUP BY r".$termCounter.".rek_subject ";

		try {
			$res = $db->fetchPairs($stmt, array_values($stringIDs));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

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
	function fillTree(&$res, $treeIDs, $key, $data)
	{
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



	function getAuthIndexStmt($roles = array(), $joinPrefix="r2.rek_pid")
	{
		$db = DB_API::get();
		$log = FezLog::get();
		
		// If the user is a Fez Administrator then don't check for security, give them everything
		$isAdministrator = Auth::isAdministrator();
		if ($isAdministrator === true) {
			return array('authStmt' => '', 'joinStmt' => ''); // turned off for testing
		}

		$rolesStmt = "";
		if (is_array($roles)) {
			if (count($roles) == 0) {
				//$roles = array('Lister','Viewer', 'Editor', 'Creator', 'Approver');
				//                $roles = array(9, 10, 2, 7, 8);
				$roles = array(9);
			}
			foreach ($roles as $role) {
				if (is_numeric($role)) {
					$rolesStmt .= $role.",";
				} else {
					$roleID = Auth::getRoleIDByTitle($role);
					if (is_numeric($roleID)) {
						$rolesStmt .= $roleID.",";
					}
				}
			}
			$rolesStmt = rtrim($rolesStmt, ",");
		} else {
			return array('authStmt' => '', 'joinStmt' => '');
		}
		$lister_only = false;
		if (count($roles) == 1 && $roles[0] == 9) { //If lister only use the faster lister only auth index for the inner joins
			$lister_only = true;
		}
		$dbtp =  APP_TABLE_PREFIX;
		$authStmt = "";
		$joinStmt = "";
		$usr_id = Auth::getUserID();

		if (is_numeric($usr_id)) {
			if (!$auth_isBGP) {
				$ses = &Auth::getSession();
			}
			if (is_array($ses['auth_index_user_rule_groups'])) {
				if (count($ses['auth_index_user_rule_groups']) > 0) {
					if (!$lister_only) {
						$authStmt .= " INNER JOIN ".$dbtp."auth_index2 ai
				                	ON authi_role in (".$rolesStmt.") AND ai.authi_pid = ".$joinPrefix." AND ai.authi_arg_id in (".implode(",",$ses['auth_index_user_rule_groups']).")";
					} else {
						$authStmt .= " INNER JOIN ".$dbtp."auth_index2_lister ai
				                	ON ai.authi_pid = ".$joinPrefix." AND ai.authi_arg_id in (".implode(",",$ses['auth_index_user_rule_groups']).")";					
					}
				}
			} else {
				if ($lister_only) {
					$authStmt .= " INNER JOIN ".$dbtp."auth_index2_lister ai
				                ON ai.authi_pid = ".$joinPrefix."
				                INNER JOIN ".$dbtp."auth_rule_group_users
				                ON argu_usr_id=".$db->quote($usr_id, 'INTEGER')." AND ai.authi_arg_id=argu_arg_id ";
				} else {
					$authStmt .= " INNER JOIN ".$dbtp."auth_index2 ai
				                ON authi_role in (".$rolesStmt.") AND ai.authi_pid = ".$joinPrefix."
				                INNER JOIN ".$dbtp."auth_rule_group_users
				                ON argu_usr_id=".$db->quote($usr_id, 'INTEGER')." AND ai.authi_arg_id=argu_arg_id ";					
				}
			}
			//            $authStmt .= "
			//                and ai.authi_pid = ".$joinPrefix;
		} else {
			$publicGroups = Collection::getPublicAuthIndexGroups();
			if (is_array($publicGroups)) { // to catch when the fez index/auth index is empty
				if (count($publicGroups) > 0) {
					$authStmt = " INNER JOIN ".$dbtp."auth_index2_lister ON authi_pid=".$joinPrefix." and authi_arg_id in (".implode(",", $publicGroups).")";
				}
			}
			$joinStmt .= "";
		}
		return array('authStmt' => $authStmt, 'joinStmt' => $joinStmt);
	}

	function getPublicAuthIndexGroups()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "SELECT distinct argr_arg_id
    	         FROM ".$dbtp."auth_rule_group_rules
                 INNER JOIN ".$dbtp."auth_rules ON ar_rule='public_list' 
                            AND ar_value='1' 
                            AND argr_ar_id=ar_id ";
			
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex->getMessage());
			return array();
		}
		return $res;
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
	function listByAuthor($current_row = 0, $max = 25,  $sort_by = "", $letter = "")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if ($max == "ALL") {
			$max = 9999999;
		}
		$start = $current_row * $max;
		$data_type = $sekdet['xsdmf_data_type'];
		$authArray = Collection::getAuthIndexStmt();
		$authStmt = $authArray['authStmt'];
		$sekdet = Search_Key::getBasicDetailsByTitle("Author ID");
		$middleStmt = "";
		$order_field = "";
		$termCounter = 3;
		$extra_join = "";
		$letter_restrict = "";
		$show_field = "";
		$dbtp =  APP_TABLE_PREFIX;
		$sql_where = "";
		$search_data_type = "int";
		$group_field_id = " r3.rek_author_id, a1.aut_display_name, a1.aut_id";
		$group_field = "r3.rek_author_id, a1.aut_display_name, a1.aut_id";
		$as_field = "record_author";
		$extra_join .= "INNER JOIN ".$dbtp."author a1 on r3.rek_author_id = a1.aut_id ";
		$show_field = "r3.rek_author_id";
		$show_field_id1 = "a1.aut_fname";
		$show_field_id2 = "a1.aut_lname";
		$order_field = " a1.aut_lname ASC, a1.aut_fname ASC ";

		if ($show_field == "") {
			$show_field = $group_field;
		}
		if (!empty($letter)) {
			$letter = addslashes($letter);
			$letter_restrict = "WHERE (r3.rek_author LIKE " . $db->quote($letter.'%') . " OR r3.rek_author LIKE " . $db->quote(strtolower($letter).'%') . ") and ";
			$letter_restrict_id = "WHERE (a1.aut_lname LIKE " . $db->quote($letter.'%') . " OR a1.aut_lname LIKE " . $db->quote(strtolower($letter).'%') . ") ";
		} else {
			$sql_where = " ";
			$sql_where_id = " ";
		}

		$middleStmt .= " " . APP_TABLE_PREFIX . "record_search_key_author_id AS r".$termCounter." ".$authStmt." ";
		$middleStmt_id .= " " . APP_TABLE_PREFIX . "record_search_key_author_id AS r".$termCounter." ";
		$countStmt = " FROM ".$middleStmt_id."
						INNER JOIN
                        ".$dbtp."record_search_key AS r2 ON r2.rek_pid = r3.rek_author_id_pid
                        AND r2.rek_status = 2
                        $authStmt ".$extra_join." ".$letter_restrict_id." ".$sql_where_id." ";
    	$stmt .= " SELECT ".APP_SQL_CACHE."
	                    COUNT(*) AS record_count, CONCAT(" . $show_field_id2 . ",', '," . $show_field_id1 . ") AS ".$as_field.", a1.aut_id AS record_author_id ".$countStmt;

       	$countStmt = " SELECT ".APP_SQL_CACHE." COUNT(DISTINCT a1.aut_id) ".$countStmt;
                        $stmt .= " GROUP BY	".$group_field_id."
				 		ORDER BY record_author ASC
				 		LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			$total_rows = $db->fetchOne($countStmt);
			 
			$total_rows = $total_rows;
			foreach ($res as $key => $row) {
				if (trim($row[$as_field]) != "") {
					if ($searchKey == "Depositor") {
						$return[$key]['record_desc'] = $row['fullname'];
					}
					$return[$key][$as_field] = $row[$as_field];
					$return[$key]['record_count'] = $row['record_count'];
					$return[$key]['record_author_id'] = $row['record_author_id'];
				}
	        }
	        
	        if (($start + $max) < $total_rows) {
	        	$total_rows_limit = $start + $max;
	        } else {
	        	$total_rows_limit = $total_rows;
	        }
	        
	        $total_pages = ceil($total_rows / $max);
	        $last_page = $total_pages - 1;
	        if (($current_row - 10) > 0) {
	        	$start_range = $current_row - 10;
	        } else {
	        	$start_range = 0;
	        }
	        
	        if (($current_row + 10) >= $last_page) {
	        	$end_range = $last_page + 1;
	        } else {
	        	$end_range = $current_row + 10;
	        }
	        
	        $printable_page = $current_row + 1;
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}		                        
       	return array(
                "list" => $return,
                "info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $total_rows_limit,
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "prev_page"     => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page,
                    "hidden_rows"   => $hidden_rows - $total_rows,
                    "start_range"   => $start_range,
                    "end_range"     => $end_range,
                    "printable_page"=> $printable_page
                        	)
                        	);
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
	function listByAttribute($current_row = 0, $max = 25, $searchKey = "Date", $sort_by = "Title", $letter = "")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if ($max == "ALL") {
			$max = 9999999;
		}
		$start = $current_row * $max;
		$sekdet = Search_Key::getDetailsByTitle($sort_by);
		$data_type = $sekdet['xsdmf_data_type'];
		$sekdet = Search_Key::getBasicDetailsByTitle($searchKey);
		$middleStmt = "";
		$order_field = "";
		$termCounter = 1;
		$stmtCount = "";
		$extra_join = "";
		$letter_restrict = "";
		$show_field = "";
		$dbtp =  APP_TABLE_PREFIX;
		$authArray = Collection::getAuthIndexStmt(array("Lister"), "r1.rek_pid");
		$authStmt = $authArray['authStmt'];

		if ($sekdet['sek_relationship'] == 1) {
			$middleStmt .= " FROM " . APP_TABLE_PREFIX . "record_search_key AS r".$termCounter." ";			
			$termCounter++;
			$middleStmt .= " INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_".$sekdet['sek_title_db']." AS r".$termCounter." 
			  					ON r1.rek_pid = r".$termCounter.".rek_".$sekdet['sek_title_db']."_pid";
			$tid = $termCounter;
		} else {
			$middleStmt .= " FROM " . APP_TABLE_PREFIX . "record_search_key AS r".$termCounter." ";
			$tid = 1;
		}

		if ($searchKey == "Subject") {
			$terms = $_GET['parent_id'];
			$search_data_type = "varchar";
		} elseif ($searchKey == "Date") {
			$search_data_type = "date";
			$group_field = "year(r".$tid.".rek_".$sekdet['sek_title_db'].")";
			$order_field = $group_field." DESC";
			$as_field = "record_year";
		} elseif ($searchKey == "Author ID") {
			$search_data_type = "int";
			$group_field = "r".$tid.".rek_".$sekdet['sek_title_db'];
			$as_field = "record_author";
			$extra_join .= "INNER JOIN ".$dbtp."author a1 on r2.rek_".$search_data_type." = a1.aut_id ";
			$show_field = "a1.aut_display_name";
			$order_field = " a1.aut_lname asc, a1.aut_fname asc ";
		} elseif ($searchKey == "Author") {
			$search_data_type = "varchar";
			$group_field = "r".$tid.".rek_".$sekdet['sek_title_db'];
			$as_field = "record_author";

			$stmtCount = "SELECT ".APP_SQL_CACHE." count(r2.rek_author) AS count_record_author
              FROM fez_record_search_key_author AS r2 ";
			if (!empty($letter)) {
				$letter = addslashes($letter);
				$letter_restrict = "WHERE r2.rek_".$sekdet['sek_title_db']." LIKE " . $db->quote($letter.'%') . " OR r".$tid.".rek_".$sekdet['sek_title_db']." LIKE " . $db->quote(strtolower($letter).'%') . " ";
				$stmtCount .= $letter_restrict;
			}

			$stmtCount .= "group by r2.rek_author";

		} elseif ($searchKey == "Depositor") {
			$search_data_type = "int";
			$group_field = "r".$tid.".rek_".$sekdet['sek_title_db'];
			$show_field = "u.usr_full_name as fullname, ".$group_field;
			$order_field = " u.usr_full_name asc";
			$as_field = "record_depositor";
			$extra_join = "LEFT JOIN " . APP_TABLE_PREFIX . "user u ON u.usr_id = r".$tid.".rek_".$sekdet['sek_title_db'];
		} else {
			$sdet = Search_Key::getDetailsByTitle($searchKey);
			$search_data_type =  "r".$tid.".rek_".$sekdet['sek_title_db'];
			$group_field = "r".$tid.".rek_".$sekdet['sek_title_db'];
			$as_field = "record_author";
		}
		if ($show_field == "") {
			$show_field = $group_field;
		}
		if ($show_field)
		if (!empty($letter)) {
			$letter = addslashes($letter);
			$letter_restrict = "WHERE r".$tid.".rek_".$sekdet['sek_title_db']." LIKE " . $db->quote($letter.'%') . " OR r".$tid.".rek_".$sekdet['sek_title_db']." LIKE " . $db->quote(strtolower($letter).'%') . " and r1.rek_status = 2";
		}
		$middleStmt .= $authStmt." ";

		try {
			if ($stmtCount == "") {
				$stmtCount = "SELECT ".APP_SQL_CACHE."
		 				count(distinct ".$show_field.") AS count_".$as_field."
						".$middleStmt."
						".$extra_join."
		                ".$letter_restrict;				
				$total_rows = $db->fetchOne($stmtCount);
			} else { //group by is a lot faster for rek_author than count(distinct 16ms compared to 828ms)\
				$total_rows = $db->fetchOne($stmtCount);
				$total_rows = count($total_rows);
			}
		}
		catch(Exception $ex) {
			$log->err($ex);
		}

		$stmt = "SELECT ".APP_SQL_CACHE."
                    COUNT(*) as record_count, ".$show_field." AS ".$as_field."
				".$middleStmt."
				".$extra_join."
                ".$letter_restrict."
				 GROUP BY
				 	".$group_field."
				 ORDER BY ";
		if ($order_field != "") {
			$stmt .= $order_field;
		} else {
			$stmt .= $group_field;
		}
		
		try {
			$stmt = $db->limit($stmt, $max, $start);
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		foreach ($res as $key => $row) {
			if (trim($row[$as_field]) != "") {
				if ($searchKey == "Depositor") {
					$return[$key]['record_desc'] = $row['fullname'];
				}
				$return[$key][$as_field] = $row[$as_field];
				$return[$key]['record_count'] = $row['record_count'];
			}
		}
		
		$total_pages = ceil($total_rows / $max);
		$last_page = $total_pages - 1;
		if (($current_row - 10) > 0) {
			$start_range = $current_row - 10;
		} else {
			$start_range = 0;
		}
		if (($current_row + 10) >= $last_page) {
			$end_range = $last_page + 1;
		} else {
			$end_range = $current_row + 10;
		}
		$printable_page = $current_row + 1;

		return array(
                "list" => $return,
                "info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $total_rows_limit,
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page,
                    "hidden_rows"     => $hidden_rows - $total_rows,
                    "start_range"   => $start_range,
                    "end_range"     => $end_range,
                    "printable_page"=> $printable_page,
                    "page_rows"     =>  $max,
		)
		);	
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
	function statsByAttribute($current_row = 0, $max = 50, $searchKey="Author", $year = "all", $month = "all", $range = "all")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$dbtp =  APP_TABLE_PREFIX;
		$extra_join = "";
		$limit = "";
		$count_sql = "SUM(r2.rek_file_downloads)";
		if ($year != 'all' && is_numeric($year)) {			
			$limit = " and year(date(stl_request_date)) = ".$db->quote($year);
			if ($month != 'all' && is_numeric($month)) {
				$limit .= " and month(date(stl_request_date)) = ".$db->quote($month, 'INTEGER');
			}
			$extra_join = "INNER JOIN ".$dbtp."statistics_all ON stl_pid = r2.rek_pid AND stl_dsid <> '' AND stl_counter_bad = 0";
			$count_sql = "COUNT(stl_pid)";
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
			$extra_join = "INNER JOIN ".$dbtp."statistics_all ON stl_pid = r2.rek_pid AND stl_dsid <> '' AND stl_counter_bad = 0";
			$count_sql = "COUNT(stl_pid)";
		}

		if ($max == "ALL") {
			$max = 9999999;
		}
		$start = $current_row * $max;
		$restrictSQL = "";
		$middleStmt = "";
		$extra = "";
		$termCounter = 3;
		$as_field = "";
		$sekdet = Search_Key::getBasicDetailsByTitle($searchKey);
		$group_field = ".rek_".$sekdet['sek_title_db'];

		if ($max == "ALL") {
			$max = 9999999;
		}
		$start = $current_row * $max;

		// this query broken into pieces to try and get some speed.

		$sort_by = 'File Downloads';
		$sekdet = Search_Key::getBasicDetailsByTitle($searchKey);
		//        $data_type = $sekdet['xsdmf_data_type'];
		$restrict_community = '';

		$authArray = Collection::getAuthIndexStmt(array("Lister", "Viewer", "Editor", "Creator"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];
		$sort_order = "DESC";

		if ($sdet['sek_relationship'] == 1) {
			$memberOfStmt = " INNER JOIN ".$dbtp."record_search_key_".$sekdet['sek_title_db']." AS r4 ";
			$group_field = $sekdet['sek_title_db'];
		} else {
			$group_field = $sekdet['sek_title_db'];
		}
		if ($searchKey == "Title") {
			$group_field = $sekdet['sek_title_db'].", rek_pid";
			$extra = ", rek_pid";
		}
		$bodyStmtPart1 = " FROM  ".$dbtp."record_search_key AS r2                       
					".$extra_join."
                    ".$authStmt."
					".$memberOfStmt." ";
		$bodyStmt = $bodyStmtPart1." ".$limit." WHERE r2.rek_status=2 GROUP BY rek_".$group_field." ";
		
		$innerStmt = "
                    SELECT ".APP_SQL_CACHE."  r2.*, ".$count_sql." AS sort_column
                    ".$bodyStmt."
					ORDER BY sort_column ".$sort_order."
                    LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER')." ";
		$stmt = $innerStmt;
				
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		foreach ($res as $key => $result) {
			$res[$key]['rek_file_downloads'] = $res[$key]['sort_column'];
		}
		Record::getSearchKeysByPIDS($res);
		return array(
                "list" => $res,
                "info" => array()
		);		
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
	function statsByAuthorID($current_row = 0, $max = 50, $searchKey="Author ID", $year = "all", $month = "all", $range = "all")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$limit = "";
		if ($year != 'all' && is_numeric($year)) {			
			$limit = " and year(date(stl_request_date)) = ".$db->quote($year);
			if ($month != 'all' && is_numeric($month)) {
				$limit .= " and month(date(stl_request_date)) = ".$db->quote($month, 'INTEGER');
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
		}

		if ($max == "ALL") {
			$max = 9999999;
		}
		$start = $current_row * $max;
		$restrictSQL = "";
		$termCounter = 3;
		$as_field = "";
		$sdet = Search_Key::getDetailsByTitle($searchKey);

		if ($max == "ALL") {
			$max = 9999999;
		}
		$start = $current_row * $max;

		$dbtp =  APP_TABLE_PREFIX;
		$sort_by = 'File Downloads';
		$sekdet = Search_Key::getDetailsByTitle($sort_by);
		$restrict_community = '';

		$stmt = " SELECT ".APP_SQL_CACHE." r4.rek_author_id, a1.aut_display_name as record_author, SUM(rek_file_downloads) as sort_column
                  FROM ".$dbtp."record_search_key AS r2 
                  INNER JOIN ".$dbtp."record_search_key_author_id AS r4 ON r4.rek_author_id_pid = r2.rek_pid					
                  INNER JOIN ".$dbtp."author a1 on a1.aut_id = r4.rek_author_id
                  ".$limit." 
                  WHERE r2.rek_file_downloads > 0 AND r2.rek_status = 2
                  GROUP BY r4.rek_author_id, a1.aut_display_name
                  ORDER BY sort_column DESC
                  LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		foreach ($res as $key => $result) {
			$res[$key]['file_downloads'] = $res[$key]['sort_column'];
		}

		$return = $res;
		$hidden_rows = 0;

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
                    "end_offset"    => $total_rows_limit,
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page,
                    "hidden_rows"     => 0
		)
		);
	}

	function suggest($terms, $current_row = 0, $max = 10) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (APP_SOLR_SWITCH == "ON" ) {
			$index = new FulltextIndex_Solr(true);
			$sort_by = "";
			$approved_roles=array(9,10);
			$params = array();
			$params['query'] = $terms;
			$res = $index->suggestQuery($params, $options, $approved_roles, $sort_by, $start, $max);
			return ($res['docs']);
		}

		if (empty($terms)) {
			return array();
		}
		$authArray = Collection::getAuthIndexStmt();
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];
		$dbtp =  APP_TABLE_PREFIX;
		$spaceCount = substr_count($terms, " ");
		$spaceCount+=1;

		$stmt = "SELECT ".APP_SQL_CACHE."
                    substr(trim(substr(r2.rek_title, instr(r2.rek_title, ".$db->quote(' '.$terms)."))),
                    1,char_length(substring_index(concat(trim(substring(r2.rek_title, instr(r2.rek_title, ".$db->quote(' '.$terms)."))),  ' '),
                    ' ', ".$spaceCount.")))  as matchword, 33 as cword
                FROM " . APP_TABLE_PREFIX . "record_search_key AS r2 ".$authStmt."
                WHERE (r2.rek_title like ".$db->quote('%'.$terms.'%').") and r2.rek_status = 2
                LIMIT 20 OFFSET 0";
		
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return 0;
		}

		$termCounter = 100;

		$return = array();
		$sorter = array();
		$res_count = array();

		foreach ($res as $word => $count) {

			$countStmt = "SELECT ".APP_SQL_CACHE." count(r2.rek_pid)
			              FROM ".$dbtp."record_search_key AS r2
						  ".$authStmt."
						  WHERE r2.rek_title like ".$db->quote('% '.$word.'%')." and r2.rek_status = 2";
			try {
				$total_rows = $db->fetchOne($countStmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return 0;
			}
			$sorter[$total_rows] = $word;
		}

		krsort($sorter);
		foreach ($sorter as $s1 => $s2) {
			$return[$s2] = $s2."        (".$s1." title matches)";
		}

		return $return;

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
	function searchListing($terms, $current_row = 0, $max = 25, $sort_by='Relevance') 
	{
		$options = array();
		$options["searchKey0"] = $terms;
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 2; // collections only
		$list = Record::getListing($options, array("Lister"), $current_row, $max, $sort_by, true);
		return $list;
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
		$options = array();
		if (APP_CUSTOM_VIEW_PID != "") {
			$options["searchKey".Search_Key::getID("isMemberOf")] = APP_CUSTOM_VIEW_PID; // enforce custom view collections only
		}
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 2; // collections only
		$list = Record::getListing($options, array("Lister"), 0, 1000, "Title", true, false);

		$list = $list['list'];
		$returnList = array();

		foreach ($list as $element) {
			if (is_numeric($element['rek_ismemberof_count'])) {
				$returnList[$element['rek_pid']] = $element['rek_title']." (".$element['rek_ismemberof_count'].")";
			} else {
				$returnList[$element['rek_pid']] = $element['rek_title'];
			}
		}
		return $returnList;

	}

}
