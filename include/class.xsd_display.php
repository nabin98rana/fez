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
 * Class to handle the business logic related to the administration
 * of custom fields in the system.
 *
 * @version 1.0
 * @author Joo Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.fezacml.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.xsd_relationship.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.citation.php");

class XSD_Display
{

	/**
	 * Method used to remove a given list of XSD Displays, cascading to all their child dependant XSD matchings.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove($params = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($params)) {
			$params = $_POST;
		}

		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id IN (".Misc::arrayToSQLBindStr($params["items"]).")";
		try {
			$db->query($stmt, $params["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
	
		// also remove any xsdmf's, sels and relationships that are connected to this display
		$stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "xsd_loop_subelement
                     WHERE
                        xsdsel_xsdmf_id IN ( SELECT xsdmf_id FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_xdis_id IN (" . Misc::arrayToSQLBindStr($params["items"]) . "))";

		try {
			$db->query($stmt, $params["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
		$stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "xsd_relationship
                     WHERE
                        xsdrel_xsdmf_id IN ( SELECT xsdmf_id FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_xdis_id IN (" . Misc::arrayToSQLBindStr($params["items"]) . "))";

		try {
			$db->query($stmt, $params["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
		
		// remove any related entries in fez_xsd_display_match
		$stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "xsd_display_attach
                     WHERE
                        att_parent_xsdmf_id IN ( SELECT xsdmf_id FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_xdis_id IN (" . Misc::arrayToSQLBindStr($params["items"]) . "))";

		try {
			$db->query($stmt, $params["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
		// remove any related entries in fez_xsd_display_mf_options
		$stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "xsd_display_mf_option
                     WHERE
                        mfo_fld_id IN ( SELECT xsdmf_id FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_xdis_id IN (" . Misc::arrayToSQLBindStr($params["items"]) . "))";

		try {
			$db->query($stmt, $params["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}

		$stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "xsd_display_matchfields
                     WHERE
                        xsdmf_xdis_id IN (" . Misc::arrayToSQLBindStr($params["items"]) . ")";
		try {
			$db->query($stmt, $params["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
		
		foreach ($params["items"] as $item) {
			Citation::deleteAllTypes($item);
		}
		return true;
	}

	/**
	 * Method used to clone an existing display in the system.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID to clone
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function cloneDisplay($xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$master_res = XSD_Display::getDetails($xdis_id);

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "xsd_display
                 (
                    xdis_title,
                    xdis_xsd_id,
                    xdis_version,
					xdis_object_type
                 ) VALUES (
                    ".$db->quote('Clone of ' .$master_res["xdis_title"]).",
                    " .$db->quote($master_res["xdis_xsd_id"], 'INTEGER') . ",
                    '1.0',
			        " .$db->quote($master_res["xdis_object_type"], 'INTEGER') . "
                 )";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		
		$new_xdis_id = $db->lastInsertId(APP_TABLE_PREFIX . "xsd_display", "xdis_id");
		// get a list of all the non-sel-child elements (where xsdmf_xsdsel_id = null)
		$xsdmf_res = XSD_HTML_Match::getNonSELChildListByDisplay($xdis_id);
		foreach ($xsdmf_res as $xsdmf_row) {
			// insert the record
			$current_xsdmf_id = $xsdmf_row['xsdmf_id'];
			$xsdmf_row['xsdmf_id'] = "";
			XSD_HTML_Match::insertFromArray($new_xdis_id, $xsdmf_row);
			// get the new xsdmf_id
			$new_xsdmf_id = $db->lastInsertId(APP_TABLE_PREFIX . "xsd_display_matchfields", "xsdmf_id");
			// get the sels for the current row
			$xsd_sel_res = XSD_Loop_Subelement::getSimpleListByXSDMF($current_xsdmf_id);
			// is the xsdmf a parent in the xsd_loop_subelement table? if so then create a clone entry for its sel entry
			if (count($xsd_sel_res) > 0) {
				foreach ($xsd_sel_res as $xsd_sel_row) {
					XSD_Loop_Subelement::insertFromArray($new_xsdmf_id, $xsd_sel_row);
					$new_sel_id = $db->lastInsertId(APP_TABLE_PREFIX . "xsd_loop_subelement", "xsdsel_id");
					$child_xsdmf_sel_res = XSD_HTML_Match::getSELChildListByDisplay($xdis_id, $xsd_sel_row['xsdsel_id']);
					// does the clone parent SEL record have any child sel elements? if so then insert clones for those too
					foreach ($child_xsdmf_sel_res as $child_xsdmf_sel_row) {
						XSD_HTML_Match::insertFromArraySEL($new_xdis_id, $new_sel_id, $child_xsdmf_sel_row);
						$new_child_xsdmf_id = $db->lastInsertId(APP_TABLE_PREFIX . "xsd_display_matchfields", "xsdmf_id");
						// do any of the children have xsd relationships? if so then insert them
						$xsdrel_res = XSD_Relationship::getSimpleListByXSDMF($child_xsdmf_sel_row['xsdmf_id']);
						foreach ($xsdrel_res as $xsdrel_row) {
							XSD_Relationship::insertFromArray($new_child_xsdmf_id, $xsdrel_row);
						}
					}
					// does the sel loop over an attribute loop candidate? if so then point to the new cloned versions xsdmf_id of it.
					if (is_numeric($xsd_sel_row['xsdsel_attribute_loop_xsdmf_id'])) {
						$new_attribute_loop_candidate = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($xsd_sel_row['xsdsel_attribute_loop_xsdmf_id'], $new_xdis_id);
						if (is_numeric($new_attribute_loop_candidate)) {
							XSD_Loop_Subelement::updateAttributeLoopCandidate($new_sel_id, $new_attribute_loop_candidate, $new_xdis_id);
						}
					}
					// does the sel have an indicator? if so then point to the new cloned versions xsdmf_id of it.
					if (is_numeric($xsd_sel_row['xsdsel_indicator_xsdmf_id'])) {
						$new_indicator = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($xsd_sel_row['xsdsel_indicator_xsdmf_id'], $new_xdis_id);
						if (is_numeric($new_indicator)) {
							XSD_Loop_Subelement::updateIndicator($new_sel_id, $new_indicator, $new_xdis_id);
						}
					}

				}
			}
			// does the clone parent SEL have any xsd relationships? if so insert them
			$xsdrel_res = XSD_Relationship::getSimpleListByXSDMF($current_xsdmf_id);
			foreach ($xsdrel_res as $xsdrel_row) {
				XSD_Relationship::insertFromArray($new_xsdmf_id, $xsdrel_row);
			}
		}
		$new_res = XSD_HTML_Match::getList($new_xdis_id);
		// after everything has been inserted check any id refs that need pointing to a new xsdmf id
		foreach ($new_res as $new_row) {
			// is the clone an xsdmf id reference of an xsd display that was the old xsd display (rather than an external one) then we need to make it point to the new version of the xsdmf id in this new xsd display
			if (is_numeric($new_row['xsdmf_id_ref'])) {
				$old_id_ref_xdis_id =  XSD_HTML_Match::getXDIS_IDByXSDMF_ID($new_row['xsdmf_id_ref']);
				if ($old_id_ref_xdis_id == $xdis_id) { // if the old one refered to an xsdmfid in its own display than make the new one refer to its new display id version of that xsdmf id as well
					$new_xsdmf_id_ref = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_id_ref'], $new_xdis_id); //what is the new display version of this old id ref
					if (is_numeric($new_xsdmf_id_ref)) {
						XSD_HTML_Match::updateXSDMF_ID_REF($new_row['xsdmf_id'], $new_xsdmf_id_ref, $new_xdis_id); //save it
					}
				}
			}
			if (is_numeric($new_row['xsdmf_asuggest_xsdmf_id'])) {
				$old_xdis_id =  XSD_HTML_Match::getXDIS_IDByXSDMF_ID($new_row['xsdmf_asuggest_xsdmf_id']);
				if ($old_xdis_id == $xdis_id) { // if the old one refered to an xsdmfid in its own display than make the new one refer to its new display id version of that xsdmf id as well
					$new_xsdmf_id_asuggest = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_asuggest_xsdmf_id'], $new_xdis_id); //what is the new display version of this old xsdmf id
					if (is_numeric($new_xsdmf_id_asuggest)) {
						XSD_HTML_Match::updateAuthorSuggestTarget($new_row['xsdmf_id'], $new_xsdmf_id_asuggest, $new_xdis_id); //save it
					}
				}
			}
			if (is_numeric($new_row['xsdmf_org_fill_xsdmf_id'])) {
				$old_xdis_id =  XSD_HTML_Match::getXDIS_IDByXSDMF_ID($new_row['xsdmf_org_fill_xsdmf_id']);
				if ($old_xdis_id == $xdis_id) { // if the old one refered to an xsdmfid in its own display than make the new one refer to its new display id version of that xsdmf id as well
					$new_xsdmf_id_org_fill = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_org_fill_xsdmf_id'], $new_xdis_id); //what is the new display version of this old xsdmf id
					if (is_numeric($new_xsdmf_id_org_fill)) {
						XSD_HTML_Match::updateOrgFillTarget($new_row['xsdmf_id'], $new_xsdmf_id_org_fill, $new_xdis_id); //save it
					}
				}
			}
			if (is_numeric($new_row['xsdmf_parent_option_child_xsdmf_id'])) {
				$old_xdis_id =  XSD_HTML_Match::getXDIS_IDByXSDMF_ID($new_row['xsdmf_parent_option_child_xsdmf_id']);
				if ($old_xdis_id == $xdis_id) { // if the old one refered to an xsdmfid in its own display than make the new one refer to its new display id version of that xsdmf id as well
					$new_xsdmf_id_parent_option_child = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_parent_option_child_xsdmf_id'], $new_xdis_id); //what is the new display version of this old xsdmf id
					if (is_numeric($new_xsdmf_id_parent_option_child)) {
						XSD_HTML_Match::updateParentOptionTarget($new_row['xsdmf_id'], $new_xsdmf_id_parent_option_child, $new_xdis_id); //save it
					}
				}
			}
			if (is_numeric($new_row['xsdmf_attached_xsdmf_id'])) {
				$new_xsdmf_id_attached = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_attached_xsdmf_id'], $new_xdis_id); //what is the new display version of this old xsdmf id
				if (is_numeric($new_xsdmf_id_attached)) {
					XSD_HTML_Match::updateAttachedTarget($new_row['xsdmf_id'], $new_xsdmf_id_attached); //save it
				}
			}
		}


		return 1;
	}


	/**
	 * Method used to add a new XSD Display to the system.
	 *
	 * @access  public
	 * @param   integer $xsd_id The XSD ID the display will be based on.
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert($xsd_id, $params=array(), $xdis_id=null)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($params)) {
			$params = &$_POST;
		}

		if (@$params["xdis_enabled"]) {
			$xdis_enabled = TRUE;
		} else {
			$xdis_enabled = FALSE;
		}
		$bind = array();
		if (!empty($xdis_id)) {
			$bind[] = $xdis_id;
			$xdis_field_str = 'xdis_id,';
			$xdis_value_str = "? ,";
		} else {
			$xdis_field_str = '';
			$xdis_value_str = '';
		}

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "xsd_display
                 (
                    ".$xdis_field_str."
                    xdis_title,
                    xdis_xsd_id,
                    xdis_version,
                    xdis_enabled,
                    xdis_object_type
                 ) VALUES (".$xdis_value_str."?,?,?,?,?)";
		$bind[] = $params["xdis_title"];
		$bind[] = $xsd_id;
		$bind[] = $params["xdis_version"];
		$bind[] = $xdis_enabled;
		$bind[] = $params["xdis_object_type"];
		
		try {						
			$db->query($stmt, $bind);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return $db->lastInsertId(APP_TABLE_PREFIX . "xsd_display", "xdis_id");
	}

	function insertAtId($xdis_id,$xsd_id, $params=array())
	{
		return XSD_Display::insert($xsd_id, $params, $xdis_id);
	}


	/**
	 * Method used to update a XSD Display in the system.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID to clone
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($xdis_id, $params = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($params)) {

			$params = &$_POST;
		}
		if (@$params["xdis_enabled"]) {
			$xdis_enabled = TRUE;
		} else {
			$xdis_enabled = FALSE;
		}

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "xsd_display
                 SET 
                    xdis_title = " . $db->quote($params["xdis_title"]) . ",
                    xdis_version = " . $db->quote($params["xdis_version"]) . ",
					xdis_enabled = " . $xdis_enabled . ",
					xdis_object_type = " .$db->quote($params["xdis_object_type"], 'INTEGER') . "
                 WHERE xdis_id = ".$db->quote($xdis_id, 'INTEGER');

		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}

	/**
	 * Method used to get the list of XSD Displays for a given XSD.
	 *
	 * @access  public
	 * @param   integer $xsd_id The XSD ID to search the list for.
	 * @param   string $where extra SQL on the where clause
	 * @return  array The list of XSD Displays
	 */
	function getList($xsd_id, $where = '')
	{
		$log = FezLog::get();
		$db = DB_API::get();
		$xsd_id = str_replace("'", "", $xsd_id);
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_xsd_id = ".$db->quote($xsd_id, 'INTEGER')." ".$where."
                 ORDER BY
                    xdis_title ASC";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the associative list of XSD Displays available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The associative list of XSD Displays
	 */
	function getAssocList()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    xdis_id, ";

		if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= " concat(xdis_title, ' Version ', xdis_version) as xdis_desc ";
		} else {
			$stmt .= " (xdis_title || ' Version ' || xdis_version) as xdis_desc ";			
		}
		$stmt .= "
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE xdis_enabled = TRUE
                 ORDER BY
                    xdis_title, xdis_version ASC";
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
	 * Method used to get the associative list of document types available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list
	 */
	function getAssocListCollectionDocTypes()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    xdis_id, ";

