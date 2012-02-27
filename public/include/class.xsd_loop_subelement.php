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
 * of sublooping elements in the system.
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

class XSD_Loop_Subelement
{
	/**
	 * Method used to get the list of sublooping elements associated with
	 * a given display id.
	 *
	 * @access  public
	 * @param   integer $xsdmf_id The XSD matching field ID
	 * @return  array The list of matching fields fields
	 */
	function getListByXSDMF($xsdmf_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
					s1.*, m1.*, d1.*, ";
					
    if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
 			$stmt .= "
					IFNULL(('(' || s1.xsdsel_attribute_loop_xsdmf_id || ') (' || s2.xsdsel_title || ') ' || m2.xsdmf_element), ('(' || m2.xsdmf_id || ') ' || m2.xsdmf_element)) as xsdmf_attribute_loop_presentation,
					IFNULL(('(' || s1.xsdsel_indicator_xsdmf_id || ') (' || s3.xsdsel_title || ') ' || m3.xsdmf_element), ('(' || m3.xsdmf_id || ') ' || m3.xsdmf_element)) as xsdmf_indicator_presentation ";
		} else {
			$stmt .= "
					IFNULL(CONCAT('(', s1.xsdsel_attribute_loop_xsdmf_id, ') (', s2.xsdsel_title, ') ', m2.xsdmf_element), CONCAT('(', m2.xsdmf_id, ') ', m2.xsdmf_element)) as xsdmf_attribute_loop_presentation,
					IFNULL(CONCAT('(', s1.xsdsel_indicator_xsdmf_id, ') (', s3.xsdsel_title, ') ', m3.xsdmf_element), CONCAT('(', m3.xsdmf_id, ') ', m3.xsdmf_element)) as xsdmf_indicator_presentation ";			
		}
		$stmt .= "
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement s1 inner join
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields m1 on (s1.xsdsel_xsdmf_id = m1.xsdmf_id) and (s1.xsdsel_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER').") inner join
                    " . APP_TABLE_PREFIX . "xsd_display d1 on (m1.xsdmf_xdis_id = d1.xdis_id) left join
					" . APP_TABLE_PREFIX . "xsd_display_matchfields m2 on (m2.xsdmf_id = s1.xsdsel_attribute_loop_xsdmf_id) left join 
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement s2 on (m2.xsdmf_xsdsel_id = s2.xsdsel_id) left join
					" . APP_TABLE_PREFIX . "xsd_display_matchfields m3 on (m3.xsdmf_id = s1.xsdsel_indicator_xsdmf_id) left join 
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement s3 on (m3.xsdmf_xsdsel_id = s3.xsdsel_id)
			
					";
		// @@@ CK - Added order statement to sublooping elements displayed in a desired order
		$stmt .= " ORDER BY s1.xsdsel_order ASC";
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
	 * Method used to get the list of sublooping elements associated with
	 * a given display id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The list of matching fields fields
	 */
	function getSimpleListByXSDMF($xsdmf_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
					*
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement
                 WHERE
                    xsdsel_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');
		$stmt .= " ORDER BY xsdsel_order ASC";

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
	 * Method used to get the list of sublooping elements associated with
	 * a given display id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The list of matching fields fields
	 */
	function getSELIDsByXSDMF($xsdmf_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
					xsdsel_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement
                 WHERE
                    xsdsel_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');
		// @@@ CK - Added order statement to sublooping elements displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order ASC";
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}


	/**
	 * Method used to get the list of sublooping elements associated with
	 * a given display id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The list of matching fields
	 */
	function getDatastreamTitles($xdis_id, $exclude_list=array(), $specify_list=array())
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$exclude_str = implode("', '", $exclude_list);
		$specify_str = implode("', '", $specify_list);

		// Get the datastream titles and xdisplay ids that are references to other display ids, and also get any binary content (file upload/select) datastreams
		$stmt = "SELECT	m1.xsdmf_id,
						m1.xsdmf_xdis_id,
						s1.xsdsel_title,
						s1.xsdsel_id,
            			xsdrel_xdis_id        
				FROM ". APP_TABLE_PREFIX . "xsd_loop_subelement s1
				INNER JOIN " . APP_TABLE_PREFIX . "xsd_display_matchfields m1 
                            ON m1.xsdmf_element in ('!datastream!datastreamVersion!xmlContent', '!datastream!datastreamVersion!contentLocation', '!datastream!datastreamVersion!binaryContent')    
                            AND m1.xsdmf_xdis_id=".$db->quote($xdis_id, 'INTEGER')." AND s1.xsdsel_id = m1.xsdmf_xsdsel_id 
                LEFT JOIN " . APP_TABLE_PREFIX . "xsd_relationship ON xsdrel_xsdmf_id = m1.xsdmf_id
				LEFT JOIN " . APP_TABLE_PREFIX . "xsd_display ON xsdrel_xdis_id = xdis_id ";

		if ($specify_str != "") {
			$stmt .= " WHERE s1.xsdsel_title in ('".$specify_str."')";
		} elseif ($exclude_str != "") {
			$stmt .= " WHERE s1.xsdsel_title not in ('".$exclude_str."')";
		}
		// @@@ CK - Added order statement to sublooping elements displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order ASC";
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
	 * Method used to get the list of sublooping elements associated with
	 * a given display id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The list of matching fields
	 */
	function getDatastreamTitle($xdis_id, $dsTitle)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		// Get the datastream titles and xdisplay ids that are references to other display ids, and also get any binary content (file upload/select) datastreams
		$stmt = "SELECT	m1.xsdmf_id, m1.xsdmf_xdis_id,
			s1.xsdsel_title,
			s1.xsdsel_id
						 FROM
							" . APP_TABLE_PREFIX . "xsd_loop_subelement s1,
							" . APP_TABLE_PREFIX . "xsd_display_matchfields m1
		WHERE 
		m1.xsdmf_element in ('!datastream!datastreamVersion!xmlContent', '!datastream!datastreamVersion!contentLocation', '!datastream!datastreamVersion!binaryContent') 	
		AND m1.xsdmf_xdis_id=".$db->quote($xdis_id, 'INTEGER')."	and s1.xsdsel_id = m1.xsdmf_xsdsel_id and s1.xsdsel_title = ".$db->quote($dsTitle);

		// @@@ CK - Added order statement to sublooping elements displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order ASC";
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to get the list of sublooping elements associated with
	 * a given display id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The list of matching fields fields
	 */
	function getTopParentLoopList($xml_element, $xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
					s1.*, m1.*, m2.xsdmf_id as child_xsdmf_id
                FROM " . APP_TABLE_PREFIX . "xsd_loop_subelement s1 
                inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields m1 
                    on s1.xsdsel_xsdmf_id = m1.xsdmf_id and m1.xsdmf_xdis_id = ".$db->quote($xdis_id, 'INTEGER')." 
					and (INSTR(".$db->quote($xml_element).", m1.xsdmf_element) = 1) 
					and m1.xsdmf_html_input = 'xsd_loop_subelement' 
				left join " . APP_TABLE_PREFIX . "xsd_display_matchfields m2 
					on (m2.xsdmf_xsdsel_id = s1.xsdsel_id) and (m2.xsdmf_element = ".$db->quote($xml_element).")
    	        ORDER BY xsdsel_order ASC";
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
	 * Method used to remove a given list of sublooping elements, and any child matching fields under that element.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$return = true;

		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement
                 WHERE
                    xsdsel_id  IN (" . Misc::arrayToSQLBindStr($_POST["items"]) . ")";
		try {
			$db->query($stmt,$_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$return = false;
		}

		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_xsdsel_id  IN (" . Misc::arrayToSQLBindStr($_POST["items"]) . ")";

		try {
			$db->query($stmt,$_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$return = false;
		}
		return $return;
	}


	/**
	 * Method used to add a new sublooping element to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement
                 (
                    xsdsel_xsdmf_id,
                    xsdsel_title,
                    xsdsel_type,";
		if (is_numeric($_POST["xsdsel_attribute_loop_xdis_id"])) {
			$stmt .= "xsdsel_attribute_loop_xdis_id,";
		}
		if (is_numeric($_POST["xsdsel_attribute_loop_xsdmf_id"])) {
			$stmt .= "xsdsel_attribute_loop_xsdmf_id,";
		}
		if (is_numeric($_POST["xsdsel_indicator_xdis_id"])) {
			$stmt .= "xsdsel_indicator_xdis_id,";
		}
		if (is_numeric($_POST["xsdsel_indicator_xsdmf_id"])) {
			$stmt .= "xsdsel_indicator_xsdmf_id,";
		}
		$stmt .= "xsdsel_indicator_value,";
		$stmt .=" xsdsel_order
                 ) VALUES (
                    " . $db->quote($_POST["xsdsel_xsdmf_id"], 'INTEGER') . ",
                    " . $db->quote($_POST["xsdsel_title"]) . ",
                    " . $db->quote($_POST["xsdsel_type"]) . ",";
		if (is_numeric($_POST["xsdsel_attribute_loop_xdis_id"])) {
			$stmt .=  $db->quote($_POST["xsdsel_attribute_loop_xdis_id"], 'INTEGER') . ",";
		}
		if (is_numeric($_POST["xsdsel_attribute_loop_xsdmf_id"])) {
			$stmt .=  $db->quote($_POST["xsdsel_attribute_loop_xsdmf_id"], 'INTEGER') . ",";
		}
		if (is_numeric($_POST["xsdsel_indicator_xdis_id"])) {
			$stmt .=  $db->quote($_POST["xsdsel_indicator_xdis_id"], 'INTEGER') . ",";
		}
		if (is_numeric($_POST["xsdsel_indicator_xsdmf_id"])) {
			$stmt .=  $db->quote($_POST["xsdsel_indicator_xsdmf_id"], 'INTEGER') . ",";
		}
		$stmt .= $db->quote($_POST["xsdsel_indicator_value"]) . ",";
		$stmt .= $db->quote($_POST["xsdsel_order"]) . ")";

		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
	}

	/**
	 * Method used to add a new sublooping element to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insertFromArray($xsdmf_id, $insertArray)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement(
                    xsdsel_xsdmf_id,
                    xsdsel_title,
                    xsdsel_type,";
		if (is_numeric($insertArray["xsdsel_attribute_loop_xdis_id"])) {
			$stmt .= "xsdsel_attribute_loop_xdis_id,";
		}
		if (is_numeric($insertArray["xsdsel_attribute_loop_xsdmf_id"])) {
			$stmt .= "xsdsel_attribute_loop_xsdmf_id,";
		}
		if (is_numeric($insertArray["xsdsel_indicator_xdis_id"])) {
			$stmt .= "xsdsel_indicator_xdis_id,";
		}
		if (is_numeric($insertArray["xsdsel_indicator_xsdmf_id"])) {
			$stmt .= "xsdsel_indicator_xsdmf_id,";
		}
		$stmt .= "xsdsel_indicator_value,";
		$stmt .="xsdsel_order
                 ) VALUES (
                    " . $db->quote($xsdmf_id, 'INTEGER') . ",
                    " . $db->quote($insertArray["xsdsel_title"]) . ",
                    " . $db->quote($insertArray["xsdsel_type"]) . ",";
		if (is_numeric($insertArray["xsdsel_attribute_loop_xdis_id"])) {
			$stmt .= $db->quote($insertArray["xsdsel_attribute_loop_xdis_id"], 'INTEGER').",";
		}
		if (is_numeric($insertArray["xsdsel_attribute_loop_xsdmf_id"])) {
			$stmt .= $db->quote($insertArray["xsdsel_attribute_loop_xsdmf_id"], 'INTEGER').",";
		}
		if (is_numeric($insertArray["xsdsel_indicator_xdis_id"])) {
			$stmt .= $db->quote($insertArray["xsdsel_indicator_xdis_id"], 'INTEGER').",";
		}
		if (is_numeric($insertArray["xsdsel_indicator_xsdmf_id"])) {
			$stmt .= $db->quote($insertArray["xsdsel_indicator_xsdmf_id"], 'INTEGER').",";
		}
		$stmt .= $db->quote($insertArray["xsdsel_indicator_value"]) . ", ".
		$db->quote($insertArray["xsdsel_order"], 'INTEGER') . ")";

		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return $db->lastInsertId(APP_TABLE_PREFIX . "xsd_loop_subelement", "xsdsel_id");
	}

	/**
	 * Method used to update a sublooping element in the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($xsdsel_id='', $params=array())
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (empty($params)) {
			$params = &$_POST;
		}
		if (empty($xsdsel_id)) {
			$xsdsel_id = $db->quote($params["xsdsel_id_edit"], 'INTEGER');
		}

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement
                 SET 
                    xsdsel_xsdmf_id = " . $db->quote($params["xsdsel_xsdmf_id"]) . ",
                    xsdsel_title = " . $db->quote($params["xsdsel_title"]) . ",
                    xsdsel_order = " . $db->quote($params["xsdsel_order"]) . ",";
		if (is_numeric($params["xsdsel_attribute_loop_xdis_id"])) {
			$stmt .= "   xsdsel_attribute_loop_xdis_id = " . $db->quote($params["xsdsel_attribute_loop_xdis_id"]) . ",";
		}
		if (is_numeric($params["xsdsel_attribute_loop_xsdmf_id"])) {
			$stmt .= "   xsdsel_attribute_loop_xsdmf_id = " . $db->quote($params["xsdsel_attribute_loop_xsdmf_id"]) . ",";
		}
		if (is_numeric($params["xsdsel_indicator_xdis_id"])) {
			$stmt .= "   xsdsel_indicator_xdis_id = " . $db->quote($params["xsdsel_indicator_xdis_id"]) . ",";
		}
		if (is_numeric($params["xsdsel_indicator_xsdmf_id"])) {
			$stmt .= "   xsdsel_indicator_xsdmf_id = " . $db->quote($params["xsdsel_indicator_xsdmf_id"]) . ",";
		}
		$stmt .= "
                    xsdsel_indicator_value = " . $db->quote($params["xsdsel_indicator_value"]) . ",
                    xsdsel_type = " . $db->quote($params["xsdsel_type"]) . "
                 WHERE xsdsel_id = ".$db->quote($xsdsel_id, 'INTEGER');
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
	 * Method used to update a sublooping element attribute loop candidate.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function updateAttributeLoopCandidate($xsdsel_id, $attribute_loop_candidate, $attribute_loop_candidate_xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement
                 SET 
                    xsdsel_attribute_loop_xsdmf_id = ".$db->quote($attribute_loop_candidate, 'INTEGER').",
                    xsdsel_attribute_loop_xdis_id = ".$db->quote($attribute_loop_candidate_xdis_id, 'INTEGER')."                    
                 WHERE xsdsel_id = " . $db->quote($xsdsel_id, 'INTEGER');
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}

	/**
	 * Method used to update a sublooping element indicator.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function updateIndicator($xsdsel_id, $indicator_xsdmf_id, $indicator_xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement
                 SET 
                    xsdsel_indicator_xsdmf_id = ".$db->quote($indicator_xsdmf_id, 'INTEGER').",
                    xsdsel_indicator_xdis_id = ".$db->quote($indicator_xdis_id, 'INTEGER')."                    
                 WHERE xsdsel_id = " . $db->quote($xsdsel_id, 'INTEGER');
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
	/**
	 * Method used to get the details of a specific sublooping element.
	 *
	 * @access  public
	 * @param   integer $fld_id The sublooping element ID
	 * @return  array The sublooping element details
	 */
	function getDetails($xsdsel_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement
                 WHERE
                    xsdsel_id=".$db->quote($xsdsel_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Method used to check if a sublooping element has any related xsd matching fields of a given html input type
	 * This is mainly used to check if a datastream is a file upload or a link so the label and ID and possibly mimetype can be read from the uploaded file
	 *
	 * @access  public
	 * @param   integer $xsdsel_id
	 * @param   string $input_type
	 * @return  boolean Whether any xsdmf's in the xsdsel were of the given type
	 */
	function getXSDMFInputType($xsdsel_id, $input_type, $exclude_attrib_loops = false)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_loop_subelement,
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_html_input = ".$db->quote($input_type)."  AND xsdmf_xsdsel_id = xsdsel_id AND xsdsel_id=".$db->quote($xsdsel_id, 'INTEGER');
		if ($exclude_attrib_loops == true) {
			$stmt .= " AND xsdmf_id <> xsdsel_attribute_loop_xsdmf_id";
		}

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	function importSubelements($xmatch, $xsdmf_id, &$maps)
	{
		$xpath = new DOMXPath($xmatch->ownerDocument);
		$xsubs = $xpath->query('loop_subelement', $xmatch);
		foreach ($xsubs as $xsub) {
			$params = array(
                'xsdsel_xsdmf_id' => $xsdmf_id,
                'xsdsel_title' => $xsub->getAttribute('xsdsel_title'),
                'xsdsel_type' => $xsub->getAttribute('xsdsel_type'),
                'xsdsel_order' => $xsub->getAttribute('xsdsel_order'),
                'xsdsel_attribute_loop_xdis_id' => $xsub->getAttribute('xsdsel_attribute_loop_xdis_id'),
                'xsdsel_attribute_loop_xsdmf_id' => $xsub->getAttribute('xsdsel_attribute_loop_xsdmf_id'),
                'xsdsel_indicator_xdis_id' => $xsub->getAttribute('xsdsel_indicator_xdis_id'),
                'xsdsel_indicator_xsdmf_id' => $xsub->getAttribute('xsdsel_indicator_xsdmf_id'),
                'xsdsel_indicator_value' => $xsub->getAttribute('xsdsel_indicator_value'),
			);
			$xsdsel_id = XSD_Loop_Subelement::insertFromArray($xsdmf_id, $params);
			$maps['xsdsel_map'][$xsub->getAttribute('xsdsel_id')] = $xsdsel_id;
		}
	}

	function remapImport(&$maps)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (empty($maps['xsdsel_map'])) {
			return;
		}
		foreach ($maps['xsdsel_map'] as $xsdsel_id) {
			$stmt = "SELECT * FROM ". APP_SQL_DBNAME . "." . APP_TABLE_PREFIX ."xsd_loop_subelement " .
                    "WHERE xsdsel_id=".$db->quote($xsdsel_id);
			try {
				$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return;
			}

			Misc::arraySearchReplace($res,
									array('xsdsel_attribute_loop_xdis_id','xsdsel_indicator_xdis_id'),
									$maps['xdis_map']);
			Misc::arraySearchReplace($res,
									array('xsdsel_attribute_loop_xsdmf_id','xsdsel_indicator_xsdmf_id'),
									$maps['xsdmf_map']);
			XSD_Loop_Subelement::update($xsdsel_id, $res);

		}
	}
}
