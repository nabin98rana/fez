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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle search keys.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.org_structure.php");

class Search_Key
{


	function stripSearchKeys($options = array()) 
	{
		$new_options = array();
		foreach ($options as $key => $value) {
			if (!is_numeric(strpos($key, "searchKey"))) {
				$new_options[$key] = $value;
			}
		}
		$new_options["searchKey_count"] = $options["searchKey_count"];
		return $new_options;
	}



	/**
	 * Method used to remove a given list of search keys.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
		try {
			$db->query($stmt, $_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
	
		if(function_exists('apc_clear_cache')) {
			apc_clear_cache('user');
		}
		return true;
	}


	/**
	 * Method used to add a new search key to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if(function_exists('apc_clear_cache')) {
			apc_clear_cache('user');
		}

		if (@$_POST["sek_simple_used"]) {
			$sek_simple_used = TRUE;
		} else {
			$sek_simple_used = FALSE;
		}
		if (@$_POST["sek_bulkchange"]) {
			$sek_bulkchange = TRUE;
		} else {
			$sek_bulkchange = FALSE;
		}
		if (@$_POST["sek_adv_visible"]) {
			$sek_adv_visible = TRUE;
		} else {
			$sek_adv_visible = FALSE;
		}
		if (@$_POST["sek_myfez_visible"]) {
			$sek_myfez_visible = TRUE;
		} else {
			$sek_myfez_visible = FALSE;
		}
		if (@$_POST["sek_faceting"]) {
			$sek_faceting = TRUE;
		} else {
			$sek_faceting = FALSE;
		}
		if (@$_POST["sek_cardinality"]) {
			$sek_cardinality = TRUE;
		} else {
			$sek_cardinality = FALSE;
		}
		if (@$_POST["sek_relationship"]) {
			$sek_relationship = TRUE;
		} else {
			$sek_relationship = FALSE;
		}


		$sekIncrId = Search_Key::getNextIncrId(APP_PID_NAMESPACE);
		$sek_id = APP_PID_NAMESPACE . '_' . $sekIncrId;

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "search_key
                 (
                    sek_id,
                    sek_namespace,
                    sek_incr_id,
                    sek_title,
                    sek_desc,
					sek_alt_title,
					sek_meta_header,
					sek_simple_used,
					sek_bulkchange,
					sek_adv_visible,
					sek_myfez_visible,
					sek_faceting,";
		if (is_numeric($_POST["sek_order"])) {
			$stmt .= " sek_order, ";
		}
		if (is_numeric($_POST["sek_relationship"])) {
			$stmt .= " sek_relationship, ";
		}
		if (is_numeric($_POST["sek_cardinality"])) {
			$stmt .= " sek_cardinality, ";
		}

		$stmt .= "
					sek_data_type,
					sek_html_input,
					sek_fez_variable,
					sek_lookup_function,
					sek_suggest_function,
					sek_derived_function,
					sek_smarty_variable ";
		if (is_numeric($_POST["sek_cvo_id"])) {
			$stmt .= " ,sek_cvo_id ";
		}
		$stmt .= "
                 ) VALUES (
                    " . $db->quote($sek_id).",
                    " . $db->quote(APP_PID_NAMESPACE) . ",
                    " . $db->quote($sekIncrId, 'INTEGER').",
                    " . $db->quote($_POST["sek_title"]) . ",
                    " . $db->quote($_POST["sek_desc"]) . ",
					" . $db->quote($_POST["sek_alt_title"]) . ",
					" . $db->quote($_POST["sek_meta_header"]) . ",
					" . $db->quote($sek_simple_used) .",
					" . $db->quote($sek_simple_used) .",
					" . $db->quote($sek_adv_visible) .",
					" . $db->quote($sek_myfez_visible) .",
		            " . $db->quote($sek_faceting) .",";
		if (is_numeric($_POST["sek_order"])) {
			$stmt .=  $_POST["sek_order"] . ",";
		}
		if (is_numeric($_POST["sek_relationship"])) {
			$stmt .=  $db->quote($sek_relationship) . ",";
		}
		if (is_numeric($_POST["sek_cardinality"])) {
			$stmt .=  $db->quote($sek_cardinality) . ",";
		}

		$stmt .= "
                    " . $db->quote($_POST["sek_data_type"]) . ",					
                    " . $db->quote($_POST["field_type"]) . ",					
                    " . $db->quote($_POST["sek_fez_variable"]) . ",
					" . $db->quote($_POST["sek_lookup_function"]) . ",
					" . $db->quote($_POST["sek_suggest_function"]) . ",
					" . $db->quote($_POST["sek_derived_function"]) . ",
                    " . $db->quote($_POST["sek_smarty_variable"]);
		if (is_numeric($_POST["sek_cvo_id"])) {
			$stmt .=  "," . $db->quote($_POST["sek_cvo_id"], 'INTEGER');
		}
		$stmt .= ")";
		
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

		if(function_exists('apc_clear_cache')) {
			apc_clear_cache('user');
		}
			
		if($_POST['create_sql']) {

			if($_POST["sek_relationship"] == 1) {
				// no longer need to get sek_id - as it is pre-got before the insert
				//	       $sek_id = $GLOBALS["db_api"]->dbh->getLastInsertId(APP_TABLE_PREFIX . "search_key", 'sek_id');
				return Search_Key::createSearchKeyDB($sek_id);
				 
			} elseif($_POST["sek_relationship"] == 0) {

				include_once(APP_INC_PATH.'class.bgp_create_searchkey.php');
				// no longer need to get sek_id - as it is pre-got before the insert
				//	        $sek_id = $GLOBALS["db_api"]->dbh->getLastInsertId(APP_TABLE_PREFIX . "search_key", 'sek_id');

				/*
				 * Because the alter might take a while, run in
				 * a background process
				 */
				$bgp = new BackgroundProcess_Create_SearchKey();
				$bgp->register(serialize(array('sek_id' => $sek_id)), Auth::getUserID());
				Session::setMessage('The column is being created as a background process (see My Fez to follow progress)');
				return 1;
			}
		}
	}


	function getNextIncrId($namespace)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT MAX(sek_incr_id) + 1 as incr_id " .
                 "FROM  " . APP_TABLE_PREFIX . "search_key " .
                 "WHERE sek_namespace = ".$db->quote($namespace);
		
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return 1;
		}
		
		if (is_null($res)) {
			return 1;
		}
		return $res;

	}


	/**
	 * Method used to update details of a search key.
	 *
	 * @access  public
	 * @param   integer $sek_id The search key ID
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (@$_POST["sek_simple_used"]) {
			$sek_simple_used = TRUE;
		} else {
			$sek_simple_used = FALSE;
		}
		if (@$_POST["sek_bulkchange"]) {
			$sek_bulkchange = TRUE;
		} else {
			$sek_bulkchange = FALSE;
		}
		if (@$_POST["sek_adv_visible"]) {
			$sek_adv_visible = TRUE;
		} else {
			$sek_adv_visible = FALSE;
		}
		if (@$_POST["sek_myfez_visible"]) {
			$sek_myfez_visible = TRUE;
		} else {
			$sek_myfez_visible = FALSE;
		}
		if (@$_POST["sek_faceting"]) {
			$sek_faceting = TRUE;
		} else {
			$sek_faceting = FALSE;
		}
		if (@$_POST["sek_cardinality"]) {
			$sek_cardinality = TRUE;
		} else {
			$sek_cardinality = FALSE;
		}
		if (@$_POST["sek_relationship"]) {
			$sek_relationship = TRUE;
		} else {
			$sek_relationship = FALSE;
		}

		if(function_exists('apc_clear_cache')) {
			apc_clear_cache('user');
		}

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "search_key
                 SET 
                    sek_title = " . $db->quote($_POST["sek_title"]) . ",
                    sek_desc = " . $db->quote($_POST["sek_desc"]) . ",
					sek_alt_title = " . $db->quote($_POST["sek_alt_title"]) . ",
                    sek_meta_header = " . $db->quote($_POST["sek_meta_header"]) . ",
					sek_simple_used = ".$db->quote($sek_simple_used).",
					sek_bulkchange = ".$db->quote($sek_bulkchange).",
					sek_myfez_visible = ".$db->quote($sek_myfez_visible).",
					sek_adv_visible = ".$db->quote($sek_adv_visible).",
					sek_faceting = ".$db->quote($sek_faceting).",";
		if ($_POST["sek_order"]) {
			$stmt .= "sek_order = ".$db->quote($_POST["sek_order"], 'INTEGER').",";
		}
		if (isset($_POST["sek_relationship"])) {
			$stmt .= "sek_relationship = ".$db->quote($sek_relationship).",";
		}
		if (isset($_POST["sek_cardinality"])) {
			$stmt .= "sek_cardinality = ".$db->quote($sek_cardinality).",";
		}

		$stmt .= "
                    sek_html_input = " . $db->quote($_POST["field_type"]) . ",
                    sek_smarty_variable = " . $db->quote($_POST["sek_smarty_variable"]) . ",
					sek_lookup_function = " . $db->quote($_POST["sek_lookup_function"]) . ",
					sek_suggest_function = " . $db->quote($_POST["sek_suggest_function"]) . ",
					sek_derived_function = " . $db->quote($_POST["sek_derived_function"]) . ",
					sek_data_type = " . $db->quote($_POST["sek_data_type"]) . ",
                    sek_fez_variable = " . $db->quote($_POST["sek_fez_variable"]);
		if (is_numeric($_POST["sek_cvo_id"])) {
			$stmt .= ",sek_cvo_id = ".$db->quote($_POST["sek_cvo_id"], 'INTEGER');
		}
		$stmt .= "
                 WHERE sek_id = ".$db->quote($sek_id);

		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

	
		if(function_exists('apc_clear_cache')) {
			apc_clear_cache('user');
		}
			
		/*
		 * Should we create the table/column for this search key?
		 */
		if($_POST['create_sql']) {

			if($_POST["sek_relationship"] == 1) {
				 
				/*
				 * Create new table
				 */
				return Search_Key::createSearchKeyDB($sek_id);
				 
			} elseif($_POST["sek_relationship"] == 0) {

				/*
				 * Create column which requires an alter
				 */
				include_once(APP_INC_PATH.'class.bgp_create_searchkey.php');

				/*
				 * Because the alter might take a while, run in
				 * a background process
				 */
				$bgp = new BackgroundProcess_Create_SearchKey();
				$bgp->register(serialize(array('sek_id' => $sek_id)), Auth::getUserID());
				Session::setMessage('The column is being created as a background process (see My Fez to follow progress)');
				return 1;

			}
		}		
	}

	/**
	 * Method used to get the ID of a specific search key by the title.
	 *
	 * @access  public
	 * @param   integer $sek_title The search key title
	 * @return  string The ID of the search key
	 */
	function getID($sek_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		static $returns;
		if (!empty($returns[$sek_title])) {
			return $returns[$sek_title];
		}
		$stmt = "SELECT
                     sek_id
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_title=".$db->quote($sek_title);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		$returns[$sek_title] = $res;
		return $res;
	}
	
	function getRelationshipByDBName($db_name) 
	{		
		$log = FezLog::get();
		$db = DB_API::get();
		
		if(strpos($db_name, 'rek_')) {
			$db_name = str_replace('rek_', '', $db_name);
		}
		
		$db_name = str_replace("_", " ", trim(strtolower($db_name)));
		
		$stmt = "SELECT
                     sek_relationship
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    LOWER(sek_title)=".$db->quote($db_name);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		return $res;
		
	}

	function getDBnamefromSolrID($solrID) 
	{
		/*
		 * These fields exist in solr for sorting purposes only
		 * We dont need them in fez
		 */
		if($solrID == 'id') {
			return false;
		}

		if(strpos($solrID, '_t_s')) {
			return false;
		}

		if(strpos($solrID, '_ms')) {
			return false;
		}

		if(strpos($solrID, 'authlister')) {
			return false;
		}

		$lastUnderscore = strrpos($solrID, '_');

		if(!$lastUnderscore) {
			return false;
		}

		return 'rek_'. substr($solrID, 0, $lastUnderscore);
	}

	/**
	 * Method used to get the title of a specific search key.
	 *
	 * @access  public
	 * @param   integer $sek_id The search key ID
	 * @return  string The title of the search key
	 */
	function getTitle($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                     IF(sek_alt_title <> '', sek_alt_title, sek_title)
                 FROM
                   " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id= ".$db->quote($sek_id);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}

	/**
	 * Method used to get the fez variable of a specific search key.
	 *
	 * @access  public
	 * @param   integer $sek_id The search key ID
	 * @return  string The fez variable of the search key
	 */
	function getFezVariable($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sek_fez_variable
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id= ".$db->quote($sek_id);
		
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;		
	}

	/**
	 * Method used to get the max sek_id.
	 *
	 * @access  public
	 * @return  array The search keys max sek id
	 */
	function getMaxID()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    COUNT(sek_id)
                 FROM
                    " . APP_TABLE_PREFIX . "search_key";
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get the list of search keys available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of search keys in an associative array (for drop down lists).
	 */
	function getAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sek_id,
					IF(sek_alt_title <> '', sek_alt_title, sek_title)
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 ORDER BY
                    sek_title ASC";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the list of search keys available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of search keys in an associative array (for drop down lists).
	 */
	function getBulkChangeAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sek_id,
					IF(sek_alt_title <> '', sek_alt_title, sek_title)
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
								 WHERE sek_bulkchange = TRUE
                 ORDER BY
                    sek_title ASC";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the list of search keys available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of search keys in an associative array (for drop down lists).
	 */
	function getAssocListAdvanced($hide_unused=0)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sek_id,
					IF(sek_alt_title <> '', sek_alt_title, sek_title)
                 FROM
                    " . APP_TABLE_PREFIX . "search_key ";
		if ($hide_unused == 1) {
			$stmt .= " INNER JOIN " . APP_TABLE_PREFIX . "xsd_display_matchfields
                    on xsdmf_sek_id=sek_id ";
		}
		$stmt .= "
				 WHERE sek_adv_visible = TRUE
                 ORDER BY
                    sek_order ASC";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the list of search keys available in the
	 * system returned in an associative array for drop down lists.
	 *
	 * @access  public
	 * @return  array The list of search keys in an associative array (for drop down lists).
	 */
	function getListAdvanced()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sek_id,
					IF(sek_alt_title <> '', sek_alt_title, sek_title),
					sek_fez_variable
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_adv_visible = TRUE                  
                 ORDER BY
                    sek_order ASC";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		for ($i = 0; $i < count($res); $i++) {
			$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
		}
		return $res;
	}


	/**
	 * Method used to get the list of search key options associated
	 * with a given search key ID.
	 *
	 * @access  public
	 * @param   integer $sek_smarty_variable The search key variable
	 * @return  array The list of search key options
	 */
	function getOptions($sek_smarty_variable)
	{
		$log = FezLog::get();
		
		$return = array();
		if (!empty($sek_smarty_variable)) {
			$log->debug("\$return = ". $sek_smarty_variable.";");
			eval("\$return = ". $sek_smarty_variable.";");
			$log->debug("returned from eval'd \$return = ". $sek_smarty_variable.";");
		}
		return $return;

	}

	/**
	 * Method used to get the list of search keys available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of search keys
	 */
	function getList($checkTableExists = true)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 ORDER BY
                    sek_title ASC";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if (empty($res)) {
			return array();
		}

		for ($i = 0; $i < count($res); $i++) {
			$res[$i]['sek_title_db'] = Search_Key::makeSQLTableName($res[$i]['sek_title'], $i);

			if($checkTableExists == true) {
				$res[$i]['key_table_exists'] = Search_Key::checkIfKeyTableExists($res[$i]['sek_title_db'], $res[$i]['sek_relationship']);
			}
		}

		return $res;
	}


	/**
	 * Method used to get the list of search keys available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of search keys
	 */
	function getAdvSearchList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_adv_visible = TRUE
                 ORDER BY
                    sek_order ASC";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
			
		if (empty($res)) {
			return array();
		} else {
			for ($i = 0; $i < count($res); $i++) {
				$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
			}
			return $res;
		}
	}

	/**
	 * Method used to get the list of search keys available in the
	 * system for the my fez search page.
	 *
	 * @access  public
	 * @return  array The list of search keys
	 */
	function getMyFezSearchList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		// AM: disable depsitor search key. with ~80000 authors it slows considerably
		// need to look at a better way to search for depositor
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_myfez_visible = TRUE
                 ORDER BY
                    sek_order ASC";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if (empty($res)) {
			return array();
		} else {
			for ($i = 0; $i < count($res); $i++) {				
				$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
			}
			return $res;
		}
	}

	/**
	 * Method used to get the list of simple/quick search keys available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of search keys
	 */
	function getQuickSearchList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_simple_used = TRUE
                 ORDER BY
                    sek_order ASC";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if (empty($res)) {
			return array();
		} else {

			for ($i = 0; $i < count($res); $i++) {
				$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
				if ($res[$i]["sek_html_input"] == "contvocab") {
					$cvo_list = Controlled_Vocab::getAssocListFullDisplay(false, "", 0, 2);
					$res[$i]['field_options'][0] = $cvo_list['data'][$res[$i]['sek_cvo_id']];
					$res[$i]['cv_titles'][0] = $cvo_list['title'][$res[$i]['sek_cvo_id']];
					$res[$i]['cv_ids'][0] = $res[$i]['sek_cvo_id'];
				}
			}
			return $res;
		}
	}


	function getFacetList() 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		 
		$stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_faceting = TRUE";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		if (empty($res)) {
			return array();
		}

		return $res;
	}

	/**
	 * Method used to get the list of search keys available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of search keys
	 */
	function getSimpleList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sek_id
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE
				   sek_simple_used = TRUE";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		if (empty($res)) {
			return array();
		} else {
			return $res;
		}	
	}

	/**
	 * Returns the list of functions that have derived functions associated with them
	 *
	 * @return array the search key details
	 **/
	public function getDerivedList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$prefix = APP_TABLE_PREFIX;
		$stmt = "SELECT sek_id FROM {$prefix}search_key WHERE sek_derived_function IS NOT NULL";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		if (empty($res)) {
			return array();
		}
		return $res;
	}

	/**
	 * Method used to get the details of a specific search key.
	 *
	 * @access  public
	 * @param   integer $sek_id The search key ID
	 * @return  array The search key details
	 */
	function getDetails($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		if ($sek_id == "0") {
			return false;
		}
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                    left join " . APP_TABLE_PREFIX . "xsd_display_matchfields
                    on xsdmf_sek_id=sek_id                    
                 WHERE
                    sek_id= ". $db->quote($sek_id);
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);
		$res['key_table_exists'] = Search_Key::checkIfKeyTableExists($res['sek_title_db'], $res['sek_relationship']);

		return $res;
	}

	/**
	 * Method used to get the details of a specific search key from a passed XSDMF match
	 *
	 * @access  public
	 * @param   integer $xsdmf_id The xsd matching field ID
	 * @return  array The search key details
	 */
	function getDetailsByXSDMF_ID($xsdmf_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    s1.*
                 FROM
                    " . APP_TABLE_PREFIX . "search_key as s1
                    inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields as x1
                    on xsdmf_sek_id=sek_id                    
                 WHERE
                    xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');

		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		if ($res['sek_id']) {
			$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);
		}
		return $res;
	}


	/**
	 * Method used to get the details of a specific search key suggest function from a passed XSDMF match
	 *
	 * @access  public
	 * @param   integer $xsdmf_id The xsd matching field ID
	 * @return  array The search key suggest function string
	 */
	function getSuggestFunctionByXSDMF_ID($xsdmf_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    s1.sek_suggest_function
                 FROM
                    " . APP_TABLE_PREFIX . "search_key as s1
                    inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields as x1
                    on xsdmf_sek_id=sek_id                    
                 WHERE
                    xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}
	
	/**
	 * Method used to get the details of a specific search key suggest function from a passed Sek match
	 *
	 * @access  public
	 * @param   integer $sek_id The sek matching field ID
	 * @return  array The search key suggest function string
	 */
	function getSuggestFunctionBySek_ID($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    sek_suggest_function
                 FROM
                    " . APP_TABLE_PREFIX . "search_key                     
                 WHERE
                    sek_id=".$db->quote($sek_id);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}

	/**
	 * Method used to get the details of a specific search key suggest function from a passed Sek match
	 *
	 * @access  public
	 * @param   integer $sek_id The sek matching field ID
	 * @return  array The search key suggest function string
	 */
	function getLookupFunctionBySek_ID($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    sek_lookup_function
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id=".$db->quote($sek_id);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}



	function suggestSearchKeyIndexValue($sek_details, $term, $assoc = false) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$dbtp =  APP_TABLE_PREFIX; // Database and table prefix
		if (!is_array($sek_details)) {
			return false;
		}
		$sek_title = Search_Key::makeSQLTableName($sek_details['sek_title']);
		if ($sek_details['sek_relationship'] == 1) { //1-M
			if (APP_MYSQL_INNODB_FLAG == "ON" || (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql")))) {
				$stmt = " SELECT rek_".$sek_title."_id as id, rek_".$sek_title." as name FROM (";
				$stmt .= "
					  SELECT rek_".$sek_title."_id, rek_".$sek_title."
						FROM ".$dbtp."record_search_key_".$sek_title."
					 WHERE rek_".$sek_title." LIKE ".$db->quote($term.'%')." GROUP BY rek_".$sek_title."";
				$stmt .= " LIMIT 10 OFFSET 0) as tempsuggest ";
			} else { //if you are mysql using myisam db type then you can take advantage of fulltext indexing (make sure you have a ft index on the value column)
				$stmt = " SELECT rek_".$sek_title."_id as id, rek_".$sek_title." as name FROM (";
				$stmt .= "
					  SELECT rek_".$sek_title."_id, rek_".$sek_title.",
						MATCH(rek_".$sek_title.") AGAINST (".$db->quote($term).") as Relevance FROM ".$dbtp."record_search_key_".$sek_title."
					 WHERE MATCH (rek_".$sek_title.") AGAINST (".$db->quote('*'.$term.'*')." IN BOOLEAN MODE) ";
				$stmt .= " GROUP BY rek_".$sek_title."_id, rek_".$sek_title." ORDER BY Relevance DESC, rek_".$sek_title." LIMIT 10 OFFSET 0) as tempsuggest ";
			}
		} else { //1-1 index table
			if (APP_MYSQL_INNODB_FLAG == "ON" || (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql")))) {
				$stmt = " SELECT 1 as id, rek_".$sek_title." as name FROM (";
				$stmt .= "
					  SELECT 1, rek_".$sek_title."
					  FROM ".$dbtp."record_search_key
					  WHERE rek_".$sek_title." LIKE ".$db->quote($term.'%')." GROUP BY rek_".$sek_title.""; //like value% will only grab values starting with the search term, but %term% won't use an index so will be too slow
				$stmt .= " LIMIT 10 OFFSET 0) as tempsuggest ";
			} else { //if you are mysql using myisam db type then you can take advantage of fulltext indexing (make sure you have a ft index on the value column)
				$stmt = " SELECT 1 as id, rek_".$sek_title." as name FROM (";
				$stmt .= "
					  SELECT 1, rek_".$sek_title.",
						MATCH(rek_".$sek_title.") AGAINST (".$db->quote($term).") as Relevance FROM ".$dbtp."record_search_key
					 WHERE MATCH (rek_".$sek_title.") AGAINST (".$db->quote('*'.$term.'*')." IN BOOLEAN MODE) ";
				$stmt .= " GROUP BY rek_".$sek_title." ORDER BY Relevance DESC, rek_".$sek_title." LIMIT 10 OFFSET 0) as tempsuggest";
			}
		}
		try {
			if ($assoc) 
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			else
				$res = $db->fetchAssoc($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
	
		if ($getLookup == true && $sek_details['sek_lookup_function'] != "") {
			$temp = array();
			eval("\$temp_value = ".$sek_details["sek_lookup_function"]."(".$res.");");
			$temp[$res] = $temp_value;
			$res = $temp;
		}
		return $res;
	}


	function getAllDetailsByXSDMF_ID($xsdmf_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key as s1
                    inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields as x1
                    on xsdmf_sek_id=sek_id                    
                 WHERE
                    xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

	
		if ($res['sek_id']) {
			$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);
		}
		return $res;
	}

	function getSolrTitles()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key as s1 ";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		$return = array();
		for ($i = 0; $i < count($res); $i++) {
			$res[$i]['sek_title_db'] = Search_Key::makeSQLTableName($res[$i]['sek_title']);
			$res[$i]['sek_title_solr'] = FulltextIndex_Solr::getFieldName($res[$i]['sek_title_db'], FulltextIndex::mapType($res[$i]['sek_data_type']), $res[$i]['sek_relationship']);
			$return[$res[$i]['sek_title_db']] = $res[$i]['sek_title_solr'];
		}
		return $return;
	}


	/**
	 * Method used to get the basic details of a specific search key.
	 *
	 * @access  public
	 * @param   integer $sek_id The search key ID
	 * @return  array The search key details
	 */
	function getBasicDetails($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id= ".$db->quote($sek_id);
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);
		return $res;
	}

	function makeSQLTableName($sek_title) 
	{
		$retString = str_replace(" ", "_", trim(strtolower($sek_title)));
		return $retString;
	}
	/**
	 * Method used to get the details of a specific search key.
	 *
	 * @access  public
	 * @param   string $sek_title The search key title
	 * @return  array The search key details
	 */
	function getDetailsByTitle($sek_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                    inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields
                    on xsdmf_sek_id=sek_id
                 WHERE
                    sek_title=".$db->quote($sek_id);
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);
		return $res;
	}



	/**
	 * Method used to get the details of a specific search key.
	 *
	 * @access  public
	 * @param   string $sek_title The search key title
	 * @return  array The search key details
	 */
	function getBasicDetailsByTitle($sek_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE ";
		if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { //pgsql is case sensitive
			$stmt .= " sek_title ILIKE ".$db->quote($sek_title);
		} else {
			$stmt .= " sek_title=".$db->quote($sek_title);
		}
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);
		return $res;
	}

	/**
	 * Determine if a search key has its corresponding database schema
	 * setup
	 *
	 * @param string $sek_title_db  the name of search key
	 * @param int    $relationship  determines if search key a column
	 *                              or table
	 *
	 * @return int  1 if setup. 0 if not setup
	 *
	 * @access public
	 * @since Method available since Fez 2.0
	 */
	function checkIfKeyTableExists($sek_title_db, $relationship)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if( $relationship == 1 ) {
			/*
			 * Check if table exists
			 */
			$sek_title_db = APP_TABLE_PREFIX.'record_search_key_'.$sek_title_db;

			$stmt = "   SELECT count(*) as cnt
                        FROM information_schema.tables
                        WHERE table_schema = ".$db->quote(APP_SQL_DBNAME)."
                            AND table_name = ".$db->quote($sek_title_db);
			try {
				$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return 0;
			}
			return $res['cnt'];

		} else {

			/*
			 * Check if column exists
			 */
			$table_name = APP_TABLE_PREFIX . 'record_search_key';
			$column_name = 'rek_' . $sek_title_db;

			$stmt = "   SELECT count(*) as cnt
                        FROM information_schema.columns
                        WHERE table_schema = ".$db->quote(APP_SQL_DBNAME)."
                            AND table_name = ".$db->quote($table_name)." 
                            AND column_name = ".$db->quote($column_name);
			try {
				$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return 0;
			}

			return $res['cnt'];
		}
	}

	/**
	 * Create in the database a column/table for a
	 * particular search key
	 *
	 * @param int $sek_id  the search key id
	 *
	 * @return int  1 if sql creation was succesful. -2 if sql failed
	 *
	 * @access public
	 * @since Method available since Fez 2.0
	 */
	function createSearchKeyDB($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = Search_Key::createSQL($sek_id);
		if(!$stmt) {
			return -2;
		}
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -2;
		}
		return 1;
	}

	/**
	 * Create sql for column/table for a search key
	 *
	 * @param int $sek_id  the search key id
	 *
	 * @return string   the sql to create the column/table. FALSE if sek_id
	 *                  is not valid
	 *
	 * @access public
	 * @since Method available since Fez 2.0
	 */
	function createSQL($sek_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$details = Search_Key::getDetails($sek_id);
		$sek_title_db = $details['sek_title_db'];
		$relationship = $details['sek_relationship'];
		$cardinality = $details['sek_cardinality'];
		$column_type  = $details['sek_data_type'];
		$key_type     = 'KEY';
		$cardinality_extra = "";

		if( !isset($sek_title_db) || $sek_title_db == "" || $column_type == "" ) {
			return -2;
		}

		if( $column_type == 'varchar' ) {
			$column_type = 'varchar(255)';
		} elseif( $column_type == 'date' ) {
			$column_type = 'datetime';
		} elseif( $column_type == 'text' ) {
			$key_type = 'FULLTEXT';
		}

		if( $relationship == 1 ) {

			/*
			 * Create new table
			 */
			$table_name     = APP_TABLE_PREFIX.'record_search_key_'.$sek_title_db;
			$column_prefix   = 'rek_' . $sek_title_db;
			if ($cardinality == 1) {
				$cardinality_extra = "     `{$column_prefix}_order` int(11) default 1,\n ";
			}


			$sql = "CREATE TABLE `$table_name` ( \n" .
                   "     `{$column_prefix}_id` int(11) NOT NULL auto_increment, \n" .
                   "     `{$column_prefix}_pid` varchar(64) default NULL, \n" .
                   "     `{$column_prefix}_xsdmf_id` int(11) default NULL,\n " .
			$cardinality_extra .
                   "     `$column_prefix` $column_type default NULL, \n" .
                   "     PRIMARY KEY (`{$column_prefix}_id`), \n" .
                   "     $key_type `$column_prefix` (`$column_prefix`), \n" .
                   "     KEY `{$column_prefix}_pid` (`{$column_prefix}_pid`) \n";
			if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
				$cardinality_extra .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8";
			} else { //otherwise just use the defaults of the non-mysql database
				$cardinality_extra .= ")";
			}
			return $sql;

		} elseif( $relationship == 0 ) {
			/*
			 * Create new columns
			 */
			$table_name     = APP_TABLE_PREFIX.'record_search_key';
			$column_name    = 'rek_' . $sek_title_db;

			$sql = "ALTER TABLE `$table_name` \n" .
                   "    ADD COLUMN `{$column_name}_xsdmf_id` int(11), \n" .
                   "    ADD COLUMN `$column_name` $column_type \n";

			return $sql;

		}

		return false;
	}

}