		if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= " concat(xdis_title, ' Version ', xdis_version) as xdis_desc ";
		} else {
			$stmt .= " (xdis_title || ' Version ' || xdis_version) as xdis_desc ";			
		}
		$stmt .= "
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display 
				 WHERE xdis_object_type = 2	and xdis_enabled = TRUE
                 ORDER BY
                    xdis_title, xdis_version ASC";
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
	 * Method used to get the associative list of document types available in the
	 * system.
	 *
	 * @access  public
	 * @param   integer $ret_id The Object Type ID to search the list for.
	 * @return  array The list
	 */
	function getAssocListByObjectType($ret_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    xdis_id, ";

		if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= " concat(xdis_title, ' Version ', xdis_version) as xdis_desc ";
		} else {
			$stmt .= " (xdis_title || ' Version ' || xdis_version) as xdis_desc ";			
		}
		$stmt .= "
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display 
				 WHERE xdis_object_type = ".$db->quote($ret_id, 'INTEGER')."			 
                 ORDER BY
                    xdis_title, xdis_version ASC";
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
	 * Method used to get the associative list of document types available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list
	 */
	function getAssocListDocTypes()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    xdis_id, ";

		if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= " concat(xdis_title, ' Version ', xdis_version) as xdis_desc ";
		} else {
			$stmt .= " (xdis_title || ' Version ' || xdis_version) as xdis_desc ";			
		}
		$stmt .= "
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
				 WHERE xdis_object_type = 3	and xdis_enabled = TRUE
                 ORDER BY
                    xdis_title, xdis_version ASC";
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
	 * Method used to get the associative list of document types available in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list
	 */
	function getAssocListDocTypesAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    xdis_id, ";

		if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= " concat(xdis_title, ' Version ', xdis_version) as xdis_desc ";
		} else {
			$stmt .= " (xdis_title || ' Version ' || xdis_version) as xdis_desc ";			
		}
		$stmt .= "
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
				 WHERE xdis_object_type != 4 and xdis_enabled = TRUE
                 ORDER BY
                    xdis_title, xdis_version ASC";
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
	 * Method used to get the parent XSD ID of an XSD Display
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The custom field details
	 */
	function getParentXSDID($xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    xdis_xsd_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id=".$db->quote($xdis_id, 'INTEGER');
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
	 * Method used to get the XSD Display ID of a XSD Display title
	 *
	 * @access  public
	 * @param   string $xdis_title The XSD title to search by.
	 * @return  integer $res the xdis_id
	 */
	function getID($xdis_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		static $returns;

		if (isset($returns[$xdis_title])) {
			return $returns[$xdis_title];
		}
		$stmt = "SELECT
                   xdis_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_title = ".$db->quote($xdis_title);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if ($GLOBALS['app_cache']) {
			$returns[$xdis_title] = $res;
		}
		return $res;
	}

	/**
	 * Method used to get the XSD Display ID of a XSD Display title related to another XSD Display
	 *
	 * @access  public
	 * @param   string $xsdsel_title The XSD sublooping element title to search by.
	 * @param   string $related_xdis_id The XSD display ID this one must be related to
	 * @return  integer $res the xdis_id
	 */
	function getIDInRelationship($xdis_title, $related_xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                   d1.xdis_id 
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display d1,
                    " . APP_TABLE_PREFIX . "xsd_relationship r1,
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields x1,
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields s1
                 WHERE
				    r1.xsdrel_xsdmf_id = x1.xsdmf_id AND x1.xsdmf_xdis_id = ".$db->quote($related_xdis_id, 'INTEGER')." AND s1.xsdsel_id = x1.xsdmf_xsdsel_id and
                    s1.xsdsel_title = ".$db->quote($xdis_title);
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
	 * Method used to get the XSD Display IDs of a list of XSD Display titles
	 *
	 * @access  public
	 * @param   integer $xdis_titles The XSD titles to search by.
	 * @return  array $res An array of IDs
	 */
	function getIDs($xdis_titles)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                   xdis_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_title in (".Misc::arrayToSQLBindStr($xdis_titles).") ORDER BY xdis_enabled DESC";
		try {
			$res = $db->fetchCol($stmt, $xdis_titles);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get the XSD Display title when given a
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD ID to search by.
	 * @return  array $res The title
	 */
	function getTitle($xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if(empty($xdis_id) || !is_numeric($xdis_id)) {
			return "";
		}
		 
		$stmt = "SELECT
                   xdis_title
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id = ".$db->quote($xdis_id, 'INTEGER');
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
	 * Method used to get the XSD Display ID with a given title
	 *
	 * @access  public
	 * @param   integer $xdis_title The XSD title to search by.
	 * @return  array $res The xdis_id
	 */
	function getXDIS_IDByTitle($xdis_title)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                   xdis_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_title = ".$db->quote($xdis_title);
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
	 * Method used to get the XSD Display ID with a given title and version
	 *
	 * @access  public
	 * @param   integer $xdis_title The XSD title to search by.
	 * @param   integer $xdis_version The XSD version to search by.	 *
	 * @return  array $res The xdis_id
	 */
	function getXDIS_IDByTitleVersion($xdis_title, $xdis_version = 'MODS 1.0')
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                   xdis_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_title = ".$db->quote($xdis_title)." and xdis_version = ".$db->quote($xdis_version);
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
	 * Method used to get the maximum XSD Display ID
	 *
	 * @access  public
	 * @return  array The XSD Display max id
	 */
	function getMaxID()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    max(xdis_id)
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display";
		try {
			$res = $db->fetchOne($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the details of a specific XSD Display.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The details of the XSD Display
	 */
	function getDetails($xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id=".$db->quote($xdis_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	function getAllDetails($xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_display left join
                    " . APP_TABLE_PREFIX . "xsd on xdis_xsd_id = xsd_id 
                 WHERE
                    xdis_id=".$db->quote($xdis_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	function exportDisplays(&$xnode, $xsd_id, $xdis_ids)
	{
		$list = XSD_Display::getList($xsd_id);
		$xcount = 0;
		foreach ($list as $item) {
			$xdis = $xnode->ownerDocument->createElement('display');
			if (in_array($item['xdis_id'], $xdis_ids)) {
				$xdis->setAttribute('xdis_id', $item['xdis_id']);
				$xdis->setAttribute('xdis_title', $item['xdis_title']);
				$xdis->setAttribute('xdis_version', $item['xdis_version']);
				$xdis->setAttribute('xdis_enabled', $item['xdis_enabled']);
				$xdis->setAttribute('xdis_object_type', $item['xdis_object_type']);
				XSD_HTML_Match::exportMatchFields($xdis, $item['xdis_id']);
				Citation::export($xdis, $item['xdis_id']);
				$xnode->appendChild($xdis);
				$xcount++;
			}
		}
		return $xcount;
	}

	function listImportFile($xsd_id, &$xdoc)
	{
		$xpath = new DOMXPath($xdoc->ownerDocument);
		$xdisplays = $xpath->query('display', $xdoc);
		foreach ($xdisplays as $xdis) {
			$item = array(
                'xdis_id' => $xdis->getAttribute('xdis_id'),
                'xdis_title' => $xdis->getAttribute('xdis_title'),
                'xdis_version' => $xdis->getAttribute('xdis_version')
			);
			$item['exists_list'] = XSD_Display::getList($xsd_id,
                "AND xdis_title='".$item['xdis_title']."' AND xdis_version='".$item['xdis_version']."'");
			if (!empty($item['exists_list'])) {
				$item['overwrite'] = true;
			} else {
				$item['overwrite'] = false;
			}
			$list[] = $item;
		}
		return $list;
	}

	/**
	 * Need two passes, first pass inserts everything
	 * Second pass needs to correct links in pretty much all the tables.
	 * A good way to do this would be to keep tables of mapped ids, at the end we go through the tables and
	 * make sure any inserted items point to the right things.  NOTE: queries must ensure that only inserted
	 * items are updated - we don't want to change exisiting items to point to new items by accident
	 */
	function importDisplays($xdoc, $xsd_id, $xdis_ids, &$maps, &$bgp)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$xpath = new DOMXPath($xdoc->ownerDocument);
		$xdisplays = $xpath->query('display', $xdoc);
		foreach ($xdisplays as $xdis) {
			$title = $xdis->getAttribute('xdis_title');
			$version = $xdis->getAttribute('xdis_version');
			if (!in_array($xdis->getAttribute('xdis_id'), $xdis_ids)) {
				$bgp->setStatus("Skipping Display ".$title);
				continue;
			}
			$found_matching_title = false;
			$list = XSD_Display::getList($xsd_id,"AND xdis_title=".$db->quote($title)." AND xdis_version=".$db->quote($version)." ");
			if (!empty($list)) {
				$found_matching_title = true;
				$xdis_id = $list[0]['xdis_id'];
			}

			$params = array(
                'xdis_xsd_id' => $xsd_id,
                'xdis_title' => $title,
                'xdis_version' => $xdis->getAttribute('xdis_version'),
                'xdis_enabled' => $xdis->getAttribute('xdis_enabled'),
                'xdis_object_type' => $xdis->getAttribute('xdis_object_type'),
			);
			if ($found_matching_title) {
				$bgp->setStatus("Overwriting Display $title $version");
				XSD_Display::update($xdis_id, $params);
				// need to delete any matchfields that refer to this xdis as we are about to bring
				// the ones from the XML doc in
				XSD_HTML_Match::removeByXDIS_ID($xdis_id);
			} else {
				$bgp->setStatus("Inserting Display $title $version");
				// need to try and insert at the xdis_id in the XML.  If there's something there already
				// then we know it doesn't match so do a insert with new id in that case
				$det = XSD_Display::getDetails($xdis->getAttribute('xdis_id'));
				if (empty($det)) {
					$xdis_id = $xdis->getAttribute('xdis_id');
					XSD_Display::insertAtId($xdis_id, $xsd_id, $params);
				} else {
					$xdis_id = XSD_Display::insert($xsd_id, $params);
				}
			}
			$maps['xdis_map'][$xdis->getAttribute('xdis_id')] = $xdis_id;
			XSD_HTML_Match::importMatchFields($xdis, $xdis_id, $maps);
			Citation::import($xdis, $xdis_id, $maps);
		}
	}


}

/**
 * XSD_DisplayObject
 * Manage access to the display tables in the database.
 */
class XSD_DisplayObject
{
	var $xdis_id;
	var $xsd_html_match;
	var $exclude_list;
	var $specify_list;

	/**
	 * XSD_DisplayObject
	 * Instantiate with a display id
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display of the object
	 * @return  void
	 */
	function XSD_DisplayObject($xdis_id)
	{
		$log = FezLog::get();
		
		$this->xdis_id = $xdis_id;
	}

	/**
	 * refresh
	 * Clear the local copies of query results.  Use to make the object requery the database if it has changed.
	 *
	 * @access  public
	 * @return  void
	 */
	function refresh()
	{
		$log = FezLog::get();
		
		$this->retrieved_mf = false;
		$this->xsdmf_array = array();
	}

	/**
	 * getMatchFieldsList
	 * Get the list of fields that can be matched for this display.
	 *
	 * @access  public
	 * @param   array optional $exclude_list The list of datastream IDs to exclude, takes preference over the specify list
	 * @param   array optional $specify_list The list of datastream IDs to specify
	 * @return  array $res The list of fields that can be matched by the display
	 */
	function getMatchFieldsList($exclude_list=array(), $specify_list=array())
	{
		$log = FezLog::get();
		
		$this->exclude_list = $exclude_list;
		$this->specify_list = $specify_list;
		$res = XSD_HTML_Match::getListByDisplay($this->xdis_id, $exclude_list, $specify_list);
		return $res;
	}

	/**
	 * getXsdAsReferencedArray
	 * Converts an XSD specification file to an array
	 *
	 * @access  public
	 * @return  array An array of XSD details
	 */
	function getXsdAsReferencedArray()
	{
		$log = FezLog::get();
		
		$xdis_id = $this->xdis_id;
		$xsd_id = XSD_Display::getParentXSDID($xdis_id);
		$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
		$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
		$xsd_top_element_name = $xsd_details['xsd_top_element_name'];
		$xsd_extra_ns_prefixes = explode(",", $xsd_details['xsd_extra_ns_prefixes']);
		$xsd_str = Doc_Type_XSD::getXSDSource($xsd_id);
		$xsd_str = $xsd_str[0]['xsd_file'];

		$xsd = new DomDocument();
		$xsd->loadXML($xsd_str);

		if ($xsd_element_prefix != "") {
			$xsd_element_prefix .= ":";
		}
		$xml_schema = Misc::getSchemaAttributes($xsd, $xsd_top_element_name, $xsd_element_prefix, $xsd_extra_ns_prefixes); // for the namespace uris etc
		$array_ptr = array();
		Misc::dom_xsd_to_referenced_array($xsd, $xsd_top_element_name, &$array_ptr, "", "", $xsd);
		return array($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema);
	}

	function getXSD()
	{
		$log = FezLog::get();
		
		if (!$this->xsd_id) {
			$this->xsd_id = XSD_Display::getParentXSDID($this->xdis_id);
			$this->xsd_details = Doc_Type_XSD::getDetails($this->xsd_id);
		}
		return $this->xsd_details;
	}

	/**
	 * getDatastreamTitles
	 * Get the datastreams that are used with this display.
	 *
	 * @access  public
	 * @return  array A list of datastream titles used with the display
	 */
	function getDatastreamTitles($exclude_list=array(), $specify_list=array())
	{
		$log = FezLog::get();
		
		return XSD_Loop_Subelement::getDatastreamTitles($this->xdis_id,$exclude_list, $specify_list);
	}

	/**
	 * getXSDMF_Values
	 * Return a list of match fields with the values from the datastream for the record with the
	 * given pid.
	 *
	 * @access  public
	 * @param   string $pid The persistent identifier of the record
	 * @return  array The list of match fields with the values from the datastream
	 */
	function getXSDMF_Values($pid, $createdDT=null, $skipIndex = false)
	{

		$log = FezLog::get();
		$this->getXSD_HTML_Match();
		
		//print_r($this->specify_list); echo count($this->specify_list); if ($skipIndex != true) { echo "hai"; }
		if (APP_XSDMF_INDEX_SWITCH == "ON" && $skipIndex != true && count($this->specify_list) == 0) { //echo "MAAA";
			// AN Attempt at seeing what performance would be like by getting all details from the index rather than from fedora, now commented out for future experimentation
			$return = array();
			$options = array();
			$filter = array();
			$filter["searchKey".Search_Key::getID("Pid")] = str_replace(":", "\:", $pid);
			//$filter["searchKey0"] = "UQ\:81784";
			$current_row = 0;
			$max = 1;
			$order_by = "Title";
			$return = Record::getListing($options, array(9,10), $current_row, $max, $order_by, false, false, $filter);
		}
		if (APP_XSDMF_INDEX_SWITCH == "ON" && count($return['list']) > 0 && $skipIndex != true && count($this->specify_list) == 0) {
			$return = $return['list'][0];
			foreach ($return as $sek_id => $value) {
				// test sek id to xsdmf id later
				///echo ucwords(str_replace("_", "", str_replace("rek_", "", $sek_id)))."\n";
				//echo Search_Key::getID(ucwords(str_replace("_", " ", str_replace("rek_", "", $sek_id))))."\n";
				$xdis_list = XSD_Relationship::getColListByXDIS($return['rek_display_type']);
				array_push($xdis_list, $return['rek_display_type']);
				$xdis_str = implode(", ", $xdis_list);
				$xsdmf_array = array();
				$xsdmf_array =  XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID(ucwords(str_replace("_", " ", str_replace("rek_", "", $sek_id)))), $xdis_str);
				foreach ($xsdmf_array as $xsdmf_id) {
					$return_pid[$xsdmf_id] = $value;
				}
			}
			$this->xsdmf_array[$pid] = $return_pid;
			return ($return_pid);
		} else {

			if (APP_XPATH_SWITCH == "ON") {
				if (isset($this->xsdmf_array[$pid])) {
					return;
				}
				$this->xsdmf_array[$pid] = array();
				$this->xsdmf_current = &$this->xsdmf_array[$pid];
				//print_r($this->exclude_list); echo "HERE";
				$this->xsdmf_array[$pid] = XSD_HTML_Match::getDetailsByXPATH($pid, $this->xdis_id, $this->exclude_list, $this->specify_list);
				
				// Now get the Non-XML stuff.. this could be cleaned up 
				
				$this->xsdmf_current = &$this->xsdmf_array[$pid];
				// Find datastreams that may be used by this display
				$datastreamTitles = $this->getDatastreamTitles();

				// need the full get datastreams to get the controlGroup etc
				$datastreams = Fedora_API::callGetDatastreams($pid);
				if (empty($datastreams)) {
					$log->err(array("The PID ".$pid." doesn't appear to be in the fedora repository - perhaps it was not ingested correctly.  " .
		                        "Please let the Fez admin know so that the Fez index can be repaired.",__FILE__,__LINE__));
					return;
				}

				foreach ($datastreams as $ds_value) {
					// get the matchfields for the FezACML of the datastream if any exists
					if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'M') {
						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title("!datastream!ID", "File_Attachment", $this->xdis_id);
						if (!is_array(@$this->xsdmf_current[$xsdmf_id])) {
							$this->xsdmf_current[$xsdmf_id] = array();
						}
						array_push($this->xsdmf_current[$xsdmf_id], $ds_value['ID']);

						$FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
						$FezACML_DS_name = FezACML::getFezACMLDSName($ds_value['ID']);
						if (Fedora_API::datastreamExistsInArray($datastreams, $FezACML_DS_name)) {
							$FezACML_DS = Fedora_API::callGetDatastreamDissemination($pid, $FezACML_DS_name, $createdDT);
							if (isset($FezACML_DS['stream'])) {
								$this->processXSDMFDatastream($FezACML_DS['stream'], $FezACML_xdis_id);
								$this->xsd_html_match->gotMatchCols = false; // make sure it refreshes for the other xsd displays
							}
						}
					}
				}

				foreach ($datastreamTitles as $dsValue) {
					// first check if the XSD Display datastream is a template
					// for a link as these are handled differently
					if ($dsValue['xsdsel_title'] == "DOI") {
						// find the datastream for DOI and set it's value
						$xsdmf_id = $dsValue['xsdmf_id'];

						$xsdmf_details = $this->xsd_html_match->getDetailsByXSDMF_ID($xsdmf_id);

						foreach ($datastreams as $ds) {
							if (isset($ds['controlGroup']) && $ds['controlGroup'] == 'R' && $ds['ID'] == 'DOI') {
								$value = trim($ds['location']);
								if (!empty($value) && strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
									$value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $value);
								}
								$this->xsdmf_current[$xsdmf_id] = $value;
							}
						}

					} elseif ($dsValue['xsdsel_title'] == "Link") {

						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title("!datastream!datastreamVersion!contentLocation", "Link", $this->xdis_id);
						foreach ($datastreams as $ds) {
							if (isset($ds['controlGroup']) && $ds['controlGroup'] == 'R' && is_numeric(strpos($ds['ID'], 'link_'))) {
								$value = trim($ds['location']);
								$value = str_replace("&amp;", "&", $value);
								if (!empty($value) && strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
									$value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $value);
								}
								if (!is_array(@$this->xsdmf_current[$xsdmf_id])) {
									$this->xsdmf_current[$xsdmf_id] = array();
								}
								array_push($this->xsdmf_current[$xsdmf_id], $value);
							}
						}

						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title("!datastream!datastreamVersion!LABEL", "Link", $this->xdis_id);
						foreach ($datastreams as $ds) {
							if (isset($ds['controlGroup']) && $ds['controlGroup'] == 'R' && is_numeric(strpos($ds['ID'], 'link_'))) {
								$value = trim($ds['label']);
								if (!empty($value) && strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
									$value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $value);
								}
								if (!is_array(@$this->xsdmf_current[$xsdmf_id])) {
									$this->xsdmf_current[$xsdmf_id] = array();
								}
								array_push($this->xsdmf_current[$xsdmf_id], $value);
							}
						}

					}
				
				}
			} else {
				$this->processXSDMF($pid, $createdDT);
			}
			return $this->xsdmf_array[$pid];

		}
		exit;

	}

	// To get the values for a specific xml datastream only (eg for when there are many FezACML for datastream values set so they don't get confused)
	function getXSDMF_Values_Datastream($pid, $dsID, $createdDT=null)
	{
		$log = FezLog::get();
		

		if (!isset($this->xsdmf_array[$pid])) {
			$this->xsdmf_array[$pid] = array();
			$this->xsdmf_current = &$this->xsdmf_array[$pid];
			$this->getXSD_HTML_Match();
		}

		$FezACML_DS_name = FezACML::getFezACMLDSName($dsID);
		if (Fedora_API::datastreamExists($pid, $FezACML_DS_name)) {
			$FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
			$FezACML_DS = Fedora_API::callGetDatastreamDissemination($pid, $FezACML_DS_name, $createdDT);
			if (isset($FezACML_DS['stream'])) {
				$this->processXSDMFDatastream($FezACML_DS['stream'], $FezACML_xdis_id);
			}
			return $this->xsdmf_array[$pid];
		} else {
			return array();
		}
	}


	function getXSD_HTML_Match()
	{
		$log = FezLog::get();

		if (!$this->xsd_html_match) {
			$xdis_list = XSD_Relationship::getListByXDIS($this->xdis_id);
			array_push($xdis_list, array("0" => $this->xdis_id));
			$xdis_str = Misc::sql_array_to_string($xdis_list);
			$this->xsd_html_match = new XSD_HTML_MatchObject($xdis_str);
		}
		return $this->xsd_html_match;
	}

	/**
	 * processXSDMF
	 * Get the values from elements in the datastreams that match against the match fields
	 * for this display
	 *
	 * @access  public
	 * @param   string $pid The persistent identifier of the record
	 * @return  void
	 */
	function processXSDMF($pid, $createdDT=null)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (isset($this->xsdmf_array[$pid])) {
			return;
		}

		$this->xsdmf_array[$pid] = array();
		$this->xsdmf_current = &$this->xsdmf_array[$pid];
		$this->getXSD_HTML_Match();

		// Find datastreams that may be used by this display
		$datastreamTitles = $this->getDatastreamTitles();

		// need the full get datastreams to get the controlGroup etc
		$datastreams = Fedora_API::callGetDatastreams($pid);
		if (empty($datastreams)) {
			$log->err(array("The PID ".$pid." doesn't appear to be in the fedora repository - perhaps it was not ingested correctly.  " .
                        "Please let the Fez admin know so that the Fez index can be repaired.",__FILE__,__LINE__));
			return;
		}
		 
		foreach ($datastreams as $ds_value) {
			// get the matchfields for the FezACML of the datastream if any exists
			if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'M') {
				$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title("!datastream!ID", "File_Attachment", $this->xdis_id);
				if (!is_array(@$this->xsdmf_current[$xsdmf_id])) {
					$this->xsdmf_current[$xsdmf_id] = array();
				}
				array_push($this->xsdmf_current[$xsdmf_id], $ds_value['ID']);

				$FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
				$FezACML_DS_name = FezACML::getFezACMLDSName($ds_value['ID']);
				if (Fedora_API::datastreamExistsInArray($datastreams, $FezACML_DS_name)) {
					$FezACML_DS = Fedora_API::callGetDatastreamDissemination($pid, $FezACML_DS_name, $createdDT);
					if (isset($FezACML_DS['stream'])) {
						$this->processXSDMFDatastream($FezACML_DS['stream'], $FezACML_xdis_id);
						$this->xsd_html_match->gotMatchCols = false; // make sure it refreshes for the other xsd displays
					}
				}
			}
		}
			
		foreach ($datastreamTitles as $dsValue) {
			// first check if the XSD Display datastream is a template
			// for a link as these are handled differently
			if ($dsValue['xsdsel_title'] == "DOI") {
				// find the datastream for DOI and set it's value
				$xsdmf_id = $dsValue['xsdmf_id'];

				$xsdmf_details = $this->xsd_html_match->getDetailsByXSDMF_ID($xsdmf_id);

				foreach ($datastreams as $ds) {
					if (isset($ds['controlGroup']) && $ds['controlGroup'] == 'R' && $ds['ID'] == 'DOI') {
						$value = trim($ds['location']);
						if (!empty($value) && strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
							$value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $value);
						}
						$this->xsdmf_current[$xsdmf_id] = $value;
					}
				}
					
			} elseif ($dsValue['xsdsel_title'] == "Link") {

				$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title("!datastream!datastreamVersion!contentLocation", "Link", $this->xdis_id);
				foreach ($datastreams as $ds) {
					if (isset($ds['controlGroup']) && $ds['controlGroup'] == 'R' && is_numeric(strpos($ds['ID'], 'link_'))) {
						$value = trim($ds['location']);
						$value = str_replace("&amp;", "&", $value);
						if (!empty($value) && strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
							$value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $value);
						}
						if (!is_array(@$this->xsdmf_current[$xsdmf_id])) {
							$this->xsdmf_current[$xsdmf_id] = array();
						}
						array_push($this->xsdmf_current[$xsdmf_id], $value);
					}
				}
					
				$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title("!datastream!datastreamVersion!LABEL", "Link", $this->xdis_id);
				foreach ($datastreams as $ds) {
					if (isset($ds['controlGroup']) && $ds['controlGroup'] == 'R' && is_numeric(strpos($ds['ID'], 'link_'))) {
						$value = trim($ds['label']);
						if (!empty($value) && strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
							$value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $value);
						}
						if (!is_array(@$this->xsdmf_current[$xsdmf_id])) {
							$this->xsdmf_current[$xsdmf_id] = array();
						}
						array_push($this->xsdmf_current[$xsdmf_id], $value);
					}
				}

			} else {
				 
				// find out if this record has the xml based datastream
				if (Fedora_API::datastreamExistsInArray($datastreams, $dsValue['xsdsel_title'])) {
					$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsValue['xsdsel_title'], $createdDT);
					if (isset($DSResultArray['stream'])) {
						$xmlDatastream = $DSResultArray['stream'];
						// get the matchfields for the datastream (using the sub-display for this stream)
						$this->processXSDMFDatastream($xmlDatastream, $dsValue['xsdrel_xdis_id']);
					} else {
						$log->err(array("Couldn't get ".$dsValue['xsdsel_title']." on ".$pid,__FILE__,__LINE__));
					}
				}
			}
		}
	}

	/**
	 * processXSDMFDatastream
	 * Find values for all the matchfields on a given Datastream and xdis_id
	 *
	 * @access  public
	 * @return  void
	 */
	function processXSDMFDatastream($xmlDatastream, $xsdmf_xdis_id)
	{
		$log = FezLog::get();
		
		$xsd_id = XSD_Display::getParentXSDID($xsdmf_xdis_id);
		$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
		$temp_xdis_str = $this->xsd_html_match->xdis_str;
		$temp_xdis_id = $this->xdis_id;

		if (!in_array($xsdmf_xdis_id, explode(",", $this->xsd_html_match->xdis_str))) {
			$this->xdis_id = $xsdmf_xdis_id;
			$this->xsd_html_match->xdis_str = $xsdmf_xdis_id;
		}

		$this->xsd_element_prefix = $xsd_details['xsd_element_prefix'];
		$this->xsd_top_element_name = $xsd_details['xsd_top_element_name'];
		$xmlnode = new DomDocument();
		@$xmlnode->loadXML($xmlDatastream);
		$cbdata = array(
	        'parentContent' => '', 
	        'parent_key'    => '', 
	        'xdis_id'       => $xsdmf_xdis_id
		);
		$this->mfcb_rootdone = false;

		Misc::XML_Walk($xmlnode, $this, 'matchFieldsCallback', $cbdata, $xmlnode);

		$this->xsd_html_match->xdis_str = $temp_xdis_str;
		$this->xdis_id = $temp_xdis_id;
	}

	/**
	 * matchFieldsCallback
	 * Used by XML_Walk to recurse through an xsd and work out the match fields.
	 *
	 * @param array $cbdata - data that is passed to each callback but is part of the recursive data - i.e. it is
	 * not remembered when recursing out.  The record object itself stores data that should persist while recursing.
	 *
	 * @access  public
	 * @param DomNode $domNode The node of the dom document
	 * @param array $cbdata The XSD array to be filled
	 * @param string $context The callback context
	 * @param rootNode $domNode The root dom document
	 * @return  array $cbdata The XSD array being filled.
	 */
	function matchFieldsCallback($domNode, $cbdata, $context=NULL, $rootNode)
	{
		$log = FezLog::get();
		
		$clean_nodeName = Misc::strip_element_name($domNode->nodeName);
		$xsdmf_ptr = &$this->xsdmf_current; // stores results
		$xsdmf_id = NULL;
		$currentSEL = "";
		// look for the xsdmf_id
		switch ($domNode->nodeType)
		{
			case XML_ELEMENT_NODE:
				switch ($context) {
					case 'startopen':
						// this is processed before we have walked the attributes for this element
						// Store the current node name for use when called back for the attributes.
						$cbdata['clean_nodeName'] = $clean_nodeName;
						$parentContent = $cbdata['parentContent'];
						if ((is_numeric(strpos(substr($parentContent, 0, 1), "!"))) || ($parentContent == "")) {
							$new_element = $parentContent."!".$clean_nodeName;
						} else {
							$new_element = "!".$parentContent."!".$clean_nodeName;
						}

						if (!is_numeric(@$cbdata['currentSEL'])) {
							$xsdmf_id = $this->xsd_html_match->getXSDMFByElement($new_element,$cbdata['xdis_id']);
							if (is_array($xsdmf_id)) {
								if (count($xsdmf_id) > 1) {
									foreach ($xsdmf_id as $row) {
										if ($row['xsdmf_html_input'] == 'xsd_loop_subelement' && is_numeric($row['xsdsel_indicator_xsdmf_id']) && $row['xsdsel_indicator_xsdmf_id'] != 0 && $row['xsdsel_indicator_value'] != '') {
											if ($row['xsd_element_prefix'] != "") {
												$indicator_xpath = $row['xsd_element_prefix'].":".ltrim(str_replace("!", "/".$row['xsd_element_prefix'].":", $row['indicator_element']), "/");
											} else {
												$indicator_xpath = $row['xsd_element_prefix'].":".ltrim(str_replace("!", "/", $row['indicator_element']), "/");
											}
											$currentNodeLength = strlen($domNode->nodeName);
											$currentNodePos = strpos($indicator_xpath, $domNode->nodeName);
											$indicator_xpath = ".".substr($indicator_xpath, $currentNodePos + $currentNodeLength);
											$xpath = new DOMXPath($rootNode);
											$xpath->registerNamespace("mods", "http://www.loc.gov/mods/v3");
												
											$indicatorNodes = $xpath->query($indicator_xpath, $domNode);
											if ($indicatorNodes->length > 0) {
												$indicatorValue = $indicatorNodes->item(0)->nodeValue; //should only ever be one search result in the array
												if ($indicatorValue == $row['xsdsel_indicator_value']) {
													$currentSEL = $row['indicator_xsdsel_id'];
												}
											} else { // search for attributes next
												$attribPos = strrpos($indicator_xpath, "/");
												if (is_numeric($attribPos)) {
													$attrib = substr($indicator_xpath, $attribPos+1);
													$indicator_xpath = substr($indicator_xpath, 0, $attribPos);
													$attrib = "@".str_replace($row['xsd_element_prefix'].":", "", $attrib);
													$indicator_xpath .= "/".$attrib;
													$indicatorNodes = $xpath->query($indicator_xpath, $domNode);
													if ($indicatorNodes->length > 0) {
														$indicatorValue = $indicatorNodes->item(0)->nodeValue; //should only ever be one search result in the array
														if ($indicatorValue == $row['xsdsel_indicator_value']) {
															$currentSEL = $row['indicator_xsdsel_id'];
														}
													}
												}
											}
										} elseif (!is_numeric($currentSEL) && $row['xsdmf_html_input'] == 'xsd_loop_subelement' && $row['xsdsel_indicator_value'] == '') {
											$currentSEL = $row['indicator_xsdsel_id'];
										}
									}
									if (is_numeric($currentSEL)) {
										$cbdata['currentSEL'] = $currentSEL;
									}
								}
							}
						}
						$xsdmf_id = NULL;
						break;
					case 'endopen':
						// this is processed after we have walked the attributes for this element
						{
							$parentContent = $cbdata['parentContent'];
							if ((is_numeric(strpos(substr($parentContent, 0, 1), "!"))) || ($parentContent == "")) {
								$new_element = $parentContent."!".$clean_nodeName;
							} else {
								$new_element = "!".$parentContent."!".$clean_nodeName;
							}

							if ($cbdata['parent_key'] != "") {
								$xsdmf_id = $this->xsd_html_match->getXSDMF_IDByParentKeyXDIS_ID($new_element, $cbdata['parent_key']);
							} elseif (is_numeric(@$cbdata['currentSEL'])) {
								$xsdmf_id = $this->xsd_html_match->getXSDMF_IDBySELXDIS_ID($new_element, $cbdata['currentSEL']);
							} else {
								$xsdmf_id = $this->xsd_html_match->getXSDMFByElement($new_element,$cbdata['xdis_id']);
								if (is_array($xsdmf_id)) {
									if (count($xsdmf_id) == 1) {
										$xsdmf_id = @$xsdmf_id[0]['xsdmf_id'];
									}
								}
							}
						}
						break;
					case 'close':
						// this is processed after have walked the attributes and children for this element
						return $cbdata;
						break;
				}
				break;

					case XML_ATTRIBUTE_NODE:
						if ((is_numeric(strpos(substr($cbdata['parentContent'], 0, 1), "!"))) || ($cbdata['parentContent'] == "")) {
							$new_element = $cbdata['parentContent']."!".$cbdata['clean_nodeName']."!".$clean_nodeName;
						} else {
							$new_element = "!".$cbdata['parentContent']."!".$cbdata['clean_nodeName']."!".$clean_nodeName;
						}

						// Is there a match field for this attribute?
						// look for key match on the attribute value first - this is where the matchfield needs the
						// attribute to be set to a certain value to match.
						$xsdmf_id = $this->xsd_html_match->getXSDMF_IDByKeyXDIS_ID($new_element, $domNode->nodeValue);
						if (!empty($xsdmf_id)) {
							break;
						}

						// look for a straight attribute match
						if (is_numeric(@$cbdata['currentSEL'])) {
							$xsdmf_id = $this->xsd_html_match->getXSDMF_IDBySELXDIS_ID($new_element, $cbdata['currentSEL']);
						} else {
							$xsdmf_id = $this->xsd_html_match->getXSDMFByElement($new_element,$cbdata['xdis_id']);
							if (is_array($xsdmf_id)) {
								if (count($xsdmf_id) > 1) {
									// ##### Start of suspect block of code
									foreach ($xsdmf_id as $row) {
										if ($row['xsdmf_html_input'] == 'xsd_loop_subelement' && is_numeric($row['xsdsel_indicator_xsdmf_id']) && $row['xsdsel_indicator_xsdmf_id'] != 0 && $row['xsdsel_indicator_value'] != "") {
											$indicator_xpath = $row['xsd_element_prefix'].":".ltrim(str_replace("!", "/".$row['xsd_element_prefix'].":", $row['indicator_element']), "/");
											$currentNodeLength = strlen($domNode->nodeName);
											$currentNodePos = strpos($indicator_xpath, $domNode->nodeName);
											$indicator_xpath = ".".substr($indicator_xpath, $currentNodePos + $currentNodeLength);
											$xpath = new DOMXPath($rootNode);
											$xpath->registerNamespace("mods", "http://www.loc.gov/mods/v3");
											$indicatorNodes = $xpath->query($indicator_xpath, $domNode);
											if ($indicatorNodes->length > 0) {
												$indicatorValue = $indicatorNodes->item(0)->nodeValue; //should only ever be one search result in the array
												if ($indicatorValue == $row['xsdsel_indicator_value']) {
													$currentSEL = $row['indicator_xsdsel_id'];
												}
											}
										}
									}
									if (is_numeric($currentSEL)) {
										$xsdmf_id = $this->xsd_html_match->getXSDMF_IDBySELXDIS_ID($new_element, $currentSEL);
									}
									// ##### End of suspect block of code
								} else {
									$xsdmf_id = $xsdmf_id[0]['xsdmf_id'];
								}
							}
						}
							
						if (empty($xsdmf_id)) {
							// if still can't find it, try it further up the tree -
							// eg for MODS name|ID looked for in name|namePart
							$new_element = "!".$cbdata['parentContent']."!".$clean_nodeName;
							$xsdmf_id = $this->xsd_html_match->getXSDMF_IDByXDIS_ID($new_element);
						}

						break;

					default:
						return $cbdata;
						break;
		}

		if (is_numeric($xsdmf_id)) {
			// We have found a match!
			// Get the value for the match and store it in the result
			$xsdmf_details = $this->xsd_html_match->getDetailsByXSDMF_ID($xsdmf_id);
			if (strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
				$ptr_value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $domNode->nodeValue);
			} else {
				$ptr_value = $domNode->nodeValue;
			}
      // death to -1 values
      if ($ptr_value == '-1') {
        $ptr_value = '';
      }


			// we want to return an 'off' for elements that correspond to checkboxes if they are empty,
			// as this is meaningful, while non-checkbox types empty is not worth indexing/returning
			if ($xsdmf_details['xsdmf_html_input'] == 'checkbox') {
				//if the xml exists (has been saved) then if it is empty it means the checkbox is off
				// (if the xml didnt exist it would mean on, but wouldn't get to this code area)
				if ($ptr_value != "on") {
					$ptr_value = "off";
				}
			}

			// Store the matchfields value against the matchfield id in the result array.
			// If there's already a value for this match field, then make an array for the value.
			if (isset($xsdmf_ptr[$xsdmf_id])) {
				if (is_array($xsdmf_ptr[$xsdmf_id])) {
					// add to the array of values
					$xsdmf_ptr[$xsdmf_id][] = $ptr_value;
				} else {
					// make an array from the single value
					$xsdmf_ptr[$xsdmf_id] = array($xsdmf_ptr[$xsdmf_id], $ptr_value);
				}
			} else {
				// store the value
				$xsdmf_ptr[$xsdmf_id] = $ptr_value;
			}
		}
		if ((($domNode->nodeType == XML_ELEMENT_NODE) && ($context == 'endopen'))
		|| $domNode->nodeType == XML_ATTRIBUTE_NODE) {
			// Store the parent key for key match fields.
			if (!empty($xsdmf_details)) {
				if (($xsdmf_details['xsdmf_is_key'] == 1) && ($xsdmf_details['xsdmf_key_match'] != '')) {
					$cbdata['parent_key'] = $xsdmf_details['xsdmf_key_match'];
				}
			}
			// Store the indicator sublooping element further down the tree.
			if ($currentSEL != '') {
				$cbdata['currentSEL'] = $currentSEL;
			}

		}
		if (($domNode->nodeType == XML_ELEMENT_NODE) && ($context == 'endopen')) {
			// update the parentContent match path
			if (!$this->mfcb_rootdone) {
				$cbdata['parentContent'] = "";
				$this->mfcb_rootdone = true;
			} else {
				if ($cbdata['parentContent'] != "") {
					$cbdata['parentContent'] = $cbdata['parentContent']."!".$clean_nodeName;
				} else {
					$cbdata['parentContent'] = $clean_nodeName;
				}
			}

		}
		return $cbdata;
	}

	function getTitle()
	{
		$log = FezLog::get();
		
		return XSD_Display::getTitle($this->xdis_id);
	}

	function getXSDMFDetailsByElement($xsdmf_element)
	{
		$log = FezLog::get();
		
		$this->getXSD_HTML_Match();
		return $this->xsd_html_match->getDetailsByElement($xsdmf_element);
	}
	function getXSDMFDetailsByXSDMF_ID($xsdmf_id)
	{
		$log = FezLog::get();
		
		$this->getXSD_HTML_Match();
		return $this->xsd_html_match->getDetailsByXSDMF_ID($xsdmf_id);
	}
}
