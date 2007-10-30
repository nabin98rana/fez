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
 * Class to handle the business logic related to the XSD to HTML Matching in the system
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */
include_once (APP_INC_PATH . "class.error_handler.php");
include_once (APP_INC_PATH . "class.misc.php");
include_once (APP_INC_PATH . "class.xsd_relationship.php");
include_once (APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once (APP_INC_PATH . "class.xsd_html_match_generator.php");
include_once (APP_INC_PATH . "class.record.php");
include_once (APP_INC_PATH . "class.user.php");
include_once (APP_INC_PATH . "class.auth.php");

class XSD_HTML_Match {
	public static $xsdmf_columns = array (
		'xsdmf_id',
		'xsdmf_xdis_id',
		'xsdmf_xsdsel_id',
		'xsdmf_element',
		'xsdmf_title',
		'xsdmf_description',
		'xsdmf_long_description',
		'xsdmf_html_input',
		'xsdmf_multiple',
		'xsdmf_multiple_limit',
		'xsdmf_valueintag',
		'xsdmf_enabled',
		'xsdmf_order',
		'xsdmf_validation_type',
		'xsdmf_required',
		'xsdmf_static_text',
		'xsdmf_dynamic_text',
		'xsdmf_xdis_id_ref',
		'xsdmf_id_ref',
		'xsdmf_id_ref_save_type',
		'xsdmf_is_key',
		'xsdmf_key_match',
		'xsdmf_show_in_view',
		'xsdmf_smarty_variable',
		'xsdmf_fez_variable',
		'xsdmf_enforced_prefix',
		'xsdmf_value_prefix',
		'xsdmf_selected_option',
		'xsdmf_dynamic_selected_option',
		'xsdmf_image_location',
		'xsdmf_parent_key_match',
		'xsdmf_data_type',
		'xsdmf_indexed',
		'xsdmf_sek_id',
		'xsdmf_cvo_id',
		'xsdmf_cvo_min_level',
		'xsdmf_cvo_save_type',
		'xsdmf_original_xsdmf_id',
		'xsdmf_attached_xsdmf_id',
		'xsdmf_cso_value',
		'xsdmf_citation_browse',
		'xsdmf_citation',
		'xsdmf_citation_bold',
		'xsdmf_citation_italics',
		'xsdmf_citation_order',
		'xsdmf_citation_brackets',
		'xsdmf_citation_prefix',
		'xsdmf_citation_suffix',
		'xsdmf_use_parent_option_list',
		'xsdmf_parent_option_xdis_id',
		'xsdmf_parent_option_child_xsdmf_id',
		'xsdmf_org_level',
		'xsdmf_use_org_to_fill',
		'xsdmf_org_fill_xdis_id',
		'xsdmf_org_fill_xsdmf_id',
		'xsdmf_asuggest_xdis_id',
		'xsdmf_asuggest_xsdmf_id',
		'xsdmf_date_type',
		'xsdmf_meta_header',
		'xsdmf_meta_header_name'
	);

	/**
	 * Method used to remove a group of matching field options.
	 *
	 * @access  public
	 * @param   array $fld_id The list of matching field IDs
	 * @param   array $mfo_id The list of matching field option IDs
	 * @return  boolean
	 */
	function removeOptions($fld_id, $mfo_id) {
		if (!is_array($fld_id)) {
			$fld_id = array (
				$fld_id
			);
		}
		if (!is_array($mfo_id)) {
			$mfo_id = array (
				$mfo_id
			);
		}
		$stmt = "DELETE FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 WHERE
		                    mfo_id IN (" . implode(",", $mfo_id) . ")";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return false;
		} else {
			// also remove any custom field option that is currently assigned to an issue
			// XXX: review this
			$stmt = "DELETE FROM
			                        " . APP_TABLE_PREFIX . "issue_custom_field
			                     WHERE
			                        icf_fld_id IN (" . implode(", ", $fld_id) . ") AND
			                        icf_value IN (" . implode(", ", $mfo_id) . ")";
			$GLOBALS["db_api"]->dbh->query($stmt);
			return true;
		}
	}

	/**
	 * Method used to add possible options into a given matching field.
	 *
	 * @access  public
	 * @param   integer $fld_id The matching field ID
	 * @param   array $options The list of options that need to be added
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function addOptions($fld_id, $options) {
		if (!is_array($options)) {
			$options = array (
				$options
			);
		}
		foreach ($options as $option) {
			$stmt = "INSERT INTO
			                        " . APP_TABLE_PREFIX . "xsd_display_mf_option
			                     (
			                        mfo_fld_id,
			                        mfo_value
			                     ) VALUES (
			                        ".$fld_id.",
			                        '" . Misc::escapeString($option) . "'
			                     )";
			$res = $GLOBALS["db_api"]->dbh->query($stmt);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array (
				$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				return -1;
			}
		}
		return 1;
	}

	/**
	 * Method used to update an existing matching field option value.
	 *
	 * @access  public
	 * @param   integer $mfo_id The matching field option ID
	 * @param   string $mfo_value The matching field option value
	 * @return  boolean
	 */
	function updateOption($mfo_id, $mfo_value) {
		$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 SET
		                    mfo_value='" . Misc::escapeString($mfo_value) . "'
		                 WHERE
		                    mfo_id=" . $mfo_id;
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Method used to get the list of matching fields associated with
	 * a given display id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The list of matching fields fields
	 */
	function getListByDisplaySpecify($xdis_id, $specify_titles = array ('FezACML')) {
		$stmt = "SELECT
		                    xsdmf_id,
		                    xsdmf_element,
		                    xsdmf_title,
		                    xsdmf_description,
		                    xsdmf_long_description,
		                    xsdmf_html_input,
		                    xsdmf_order,
		                    xsdmf_validation_type,
		                    xsdmf_enabled,
		                    xsdmf_indexed,
		                    xsdmf_show_in_view,
		                    xsdmf_multiple,
		                    xsdmf_multiple_limit,
							xsdmf_static_text,
							xsdmf_dynamic_text,
							xsdmf_smarty_variable,
							xsdmf_dynamic_selected_option,
							xsdmf_selected_option,
							xsdmf_fez_variable,
							xsdmf_enforced_prefix,
							xsdmf_data_type,
							xsdmf_value_prefix,
		                    xsdmf_xdis_id_ref,
		                    xsdmf_id_ref,
		                    xsdmf_xdis_id_ref,
		                    xsdmf_id_ref_save_type,
							xsdmf_attached_xsdmf_id,
							xsdmf_cvo_id,
							xsdmf_cvo_min_level,
							xsdmf_cvo_save_type,
							xsdsel_order
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdsel_id = xsdmf_xsdsel_id)
		                 WHERE
		                   xsdmf_xdis_id=".$xdis_id." AND xsdmf_enabled=1";
		// @@@ CK - Added order statement to custom fields displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order, xsdmf_order ASC";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			// Add any reference displays
			$specify_ids = XSD_Display::getIDs($specify_titles);
			foreach ($res as $rkey => $record) {
				if ($record['xsdmf_html_input'] == 'xsd_ref') {
					$ref = XSD_Relationship::getListByXSDMF($record['xsdmf_id']);
					if (is_array($ref)) {
						foreach ($ref as $reference) {
							if (is_array($specify_ids) && (count($specify_ids) > 0)) {
								if (in_array($reference['xsdrel_xdis_id'], $specify_ids)) {
									$ref_display = XSD_HTML_Match::getListByDisplay($reference['xsdrel_xdis_id']);
									$res = array_merge($ref_display, $res);
								}
							} else {
								$ref_display = XSD_HTML_Match::getListByDisplay($reference['xsdrel_xdis_id']);
								$res = array_merge($ref_display, $res);
							}
						}
					}
				}
				//@@@ CK - 29/4/2005 - Added multiple_array as an element so smarty could look the html input elements
				if (($record['xsdmf_multiple'] == 1) && (is_numeric($record['xsdmf_multiple_limit']))) {
					$res[$rkey]['multiple_array'] = array ();
					for ($x = 1; $x < ($record['xsdmf_multiple_limit'] + 1); $x++) {
						array_push($res[$rkey]['multiple_array'], $x);
					}
				}
			}
			if (count($res) == 0) {
				return "";
			} else {
				//				echo "About to do ".strval(count($res) * 2)." queries on line ".__LINE__."\n";
				for ($i = 0; $i < count($res); $i++) {
					$res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["xsdmf_id"]);
					$res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);
				}
				return $res;
			}
		}
	}

	/**
	 * Method used to get an associative array of the xsdmf id and xsdmf element
	 *
	 * @access  public
	 * @return  array The list of xsd html matches
	 */
	function getAssocList($xdis_id) {
		$stmt = "SELECT
		                    distinct xsdmf_id, IFNULL(CONCAT('(', xsdmf_id, ') (', xsdsel_title, ') ', xsdmf_element), CONCAT('(', xsdmf_id, ') ', xsdmf_element)) as xsdmf_presentation
						 FROM 
							" . APP_TABLE_PREFIX . "xsd_display_matchfields as m1 left join
							" . APP_TABLE_PREFIX . "xsd_loop_subelement as s1 on s1.xsdsel_id = m1.xsdmf_xsdsel_id
			 			 WHERE xsdmf_xdis_id = " . $xdis_id . " 
						 ORDER BY xsdsel_title";

		$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return $res;
		}
	}

	/**
	  * Method used to get the list of matching fields associated with
	  * a given display id.
	  *
	  * @access  public
	  * @param   integer $xdis_id The XSD Display ID
	  * @param   array optional $exclude_list The list of datastream IDs to exclude, takes preference over the specify list
	  * @param   array optional $specify_list The list of datastream IDs to specify 
	  * @return  array The list of matching fields fields
	  */
	function getBasicListByDisplay($xdis_id, $exclude_list = array (), $specify_list = array()) {
		$exclude_str = implode("', '", $exclude_list);
		$specify_str = implode("', '", $specify_list);

		if (in_array("FezACML for Datastreams", $specify_list)) {
			$FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
			$specify_str = "FezACML";
			$xsdrelall = array();
			array_push($xsdrelall, $FezACML_xdis_id);						
		} else {
			$stmt = "SELECT distinct r2.xsdrel_xdis_id FROM " . APP_TABLE_PREFIX . "xsd_relationship r2 right join
								(SELECT m3.xsdmf_id FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields as m3 WHERE m3.xsdmf_xdis_id=" . $xdis_id . ")
								as rels on (r2.xsdrel_xsdmf_id = (rels.xsdmf_id))";
			$xsdrelall = $GLOBALS["db_api"]->dbh->getCol($stmt);

			if (PEAR::isError($xsdrelall)) {
				Error_Handler::logError(array (
				$xsdrelall->getMessage(), $xsdrelall->getDebugInfo()), __FILE__, __LINE__);
				$xsdrelall = array();
			}
			array_push($xsdrelall, $xdis_id);			
		}


		$stmt = "SELECT
		                    distinct xsdmf_id,
		                    xsdmf_element,
		                    xsdmf_title,
		                    xsdmf_description,
		                    xsdmf_long_description,					
		                    xsdmf_html_input,
		                    xsdmf_order,
                            xsdmf_validation_type,
		                    xsdmf_enabled,
		                    xsdmf_show_in_view,
		                    xsdmf_multiple,
		                    xsdmf_multiple_limit,
							xsdmf_static_text,
							xsdmf_dynamic_text,
							xsdmf_smarty_variable,
							xsdmf_dynamic_selected_option,
							xsdmf_selected_option,
							xsdmf_fez_variable,
							xsdmf_enforced_prefix,
							xsdmf_is_key,
							xsdmf_required,					
							xsdmf_data_type,
							xsdmf_key_match,
							xsdmf_parent_key_match,
							xsdmf_value_prefix,
							xsdmf_static_text,
							xsdmf_xsdsel_id,
							xsdmf_image_location,
		                    xsdmf_xdis_id_ref,
		                    xsdmf_id_ref,
		                    xsdmf_id_ref_save_type,
							xsdsel_order,
							xsdmf_attached_xsdmf_id,					
							xsdmf_cvo_id,
							xsdmf_cvo_min_level,
							xsdmf_cvo_save_type,
							xsdmf_cso_value,
							xsdmf_citation_browse,
							xsdmf_citation,
							xsdmf_citation_bold,					
							xsdmf_citation_italics,
							xsdmf_citation_brackets,
							xsdmf_citation_order,
							xsdmf_citation_prefix,
							xsdmf_citation_suffix,
							xsdmf_use_parent_option_list,
							xsdmf_parent_option_xdis_id,
							xsdmf_parent_option_child_xsdmf_id,
							xsdmf_asuggest_xdis_id,
							xsdmf_asuggest_xsdmf_id,
							xsdmf_org_level,
							xsdmf_use_org_to_fill,
							xsdmf_org_fill_xdis_id,
							xsdmf_org_fill_xsdmf_id,
							xsdmf_date_type,
							xsdmf_meta_header,
							xsdmf_meta_header_name,
							sek_title
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
							left join " . APP_TABLE_PREFIX . "search_key as sk1 on sk1.sek_id = m1.xsdmf_sek_id ";

		if ($specify_str != "") {
			$stmt .= "
								inner join
								(SELECT d1.xdis_id FROM " . APP_TABLE_PREFIX . "xsd_display d1, " . APP_TABLE_PREFIX . "xsd as xsd WHERE xsd.xsd_id=d1.xdis_xsd_id AND xsd.xsd_title in ('" . $specify_str . "')) as displays on (m1.xsdmf_xdis_id in (displays.xdis_id) AND displays.xdis_id in (" . Misc::sql_array_to_string_simple($xsdrelall) . "))";
		}
		elseif ($exclude_str != "") {
			$stmt .= "
								inner join
								(SELECT d1.xdis_id FROM " . APP_TABLE_PREFIX . "xsd_display d1, " . APP_TABLE_PREFIX . "xsd as xsd WHERE xsd.xsd_id=d1.xdis_xsd_id AND xsd.xsd_title not in ('" . $exclude_str . "')) as displays on (m1.xsdmf_xdis_id in (displays.xdis_id) AND displays.xdis_id in (" . Misc::sql_array_to_string_simple($xsdrelall) . "))";
		}
		//				if ($specify_str == "") { 
		//				}
		$stmt .= "
							left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement as s1 on (s1.xsdsel_id = m1.xsdmf_xsdsel_id)
						WHERE m1.xsdmf_xdis_id in (" . Misc::sql_array_to_string_simple($xsdrelall) . ")";
		// @@@ CK - Added order statement to custom fields displayed in a desired order

		$stmt .= " ORDER BY xsdmf_order, xsdsel_order ASC";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);

		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			// Add any reference displays
			foreach ($res as $rkey => $record) {
				if (($record['xsdmf_multiple'] == 1) && (is_numeric($record['xsdmf_multiple_limit']))) {
					$res[$rkey]['multiple_array'] = array();
					for ($x = 1; $x < ($record['xsdmf_multiple_limit'] + 1); $x++) {
						array_push($res[$rkey]['multiple_array'], $x);
					}
				}
				//				if ($record['xsdmf_attached_xsdmf_id'] != "")
			}

			return $res;
		}
	}

	/**
	 * Method used to get the list of matching fields that are not sublooping elements associated with
	 * a given display id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @return  array The list of matching fields fields
	 */
	function getNonSELChildListByDisplay($xdis_id) {
		$stmt = "SELECT
							*
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
						 WHERE ISNULL(m1.xsdmf_xsdsel_id) AND m1.xsdmf_xdis_id = " . $xdis_id;
		$stmt .= " ORDER BY xsdmf_id ASC";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			return $res;
		}

	}

	/**
	 * Method used to get the list of matching fields that are sublooping elements associated with
	 * a given display id and sublooping element id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @param   integer $xsdsel_id The sublooping element ID	 
	 * @return  array The list of matching fields fields
	 */
	function getSELChildListByDisplay($xdis_id, $xsdsel_id) {
		$stmt = "SELECT
							*
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
						 WHERE (m1.xsdmf_xsdsel_id = ".$xsdsel_id.") AND m1.xsdmf_xdis_id = " . $xdis_id;
		$stmt .= " ORDER BY xsdmf_id ASC";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			return $res;
		}

	}

	/**
	 * Method used to get the list of matching fields.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @param   array optional $exclude_list The list of datastream IDs to exclude, takes preference over the specify list
	 * @param   array optional $specify_list The list of datastream IDs to specify 
	 * @return  array The list of matching fields fields
	 */
	function getListByDisplay($xdis_id, $exclude_list = array (), $specify_list = array ()) {

		$res = XSD_HTML_Match::getBasicListByDisplay($xdis_id, $exclude_list, $specify_list);

		if (count($res) == 0) {
			return array ();
		} else {
			/** 
			 * ORIGINAL FEZ CODE 
			 * KJ: optimized for performance - a single MySQL query was used for each matchfield
			 * (but most matchfields don't have any options at all). This was slowing down record
			 * display in view.php.
			 */				
			/**		
			for ($i = 0; $i < count($res); $i++) {				
				$res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["xsdmf_id"]);				
				$res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);
			}
			*/

			/**
			 * OPTIMIZED CODE
			 * Use a single query for all matchfields and requery for values only if there are any options.
			 * mfo_value is already selected for a possible later performance optimization
			 */
			// one query to get all matchfield options
			$ids = array();
			for ($i = 0; $i < count($res); $i++) {
				array_push($ids, $res[$i]["xsdmf_id"]);												
			}
			$fld_id_str = implode(',', $ids);
			$stmt = "SELECT mfo_fld_id, mfo_id, mfo_value ".
					"FROM ".APP_TABLE_PREFIX."xsd_display_mf_option ".
					"WHERE mfo_fld_id IN (".$fld_id_str.") ORDER BY	mfo_fld_id, mfo_value ASC";				
									
			// last parameter of getAssoc $group=true: pushes values for the same key (mfo_fld_id) 
			// into an array
			$mfoResult = $GLOBALS["db_api"]->dbh->getAssoc($stmt, false, array(), DB_FETCHMODE_DEFAULT, true);
			if (PEAR::isError($mfoResult)) {
				Error_Handler::logError(array ($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				return array ();
			}
									
			// iterate over match field list: only get value(s) if there are any options at all
			
			for ($i = 0; $i < count($res); $i++) {	
				$res[$i]["field_options"] = array();
				$res[$i]["field_options_value_only"] = array();
				
				$mfoEntries = $mfoResult[$res[$i]['xsdmf_id']];		
				
				// check if this field has any options
				if (count($mfoEntries) > 0) {
					
					for ($n=0; $n<count($mfoEntries); $n++) {
						$res[$i]["field_options"][$mfoEntries[$n][0]] = $mfoEntries[$n][1];
					}
					
					// this could be further optimized, but is just called in very few cases
					$res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);
					
					//print_r($res[$i]["field_options"]);
					//print_r($res[$i]["field_options_value_only"]);
				} 								
			}
								
			return $res;
		}
	}

	/**
	 * Method used to get the matching field option value.
	 *
	 * @access  public
	 * @param   integer $fld_id The matching field ID
	 * @param   integer $value The matching field option ID
	 * @return  string The matching field option value
	 */
	function getOptionValue($fld_id, $value) {
        //Logger::debug("-----------getOptionValue");		
		if (empty ($value)) {
			return "";
		}
		$stmt = "SELECT
		                    mfo_value
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 WHERE
		                    mfo_fld_id=".$fld_id." AND
		                    mfo_id=".$value;
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if ($res == NULL) {
				return "";
			} else {
				return $res;
			}
		}
	}

	/**
	 * Method used to get the matching field option value by ID.
	 *
	 * @access  public
	 * @param   integer $mfo_id The custom field ID
	 * @return  string The custom field option value
	 */
	function getOptionValueByMFO_ID($mfo_id) {
        //Logger::debug("-----------getOptionValueByMFO_ID");			
		if (!is_numeric($mfo_id)) {
			return "";
		}
		$stmt = "SELECT
		                    mfo_value
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 WHERE
		                    mfo_id=".$mfo_id;
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if ($res == NULL) {
				return "";
			} else {
				return $res;
			}
		}
	}

	/**
	 * Method used to remove a XSD matching field.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @param   string  $xml_element The XML element
	 * @return  boolean
	 */
	function remove($xdis_id, $xml_element) {
		$stmt = "DELETE FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_xdis_id = ".$xdis_id." AND xsdmf_element='" . $xml_element . "'";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1;
		} else {
			return 1;
		}
	}

	/**
	 * Method used to remove a XSD matching fields by their XSDMF IDs.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function removeByXSDMF_IDs($xsdmf_ids = array ()) {
		if (empty ($xsdmf_ids)) {
			global $HTTP_POST_VARS;
			$xsdmf_ids = & $HTTP_POST_VARS['items'];
		}
		$items = Misc::arrayToSQL($xsdmf_ids);
		if (@ strlen($items) < 1) {
			return false;
		}
		foreach ($xsdmf_ids as $xsdmf_id) {
			$att_list = XSD_HTML_Match::getChildren($xsdmf_id);
			if (!empty ($att_list)) {
				$att_ids = Misc::arrayToSQL(array_keys(Misc::keyArray($att_list, 'att_id')));
				$stmt = "delete from " . APP_TABLE_PREFIX . "xsd_display_attach " .
				"where att_id in (".$att_ids.")";
				$res = $GLOBALS["db_api"]->dbh->query($stmt);
				if (PEAR::isError($res)) {
					Error_Handler::logError(array (
					$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				}
			}
			$mfo_list = XSD_HTML_Match::getOptions($xsdmf_id);
			if (is_array($mfo_list) && !empty($mfo_list)) {
				$mfo_ids = Misc::arrayToSQL(array_keys(Misc::keyArray($mfo_list, 'mfo_id')));
				$stmt = "delete from " . APP_TABLE_PREFIX . "xsd_display_mf_option " .
				"where mfo_id in (".$mfo_ids.")";
				$res = $GLOBALS["db_api"]->dbh->query($stmt);
				if (PEAR::isError($res)) {
					Error_Handler::logError(array (
					$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				}
			}
			$subs = XSD_Loop_Subelement::getSimpleListByXSDMF($xsdmf_id);
			if (!empty ($subs)) {
				$xsdsel_ids = Misc::arrayToSQL(array_keys(Misc::keyArray($subs, 'xsdsel_id')));
				$stmt = "delete from " . APP_TABLE_PREFIX . "xsd_loop_subelement " .
				"where xsdsel_id in (".$xsdsel_ids.")";
				$res = $GLOBALS["db_api"]->dbh->query($stmt);
				if (PEAR::isError($res)) {
					Error_Handler::logError(array (
					$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				}
			}
			$rels = XSD_Relationship::getSimpleListByXSDMF($xsdmf_id);
			if (!empty ($rels)) {
				$xsdrel_ids = Misc::arrayToSQL(array_keys(Misc::keyArray($rels, 'xsdrel_id')));
				$stmt = "delete from " . APP_TABLE_PREFIX . "xsd_relationship " .
				"where xsdrel_id in (".$xsdrel_ids.")";
				$res = $GLOBALS["db_api"]->dbh->query($stmt);
				if (PEAR::isError($res)) {
					Error_Handler::logError(array (
					$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				}
			}
		}

		$stmt = "DELETE FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_id in (".$items.")";

		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1;
		} else {
			return 1;
		}
	}

	function removeByXDIS_ID($xdis_id) {
		$list = XSD_HTML_Match::getList($xdis_id);
		$xsdmf_ids = array_keys(Misc::keyArray($list, 'xsdmf_id'));
		return XSD_HTML_Match::removeByXSDMF_IDs($xsdmf_ids);
	}

	/**
	 * Method used to add a new XSD matching field to the system, from form post variables.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @param   string  $xml_element The XML element
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert($xdis_id, $xml_element) {
		global $HTTP_POST_VARS;
		if (@ $HTTP_POST_VARS["enabled"]) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}
		if (@ $HTTP_POST_VARS["multiple"]) {
			$multiple = 1;
		} else {
			$multiple = 0;
		}
		if (@ $HTTP_POST_VARS["indexed"]) {
			$indexed = 1;
		} else {
			$indexed = 0;
		}
		if (@ $HTTP_POST_VARS["required"]) {
			$required = 1;
		} else {
			$required = 0;
		}
		if (@ $HTTP_POST_VARS["show_in_view"]) {
			$show_in_view = 1;
		} else {
			$show_in_view = 0;
		}
		if (@ $HTTP_POST_VARS["valueintag"]) {
			$valueintag = 1;
		} else {
			$valueintag = 0;
		}
		if (@ $HTTP_POST_VARS["is_key"]) {
			$is_key = 1;
		} else {
			$is_key = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_citation"]) {
			$xsdmf_citation = 1;
		} else {
			$xsdmf_citation = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_citation_browse"]) {
			$xsdmf_citation_browse = 1;
		} else {
			$xsdmf_citation_browse = 0;
		}

		if (@ $HTTP_POST_VARS["xsdmf_citation_bold"]) {
			$xsdmf_citation_bold = 1;
		} else {
			$xsdmf_citation_bold = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_citation_italics"]) {
			$xsdmf_citation_italics = 1;
		} else {
			$xsdmf_citation_italics = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_citation_brackets"]) {
			$xsdmf_citation_brackets = 1;
		} else {
			$xsdmf_citation_brackets = 0;
		}

		if (@ $HTTP_POST_VARS["xsdmf_use_parent_option_list"]) {
			$xsdmf_use_parent_option_list = 1;
		} else {
			$xsdmf_use_parent_option_list = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_use_org_to_fill"]) {
			$xsdmf_use_org_to_fill = 1;
		} else {
			$xsdmf_use_org_to_fill = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_meta_header"]) {
			$xsdmf_meta_header = 1;
		} else {
			$xsdmf_meta_header = 0;
		}

		$stmt = "INSERT INTO
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 (
		                    xsdmf_xdis_id,
		                    xsdmf_element,
		                    xsdmf_title,
		                    xsdmf_description,
		                    xsdmf_long_description,					
		                    xsdmf_html_input,
		                    xsdmf_order,
		                    xsdmf_validation_type,
		                    xsdmf_enabled,
		                    xsdmf_indexed,
		                    xsdmf_required,
		                    xsdmf_multiple,
							xsdmf_meta_header_name,
							xsdmf_meta_header,
							xsdmf_citation_browse,
							xsdmf_citation,
							xsdmf_citation_bold,
							xsdmf_citation_italics,
							xsdmf_citation_brackets,";
		if (is_numeric($HTTP_POST_VARS["xsdmf_citation_order"])) {
			$stmt .= "xsdmf_citation_order,";
		}
		if ($HTTP_POST_VARS["xsdmf_citation_prefix"] != "") {
			$stmt .= "xsdmf_citation_prefix,";
		}
		if ($HTTP_POST_VARS["xsdmf_citation_suffix"] != "") {
			$stmt .= "xsdmf_citation_suffix,";
		}

		if ($HTTP_POST_VARS["multiple_limit"] != "") {
			$stmt .= "xsdmf_multiple_limit,";
		}
		if ($HTTP_POST_VARS["xsdmf_sek_id"] != "") {
			$stmt .= "xsdmf_sek_id,";
		}
		if ($HTTP_POST_VARS["xsdmf_org_level"] != "") {
			$stmt .= "xsdmf_org_level,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_org_fill_xdis_id"])) {
			$stmt .= "xsdmf_org_fill_xdis_id,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_org_fill_xsdmf_id"])) {
			$stmt .= "xsdmf_org_fill_xsdmf_id,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_parent_option_xdis_id"])) {
			$stmt .= "xsdmf_parent_option_xdis_id,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_parent_option_child_xsdmf_id"])) {
			$stmt .= "xsdmf_parent_option_child_xsdmf_id,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_asuggest_xdis_id"])) {
			$stmt .= "xsdmf_asuggest_xdis_id,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_asuggest_xsdmf_id"])) {
			$stmt .= "xsdmf_asuggest_xsdmf_id,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_cvo_min_level"])) {
			$stmt .= "xsdmf_cvo_min_level,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_cvo_save_type"])) {
			$stmt .= "xsdmf_cvo_save_type,";
		}

		$stmt .= "
							xsdmf_use_org_to_fill,
							xsdmf_use_parent_option_list,
		                    xsdmf_valueintag,
		                    xsdmf_is_key,
		                    xsdmf_data_type,
		                    xsdmf_parent_key_match,
		                    xsdmf_key_match,";
		if ($HTTP_POST_VARS["xsdmf_xdis_id_ref"] != "") {
			$stmt .= "xsdmf_xdis_id_ref,";
		}
		if ($HTTP_POST_VARS["xsdmf_id_ref"] != "") {
			$stmt .= "xsdmf_id_ref,";
		}
		if ($HTTP_POST_VARS["xsdmf_id_ref_save_type"] != "") {
			$stmt .= "xsdmf_id_ref_save_type,";
		}

		if ($HTTP_POST_VARS["smarty_variable"] != "") {
			$stmt .= "xsdmf_smarty_variable,";
		}
		if ($HTTP_POST_VARS["fez_variable"] != "") {
			$stmt .= "xsdmf_fez_variable,";
		}
		if ($HTTP_POST_VARS["dynamic_selected_option"] != "") {
			$stmt .= "xsdmf_dynamic_selected_option,";
		}
		if ($HTTP_POST_VARS["selected_option"] != "") {
			$stmt .= "xsdmf_selected_option,";
		}

		$stmt .= "xsdmf_cso_value,";

		$stmt .= "xsdmf_show_in_view,
							xsdmf_enforced_prefix,
							xsdmf_value_prefix,
							xsdmf_image_location,
							xsdmf_static_text,
							xsdmf_dynamic_text,
							xsdmf_date_type,
							xsdmf_cvo_id";
		if (is_numeric($HTTP_POST_VARS["attached_xsdmf_id"])) {
			$stmt .= ", xsdmf_attached_xsdmf_id";
		}
		if (is_numeric($HTTP_POST_VARS["xsdsel_id"])) {
			$stmt .= ", xsdmf_xsdsel_id";
		}
		$stmt .= "
		                 ) VALUES (
		                    ".$xdis_id.",
		                    '".$xml_element."',
		                    '" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["description"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["long_description"]) . "',					
		                    '" . Misc::escapeString($HTTP_POST_VARS["field_type"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["order"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["validation_types"]) . "',
		                    " . $enabled . ",
		                    " . $indexed . ",
		                    " . $required . ",
		                    " . $multiple . ",
		                    '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_meta_header_name"]) . "',					
		                    " . $xsdmf_meta_header . ",
		                    " . $xsdmf_citation_browse . ",
		                    " . $xsdmf_citation . ",
		                    " . $xsdmf_citation_bold . ",
		                    " . $xsdmf_citation_italics . ",
		                    " . $xsdmf_citation_brackets . ", ";
		if (is_numeric($HTTP_POST_VARS["xsdmf_citation_order"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_citation_order"]) . ", ";
		}
		if ($HTTP_POST_VARS["xsdmf_citation_prefix"]) {
			$stmt .= "'" . Misc::escapeString($HTTP_POST_VARS["xsdmf_citation_prefix"]) . "', ";
		}
		if ($HTTP_POST_VARS["xsdmf_citation_suffix"]) {
			$stmt .= "'" . Misc::escapeString($HTTP_POST_VARS["xsdmf_citation_suffix"]) . "', ";
		}
		if ($HTTP_POST_VARS["multiple_limit"] != "") {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["multiple_limit"]) . ",";
		}
		if ($HTTP_POST_VARS["xsdmf_sek_id"] != "") {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_sek_id"]) . ",";
		}
		if ($HTTP_POST_VARS["xsdmf_org_level"] != "") {
			$stmt .= "'" . Misc::escapeString($HTTP_POST_VARS["xsdmf_org_level"]) . "',";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_org_fill_xdis_id"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_org_fill_xdis_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_org_fill_xsdmf_id"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_org_fill_xsdmf_id"]) . ",";
		}

		if (is_numeric($HTTP_POST_VARS["xsdmf_parent_option_xdis_id"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_parent_option_xdis_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_parent_option_child_xsdmf_id"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_parent_option_child_xsdmf_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_asuggest_xdis_id"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_asuggest_xdis_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_asuggest_xsdmf_id"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_asuggest_xsdmf_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_cvo_min_level"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_cvo_min_level"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_cvo_save_type"])) {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_cvo_save_type"]) . ",";
		}
		$stmt .= $xsdmf_use_org_to_fill . ",
							" . $xsdmf_use_parent_option_list . ",					
		                    " . $valueintag . ",
		                    " . $is_key . ",
		                    '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_data_type"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["parent_key_match"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["key_match"]) . "',";

		if ($HTTP_POST_VARS["xsdmf_xdis_id_ref"] != "") {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_xdis_id_ref"]) . ",";
		}
		if ($HTTP_POST_VARS["xsdmf_id_ref"] != "") {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_id_ref"]) . ",";
		}
		if ($HTTP_POST_VARS["xsdmf_id_ref_save_type"] != "") {
			$stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_id_ref_save_type"]) . ",";
		}

		if ($HTTP_POST_VARS["smarty_variable"] != "") {
			$stmt .= "'" . Misc::escapeString($HTTP_POST_VARS["smarty_variable"]) . "',";
		}
		if ($HTTP_POST_VARS["fez_variable"] != "") {
			$stmt .= "'" . Misc::escapeString($HTTP_POST_VARS["fez_variable"]) . "',";
		}
		if ($HTTP_POST_VARS["dynamic_selected_option"] != "") {
			$stmt .= "'" . Misc::escapeString($HTTP_POST_VARS["dynamic_selected_option"]) . "',";
		}
		if ($HTTP_POST_VARS["selected_option"] != "") {
			$stmt .= "'" . Misc::escapeString($HTTP_POST_VARS["selected_option"]) . "',";
		}

		$stmt .= "'" . Misc::escapeString($HTTP_POST_VARS["checkbox_selected_option"]) . "',";

		$stmt .= $show_in_view . ",
		                    '" . Misc::escapeString($HTTP_POST_VARS["enforced_prefix"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["value_prefix"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["image_location"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["static_text"]) . "',
		                    '" . Misc::escapeString($HTTP_POST_VARS["dynamic_text"]) . "',
		                    " . Misc::escapeString($HTTP_POST_VARS["xsdmf_date_type"]) . ",
		                    " . $HTTP_POST_VARS["xsdmf_cvo_id"];

		if (is_numeric($HTTP_POST_VARS["attached_xsdmf_id"])) {
			$stmt .= ", " . Misc::escapeString($HTTP_POST_VARS["attached_xsdmf_id"]);
		}

		if (is_numeric($HTTP_POST_VARS["xsdsel_id"])) {
			$stmt .= ", " . Misc::escapeString($HTTP_POST_VARS["xsdsel_id"]);
		}
		$stmt .= "
		                 )";

		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1;
		} else {
			//
			$new_id = $GLOBALS["db_api"]->get_last_insert_id();
			if (($HTTP_POST_VARS["field_type"] == 'combo') || ($HTTP_POST_VARS["field_type"] == 'multiple')) {
				foreach ($HTTP_POST_VARS["field_options"] as $option_value) {
					$params = XSD_HTML_Match::parseParameters($option_value);
					XSD_HTML_Match::addOptions($new_id, $params["value"]);
				}
			}
		}
	}

	/**
	 * Method used to add a new XSD matching field to the system, from an array.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @param   array  $insertArray The array of values to be entered.
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insertFromArray($xdis_id, $insertArray) {
		$insertArray['xsdmf_xdis_id'] = $xdis_id;
		$stmt = "INSERT INTO
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 ( ";
        foreach (XSD_HTML_Match::$xsdmf_columns as $col_name) {
        	if (!empty($insertArray[$col_name])) {
                $stmt .= "  $col_name,\n";
            }
        }                         
        $stmt = rtrim($stmt,", \n"); // get rid of trailing comma
        $stmt .= " ) VALUES ( ";
        foreach (XSD_HTML_Match::$xsdmf_columns as $col_name) {
            if (!empty($insertArray[$col_name])) {
                $value = Misc::escapeString($insertArray[$col_name]);
                $stmt .= "  '$value',\n";
            }
        }                         
        $stmt = rtrim($stmt,", \n"); // get rid of trailing comma
        $stmt .= " )";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1;
		} else {
			return $GLOBALS["db_api"]->get_last_insert_id();
		}
	}

	/**
	 * Method used to add a new XSD matching field to the system for a specific XSD sublooping element, from an array.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @param   string  $xsdsel_id The XSD sublooping element 
	 * @param   array  $insertArray The array of values to be entered.
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insertFromArraySEL($xdis_id, $xsdsel_id, $insertArray) {
		$stmt = "INSERT INTO
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 (
		                    xsdmf_xdis_id,
		                    xsdmf_element,
		                    xsdmf_title,
		                    xsdmf_description,
		                    xsdmf_html_input,
		                    xsdmf_validation_type,";

		if (!empty ($insertArray["xsdmf_order"])) {
			$stmt .= " xsdmf_order,";
		}

		if (!empty ($insertArray["xsdmf_enabled"])) {
			$stmt .= " xsdmf_enabled,";
		}
		if (!empty ($insertArray["xsdmf_indexed"])) {
			$stmt .= " xsdmf_indexed,";
		}
		if (!empty ($insertArray["xsdmf_required"])) {
			$stmt .= " xsdmf_required,";
		}
		if (!empty ($insertArray["xsdmf_multiple"])) {
			$stmt .= " xsdmf_multiple,";
		}

		if (!empty ($insertArray["xsdmf_multiple_limit"])) {
			$stmt .= "xsdmf_multiple_limit,";
		}
		if (!empty ($insertArray["xsdmf_sek_id"])) {
			$stmt .= "xsdmf_sek_id,";
		}
		if (!empty ($insertArray["xsdmf_valueintag"])) {
			$stmt .= "xsdmf_valueintag,";
		}
		if (!empty ($insertArray["xsdmf_is_key"])) {
			$stmt .= "xsdmf_is_key,";
		}
		if (!empty ($insertArray["xsdmf_meta_header_name"])) {
			$stmt .= "xsdmf_meta_header_name,";
		}
		if (!empty ($insertArray["xsdmf_meta_header"])) {
			$stmt .= "xsdmf_meta_header,";
		}
		if (!empty ($insertArray["xsdmf_citation_browse"])) {
			$stmt .= "xsdmf_citation_browse,";
		}
		if (!empty ($insertArray["xsdmf_citation"])) {
			$stmt .= "xsdmf_citation,";
		}
		if (!empty ($insertArray["xsdmf_citation_bold"])) {
			$stmt .= "xsdmf_citation_bold,";
		}
		if (!empty ($insertArray["xsdmf_citation_italics"])) {
			$stmt .= "xsdmf_citation_italics,";
		}
		if (!empty ($insertArray["xsdmf_citation_brackets"])) {
			$stmt .= "xsdmf_citation_brackets,";
		}
		if (!empty ($insertArray["xsdmf_citation_order"])) {
			$stmt .= "xsdmf_citation_order,";
		}
		if (!empty ($insertArray["xsdmf_citation_prefix"])) {
			$stmt .= "xsdmf_citation_prefix,";
		}
		if (!empty ($insertArray["xsdmf_citation_suffix"])) {
			$stmt .= "xsdmf_citation_suffix,";
		}

		$stmt .= "
		                    xsdmf_parent_key_match,
		                    xsdmf_key_match,";
		if (!empty ($insertArray["xsdmf_xdis_id_ref"])) {
			$stmt .= "xsdmf_xdis_id_ref,";
		}
		if (!empty ($insertArray["xsdmf_id_ref"])) {
			$stmt .= "xsdmf_id_ref,";
		}
		if (!empty ($insertArray["xsdmf_id_ref_save_type"])) {
			$stmt .= "xsdmf_id_ref_save_type,";
		}

		if (!empty ($insertArray["xsdmf_smarty_variable"])) {
			$stmt .= "xsdmf_smarty_variable,";
		}
		if (!empty ($insertArray["xsdmf_fez_variable"])) {
			$stmt .= "xsdmf_fez_variable,";
		}
		if (!empty ($insertArray["xsdmf_dynamic_selected_option"])) {
			$stmt .= "xsdmf_dynamic_selected_option,";
		}
		if (!empty ($insertArray["xsdmf_selected_option"])) {
			$stmt .= "xsdmf_selected_option,";
		}
		if (is_numeric($insertArray["xsdmf_show_in_view"])) {
			$stmt .= "xsdmf_show_in_view,";
		}

		if (!empty ($insertArray["xsdmf_use_parent_option_list"])) {
			$stmt .= "xsdmf_use_parent_option_list,";
		}
		if (!empty ($insertArray["xsdmf_parent_option_xdis_id"])) {
			$stmt .= "xsdmf_parent_option_xdis_id,";
		}
		if (!empty ($insertArray["xsdmf_parent_option_xsdmf_id"])) {
			$stmt .= "xsdmf_parent_option_xsdmf_id,";
		}
		if (!empty ($insertArray["xsdmf_asuggest_xdis_id"])) {
			$stmt .= "xsdmf_asuggest_xdis_id,";
		}
		if (!empty ($insertArray["xsdmf_asuggest_xsdmf_id"])) {
			$stmt .= "xsdmf_asuggest_xsdmf_id,";
		}
		if (!empty ($insertArray["xsdmf_cvo_save_type"])) {
			$stmt .= "xsdmf_cvo_save_type,";
		}
		if (!empty ($insertArray["xsdmf_cvo_min_level"])) {
			$stmt .= "xsdmf_cvo_min_level,";
		}
		if (!empty ($insertArray["xsdmf_cvo_id"])) {
			$stmt .= "xsdmf_cvo_id,";
		}

		if (!empty ($insertArray["xsdmf_org_level"])) {
			$stmt .= "xsdmf_org_level,";
		}
		if (!empty ($insertArray["xsdmf_use_org_to_fill"])) {
			$stmt .= "xsdmf_use_org_to_fill,";
		}
		if (!empty ($insertArray["xsdmf_org_fill_xdis_id"])) {
			$stmt .= "xsdmf_org_fill_xdis_id,";
		}
		if (!empty ($insertArray["xsdmf_org_fill_xsdmf_id"])) {
			$stmt .= "xsdmf_org_fill_xsdmf_id,";
		}
		if (!empty ($insertArray["xsdmf_attached_xsdmf_id"])) {
			$stmt .= "xsdmf_attached_xsdmf_id,";
		}

		$stmt .= "xsdmf_enforced_prefix,
							xsdmf_value_prefix,
							xsdmf_image_location,
							xsdmf_static_text,
							xsdmf_dynamic_text,
							xsdmf_original_xsdmf_id";
		if (is_numeric($xsdsel_id)) {
			$stmt .= ", xsdmf_xsdsel_id";
		}
		$stmt .= "
		                 ) VALUES (
		                    ".$xdis_id.",
		                    '" . Misc::escapeString($insertArray["xsdmf_element"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_title"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_description"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_html_input"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_validation_type"]) . "',";

		if (!empty ($insertArray["xsdmf_order"])) {
			$stmt .= $insertArray["xsdmf_order"] . ",";
		}
        
		if (!empty ($insertArray["xsdmf_enabled"])) {
			$stmt .= $insertArray["xsdmf_enabled"] . ",";
		}

		if (!empty ($insertArray["xsdmf_required"])) {
			$stmt .= $insertArray["xsdmf_required"] . ",";
		}

		if (!empty ($insertArray["xsdmf_indexed"])) {
			$stmt .= $insertArray["xsdmf_indexed"] . ",";
		}
		if (!empty ($insertArray["xsdmf_multiple"])) {
			$stmt .= $insertArray["xsdmf_multiple"] . ",";
		}

			if (!empty($insertArray["xsdmf_multiple_limit"])) {
               $stmt .= $insertArray["xsdmf_multiple_limit"] . ",";
			}
			if (!empty($insertArray["xsdmf_sek_id"])) {
               $stmt .= $insertArray["xsdmf_sek_id"] . ",";
			}
			if (!empty($insertArray["xsdmf_valueintag"])) {
               $stmt .= $insertArray["xsdmf_valueintag"] . ",";
			}
			if (!empty($insertArray["xsdmf_is_key"])) {
               $stmt .= $insertArray["xsdmf_is_key"] . ",";
			}
			if (!empty($insertArray["xsdmf_meta_header_name"])) {
               $stmt .= "'".$insertArray["xsdmf_meta_header_name"] . "',";
			}

		if (!empty ($insertArray["xsdmf_meta_header"])) {
			$stmt .= $insertArray["xsdmf_meta_header"] . ",";
		}

		if (!empty ($insertArray["xsdmf_citation_browse"])) {
			$stmt .= $insertArray["xsdmf_citation_browse"] . ",";
		}
		if (!empty ($insertArray["xsdmf_citation"])) {
			$stmt .= $insertArray["xsdmf_citation"] . ",";
		}
		if (!empty ($insertArray["xsdmf_citation_bold"])) {
			$stmt .= $insertArray["xsdmf_citation_bold"] . ",";
		}
		if (!empty ($insertArray["xsdmf_citation_italics"])) {
			$stmt .= $insertArray["xsdmf_citation_italics"] . ",";
		}
		if (!empty ($insertArray["xsdmf_citation_brackets"])) {
			$stmt .= $insertArray["xsdmf_citation_brackets"] . ",";
		}
		if (!empty ($insertArray["xsdmf_citation_order"])) {
			$stmt .= $insertArray["xsdmf_citation_order"] . ",";
		}
		if (!empty ($insertArray["xsdmf_citation_prefix"])) {
			$stmt .= "'" . $insertArray["xsdmf_citation_prefix"] . "',";
		}
		if (!empty ($insertArray["xsdmf_citation_suffix"])) {
			$stmt .= "'" . $insertArray["xsdmf_citation_suffix"] . "',";
		}
		$stmt .= "
		                    '" . Misc::escapeString($insertArray["xsdmf_parent_key_match"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_key_match"]) . "',";

		if (!empty ($insertArray["xsdmf_xdis_id_ref"])) {
			$stmt .= Misc::escapeString($insertArray["xsdmf_xdis_id_ref"]) . ",";
		}
		if (!empty ($insertArray["xsdmf_id_ref"])) {
			$stmt .= Misc::escapeString($insertArray["xsdmf_id_ref"]) . ",";
		}
		if (!empty ($insertArray["xsdmf_id_ref_save_type"])) {
			$stmt .= Misc::escapeString($insertArray["xsdmf_id_ref_save_type"]) . ",";
		}

		if (!empty ($insertArray["xsdmf_smarty_variable"])) {
			$stmt .= "'" . Misc::escapeString($insertArray["xsdmf_smarty_variable"]) . "',";
		}
		if (!empty ($insertArray["xsdmf_fez_variable"])) {
			$stmt .= "'" . Misc::escapeString($insertArray["xsdmf_fez_variable"]) . "',";
		}
		if (!empty ($insertArray["xsdmf_dynamic_selected_option"])) {
			$stmt .= "'" . Misc::escapeString($insertArray["xsdmf_dynamic_selected_option"]) . "',";
		}
		if (!empty ($insertArray["xsdmf_selected_option"])) {
			$stmt .= "'" . Misc::escapeString($insertArray["xsdmf_selected_option"]) . "',";
		}
		if (is_numeric($insertArray["xsdmf_show_in_view"])) {
			$stmt .= $insertArray["xsdmf_show_in_view"] . ",";
		}

		if (!empty ($insertArray["xsdmf_use_parent_option_list"])) {
			$stmt .= $insertArray["xsdmf_use_parent_option_list"] . ",";
		}
		if (!empty ($insertArray["xsdmf_parent_option_xdis_id"])) {
			$stmt .= $insertArray["xsdmf_parent_option_xdis_id"] . ",";
		}
		if (!empty ($insertArray["xsdmf_parent_option_xsdmf_id"])) {
			$stmt .= $insertArray["xsdmf_parent_option_xsdmf_id"] . ",";
		}
		if (!empty ($insertArray["xsdmf_asuggest_xdis_id"])) {
			$stmt .= $insertArray["xsdmf_asuggest_xdis_id"] . ",";
		}
		if (!empty ($insertArray["xsdmf_asuggest_xsdmf_id"])) {
			$stmt .= $insertArray["xsdmf_asuggest_xsdmf_id"] . ",";
		}
		if (!empty ($insertArray["xsdmf_cvo_save_type"])) {
			$stmt .= $insertArray["xsdmf_cvo_save_type"] . ",";
		}
		if (!empty ($insertArray["xsdmf_cvo_min_level"])) {
			$stmt .= $insertArray["xsdmf_cvo_min_level"] . ",";
		}
		if (!empty ($insertArray["xsdmf_cvo_id"])) {
			$stmt .= $insertArray["xsdmf_cvo_id"] . ",";
		}

		if (!empty ($insertArray["xsdmf_org_level"])) {
			$stmt .= "'" . Misc::escapeString($insertArray["xsdmf_org_level"]) . "',";
		}
		if (!empty ($insertArray["xsdmf_use_org_to_fill"])) {
			$stmt .= $insertArray["xsdmf_use_org_to_fill"] . ",";
		}
		if (!empty ($insertArray["xsdmf_org_fill_xdis_id"])) {
			$stmt .= $insertArray["xsdmf_org_fill_xdis_id"] . ",";
		}
		if (!empty ($insertArray["xsdmf_org_fill_xsdmf_id"])) {
			$stmt .= $insertArray["xsdmf_org_fill_xsdmf_id"] . ",";
		}
		if (!empty ($insertArray["xsdmf_attached_xsdmf_id"])) {
			$stmt .= $insertArray["xsdmf_attached_xsdmf_id"] . ",";
		}
		$stmt .= "
		                    '" . Misc::escapeString($insertArray["xsdmf_enforced_prefix"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_value_prefix"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_image_location"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_static_text"]) . "',
		                    '" . Misc::escapeString($insertArray["xsdmf_dynamic_text"]) . "',
							" . $insertArray["xsdmf_id"];

		if (is_numeric($xsdsel_id)) {
			$stmt .= ", " . $xsdsel_id;
		}
		$stmt .= "
		                 )";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1;
		} else {
			return 1;
			//
		}
	}

	/**
	 * Method used to add a new custom field to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($xdis_id, $xml_element) {
		global $HTTP_POST_VARS;

		if (@ $HTTP_POST_VARS["enabled"]) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		if (@ $HTTP_POST_VARS["multiple"]) {
			$multiple = 1;
		} else {
			$multiple = 0;
		}
		if (@ $HTTP_POST_VARS["required"]) {
			$required = 1;
		} else {
			$required = 0;
		}
		if (@ $HTTP_POST_VARS["indexed"]) {
			$indexed = 1;
		} else {
			$indexed = 0;
		}

		if (@ $HTTP_POST_VARS["valueintag"]) {
			$valueintag = 1;
		} else {
			$valueintag = 0;
		}

		if (@ $HTTP_POST_VARS["show_in_view"]) {
			$show_in_view = 1;
		} else {
			$show_in_view = 0;
		}

		if (@ $HTTP_POST_VARS["is_key"]) {
			$is_key = 1;
		} else {
			$is_key = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_meta_header"]) {
			$xsdmf_meta_header = 1;
		} else {
			$xsdmf_meta_header = 0;
		}

		if (@ $HTTP_POST_VARS["xsdmf_citation_browse"]) {
			$xsdmf_citation_browse = 1;
		} else {
			$xsdmf_citation_browse = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_citation"]) {
			$xsdmf_citation = 1;
		} else {
			$xsdmf_citation = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_citation_bold"]) {
			$xsdmf_citation_bold = 1;
		} else {
			$xsdmf_citation_bold = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_citation_italics"]) {
			$xsdmf_citation_italics = 1;
		} else {
			$xsdmf_citation_italics = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_citation_brackets"]) {
			$xsdmf_citation_brackets = 1;
		} else {
			$xsdmf_citation_brackets = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_use_parent_option_list"]) {
			$xsdmf_use_parent_option_list = 1;
		} else {
			$xsdmf_use_parent_option_list = 0;
		}
		if (@ $HTTP_POST_VARS["xsdmf_use_org_to_fill"]) {
			$xsdmf_use_org_to_fill = 1;
		} else {
			$xsdmf_use_org_to_fill = 0;
		}
		if (is_numeric($HTTP_POST_VARS["xsdsel_id"])) {
			$extra_where = " AND xsdmf_xsdsel_id = " . $HTTP_POST_VARS["xsdsel_id"];
		} else {
			$extra_where = " AND xsdmf_xsdsel_id IS NULL";
		}

		$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET 
		                    xsdmf_title = '" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
		                    xsdmf_description = '" . Misc::escapeString($HTTP_POST_VARS["description"]) . "',
		                    xsdmf_long_description = '" . Misc::escapeString($HTTP_POST_VARS["long_description"]) . "',
		                    xsdmf_html_input = '" . Misc::escapeString($HTTP_POST_VARS["field_type"]) . "',
		                    xsdmf_validation_type = '" . Misc::escapeString($HTTP_POST_VARS["validation_types"]) . "',
		                    xsdmf_order = " . Misc::escapeString($HTTP_POST_VARS["order"]) . ",
		                    xsdmf_date_type = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_date_type"]) . ",					
		                    xsdmf_cvo_id = " . $HTTP_POST_VARS["xsdmf_cvo_id"] . ",					
		                    xsdmf_use_org_to_fill = " . $xsdmf_use_org_to_fill . ",
		                    xsdmf_use_parent_option_list = " . $xsdmf_use_parent_option_list . ",
		                    xsdmf_required = " . $required . ",
		                    xsdmf_indexed = " . $indexed . ",
		                    xsdmf_enabled = " . $enabled . ",
		                    xsdmf_meta_header_name = '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_meta_header_name"]) . "',
		                    xsdmf_meta_header = " . $xsdmf_meta_header . ",
		                    xsdmf_citation_browse = " . $xsdmf_citation_browse . ",
		                    xsdmf_citation = " . $xsdmf_citation . ",
		                    xsdmf_citation_bold = " . $xsdmf_citation_bold . ",
		                    xsdmf_citation_italics = " . $xsdmf_citation_italics . ",										
		                    xsdmf_citation_brackets = " . $xsdmf_citation_brackets . ",
		                    xsdmf_citation_prefix = '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_citation_prefix"]) . "',
		                    xsdmf_citation_suffix = '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_citation_suffix"]) . "',
		                    xsdmf_multiple = " . $multiple . ",";
		if ($HTTP_POST_VARS["multiple_limit"] != "") {
			$stmt .= " xsdmf_multiple_limit = " . Misc::escapeString($HTTP_POST_VARS["multiple_limit"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_sek_id"])) {
			$stmt .= " xsdmf_sek_id = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_sek_id"]) . ",";
		} else {
			$stmt .= " xsdmf_sek_id = NULL,";
		}
		if ($HTTP_POST_VARS["xsdmf_org_level"] != "") {
			$stmt .= " xsdmf_org_level = '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_org_level"]) . "',";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_org_fill_xdis_id"])) {
			$stmt .= " xsdmf_org_fill_xdis_id = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_org_fill_xdis_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_org_fill_xsdmf_id"])) {
			$stmt .= " xsdmf_org_fill_xsdmf_id = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_org_fill_xsdmf_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_parent_option_xdis_id"])) {
			$stmt .= " xsdmf_parent_option_xdis_id = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_parent_option_xdis_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_parent_option_child_xsdmf_id"])) {
			$stmt .= " xsdmf_parent_option_child_xsdmf_id = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_parent_option_child_xsdmf_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_asuggest_xdis_id"])) {
			$stmt .= " xsdmf_asuggest_xdis_id = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_asuggest_xdis_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_asuggest_xsdmf_id"])) {
			$stmt .= " xsdmf_asuggest_xsdmf_id = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_asuggest_xsdmf_id"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_cvo_min_level"])) {
			$stmt .= " xsdmf_cvo_min_level = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_cvo_min_level"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_cvo_save_type"])) {
			$stmt .= " xsdmf_cvo_save_type = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_cvo_save_type"]) . ",";
		}

		if (is_numeric($HTTP_POST_VARS["xsdmf_citation_order"])) {
			$stmt .= " xsdmf_citation_order = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_citation_order"]) . ",";
		}
		if ($HTTP_POST_VARS["smarty_variable"] != "") {
			$stmt .= " xsdmf_smarty_variable = '" . Misc::escapeString($HTTP_POST_VARS["smarty_variable"]) . "',";
		}
		if ($HTTP_POST_VARS["fez_variable"] != "") {
			$stmt .= " xsdmf_fez_variable = '" . Misc::escapeString($HTTP_POST_VARS["fez_variable"]) . "',";
		}
		if ($HTTP_POST_VARS["dynamic_selected_option"] != "") {
			$stmt .= " xsdmf_dynamic_selected_option = '" . Misc::escapeString($HTTP_POST_VARS["dynamic_selected_option"]) . "',";
		}
		if (!empty ($HTTP_POST_VARS["selected_option"])) {
			$stmt .= " xsdmf_selected_option = '" . Misc::escapeString($HTTP_POST_VARS["selected_option"]) . "',";
		}

		$stmt .= "
		                    xsdmf_valueintag = " . $valueintag . ",
		                    xsdmf_is_key = " . $is_key . ",
		                    xsdmf_show_in_view = " . $show_in_view . ",
		                    xsdmf_key_match = '" . Misc::escapeString($HTTP_POST_VARS["key_match"]) . "',
		                    xsdmf_parent_key_match = '" . Misc::escapeString($HTTP_POST_VARS["parent_key_match"]) . "',
		                    xsdmf_data_type = '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_data_type"]) . "',";
		if (is_numeric($HTTP_POST_VARS["attached_xsdmf_id"])) {
			$stmt .= "   xsdmf_attached_xsdmf_id = " . Misc::escapeString($HTTP_POST_VARS["attached_xsdmf_id"]) . ",";
		}
		elseif (trim($HTTP_POST_VARS["attached_xsdmf_id"]) == "") {
			$stmt .= "   xsdmf_attached_xsdmf_id = NULL,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_xdis_id_ref"])) {
			$stmt .= " xsdmf_xdis_id_ref = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_xdis_id_ref"]) . ",";
		}
		elseif (trim($HTTP_POST_VARS["xsdmf_xdis_id_ref"]) == "") {
			$stmt .= "   xsdmf_xdis_id_ref = NULL,";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_id_ref"])) {
			$stmt .= " xsdmf_id_ref = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_id_ref"]) . ",";
		}
		elseif (trim($HTTP_POST_VARS["xsdmf_id_ref"]) == "") {
			$stmt .= "   xsdmf_id_ref = NULL,";
		}

		if (is_numeric($HTTP_POST_VARS["xsdmf_id_ref_save_type"])) {
			$stmt .= " xsdmf_id_ref_save_type = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_id_ref_save_type"]) . ",";
		}
		elseif (trim($HTTP_POST_VARS["xsdmf_id_ref_save_type"]) == "") {
			$stmt .= "   xsdmf_id_ref_save_type = NULL,";
		}

		$stmt .= "
		                    xsdmf_enforced_prefix = '" . Misc::escapeString($HTTP_POST_VARS["enforced_prefix"]) . "',
		                    xsdmf_value_prefix = '" . Misc::escapeString($HTTP_POST_VARS["value_prefix"]) . "',
		                    xsdmf_image_location = '" . Misc::escapeString($HTTP_POST_VARS["image_location"]) . "',
		                    xsdmf_dynamic_text = '" . Misc::escapeString($HTTP_POST_VARS["dynamic_text"]) . "',
		                    xsdmf_static_text = '" . Misc::escapeString($HTTP_POST_VARS["static_text"]) . "'";
		$stmt .= " WHERE xsdmf_xdis_id = $xdis_id AND xsdmf_element = '" . $xml_element . "'" . $extra_where;

		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1;
		} else {
			// update the custom field options, if any
			if (($HTTP_POST_VARS["field_type"] == "combo") || ($HTTP_POST_VARS["field_type"] == "multiple")) {
				$stmt = "SELECT
				                            mfo_id
				                         FROM
				                            " . APP_TABLE_PREFIX . "xsd_display_mf_option
				                         WHERE
				                            mfo_fld_id=" . $HTTP_POST_VARS['xsdmf_id'];
				$current_options = $GLOBALS["db_api"]->dbh->getCol($stmt);
				$updated_options = array ();

				foreach ($HTTP_POST_VARS["field_options"] as $option_value) {
					$params = XSD_HTML_Match::parseParameters($option_value);
					if ($params["type"] == 'new') {
						XSD_HTML_Match::addOptions($HTTP_POST_VARS["xsdmf_id"], $params["value"]);
					} else {
						$updated_options[] = $params["id"];
						// check if the user is trying to update the value of this option
						if ($params["value"] != XSD_HTML_Match::getOptionValue($HTTP_POST_VARS["xsdmf_id"], $params["id"])) {
							XSD_HTML_Match::updateOption($params["id"], $params["value"]);
						}
					}
				}
			}

			// get the diff between the current options and the ones posted by the form
			// and then remove the options not found in the form submissions
			if (in_array($HTTP_POST_VARS["field_type"], array (
					'combo',
					'multiple'
				))) {
				$params = XSD_HTML_Match::parseParameters($option_value);
				$diff_ids = @ array_diff($current_options, $updated_options);
				if (@ count($diff_ids) > 0) {
					XSD_HTML_Match::removeOptions($HTTP_POST_VARS['xsdmf_id'], array_values($diff_ids));
				}
			}

		}
	}

    function updateFromArray($xsdmf_id, $params)
    {
    	$stmt = "UPDATE " . APP_TABLE_PREFIX . "xsd_display_matchfields " .
                "SET ";
        foreach (XSD_HTML_Match::$xsdmf_columns as $col_name) {
            if ($col_name == 'xsdmf_id') {
            	// don't set the id
                continue;
            }
            $value = Misc::escapeString(@$params[$col_name]);
            if (strstr($col_name, '_id') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_multiple') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_required') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_order') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_is_key') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_show_in_view') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_cvo_min_level') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_enabled') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_citation_order') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } elseif (strstr($col_name, 'xsdmf_valueintag') && empty($value)) {
                $stmt .= " ".$col_name."=null,\n";
            } else {
                $stmt .= " ".$col_name."='".$value."',\n";
            }
        }
        $stmt = rtrim($stmt,", \n"); // get rid of trailing comma
        $stmt .= " WHERE xsdmf_id='".$xsdmf_id."' ";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array (
            $res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }
    
	/**
	 * Method used to try and get a XSDMF ID from an xsdmf_element
	 *
	 * @access  public
	 * @param   string $xsdmf_element The xsdmf element to search for
	 * @param   string $xsdmf_xdis_id The XSD display to search for
	 * @return  array The XSDMF ID, or false if not found or more than one was found.
	 */
	function getXSDMF_IDByElement($xsdmf_element, $xsdmf_xdis_id) {
		$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_element = '".$xsdmf_element."' and xsdmf_xdis_id = ".$xsdmf_xdis_id." and xsdmf_xsdsel_id IS NULL";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	/**
	 * Method used to update an idref to a new value for an xsdmf id record
	 *
	 * @access  public
	 * @param   string $xsdmf_id the XSD MF ID to update
	 * @param   string $new_xsdmf_id_ref The new id ref to replace the old one with
	 * @return  "" if failed, 1 if success.
	 */
	function updateXSDMF_ID_REF($xsdmf_id, $new_xsdmf_id_ref, $new_id_ref_xdis_id) {
		$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_id_ref = ".$new_xsdmf_id_ref.",
		                     xsdmf_xdis_id_ref = ".$new_id_ref_xdis_id."
		                 WHERE
		                    xsdmf_id = ".$xsdmf_id;
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return 1;
		}
	}

	/**
	 * Method used to update an author suggest xdis id and xsdmf id to a new value for an xsdmf id record (mainly for cloning)
	 *
	 * @access  public
	 * @param   string $xsdmf_id the XSD MF ID to update
	 * @param   string $new_xsdmf_id_ref The new id ref to replace the old one with
	 * @return  "" if failed, 1 if success.
	 */
	function updateAuthorSuggestTarget($xsdmf_id, $new_xsdmf_id, $new_xdis_id) {
		$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_asuggest_xsdmf_id = ".$new_xsdmf_id.",
		                     xsdmf_asuggest_xdis_id = ".$new_xdis_id."
		                 WHERE
		                    xsdmf_id = ".$xsdmf_id;
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return 1;
		}
	}

	/**
	 * Method used to update an organisation struction fill target xdis id and xsdmf id to a new value for an xsdmf id record (mainly for cloning)
	 *
	 * @access  public
	 * @param   string $xsdmf_id the XSD MF ID to update
	 * @param   string $new_xsdmf_id The new id to replace the old one with
	 * @return  "" if failed, 1 if success.
	 */
	function updateOrgFillTarget($xsdmf_id, $new_xsdmf_id, $new_xdis_id) {
		$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_org_fill_xsdmf_id = ".$new_xsdmf_id.",
		                     xsdmf_org_fill_xdis_id = ".$new_xdis_id."
		                 WHERE
		                    xsdmf_id = ".$xsdmf_id;
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return 1;
		}
	}

	/**
	 * Method used to update parent option fill target xdis id and xsdmf id to a new value for an xsdmf id record (mainly for cloning)
	 *
	 * @access  public
	 * @param   string $xsdmf_id the XSD MF ID to update
	 * @param   string $new_xsdmf_id The new id to replace the old one with
	 * @return  "" if failed, 1 if success.
	 */
	function updateParentOptionTarget($xsdmf_id, $new_xsdmf_id, $new_xdis_id) {
		$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_parent_option_child_xsdmf_id = ".$new_xsdmf_id.",
		                     xsdmf_parent_option_xdis_id = ".$new_xdis_id."
		                 WHERE
		                    xsdmf_id = ".$xsdmf_id;
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return 1;
		}
	}

	/**
	 * Method used to update attached xsdmf id targe and xsdmf id to a new value for an xsdmf id record (mainly for cloning)
	 *
	 * @access  public
	 * @param   string $xsdmf_id the XSD MF ID to update
	 * @param   string $new_xsdmf_id The new id to replace the old one with
	 * @return  "" if failed, 1 if success.
	 */
	function updateAttachedTarget($xsdmf_id, $new_xsdmf_id) {
		$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_attached_xsdmf_id = ".$new_xsdmf_id."
		                 WHERE
		                    xsdmf_id = ".$xsdmf_id;
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return 1;
		}
	}

	/**
	 * Method used to try and get a XSDMF ID from an xsdmf_element
	 *
	 * @access  public
	 * @param   string $xsdmf_element The xsdmf element to search for
	 * @param   string $xdis_str The string comma separated list of xdis_id's to search through
	 * @return  array The XSDMF ID, or false if not found or more than one was found.
	 */
	function getXSDMF_IDByXDIS_ID($xsdmf_element, $xdis_str) {
		$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                     xsdmf_element = '".$xsdmf_element."' and xsdmf_xdis_id in (".$xdis_str.") and (xsdmf_is_key != 1 || xsdmf_is_key is null)";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	/**
	  * getXSDMF_IDByKeyXDIS_ID
	  * look for key match on the attribute value - this is where the matchfield needs the 
	  * attribute to be set to a certain value to match.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   string $element_value 
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
	function getXSDMF_IDByKeyXDIS_ID($xsdmf_element, $element_value, $xdis_str) {
		$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    ('".$xsdmf_element."' = xsdmf_element) and xsdmf_xdis_id in (".$xdis_str.") and xsdmf_is_key = 1 and ('".$element_value."' = xsdmf_key_match)";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	/**
	  * getXSDMF_IDByParentKeyXDIS_ID
	  * look for key match on the attribute value - this is where the matchfield needs the 
	  * attribute to be set to a certain value to match, by parent key.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   string $parent_key The parent key to use in the search 
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
	function getXSDMF_IDByParentKeyXDIS_ID($xsdmf_element, $parent_key, $xdis_str) {

		$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    '".$xsdmf_element."' = xsdmf_element and xsdmf_xdis_id in (".$xdis_str.") and xsdmf_parent_key_match = '".$parent_key."'";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	/**
	  * getXSDMF_IDByParentKeyXDIS_ID
	  * look for key match on the attribute value - this is where the matchfield needs the 
	  * attribute to be set to a certain value to match, by parent key.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   string $key_value The key value to search by.
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
		 * @param   integer $xsdsel_id The xsdsel ID to search by.
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
	function getXSDMF_IDByKeyXDIS_IDSEL_ID($xsdmf_element, $key_value, $xdis_str, $xsdsel_id) {
		if (!is_array($xsdsel_ids)) {
			return false;
		}
		$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    ('".$xsdmf_element."' = xsdmf_element) and xsdmf_xdis_id in (".$xdis_str.") and xsdmf_is_key = 1 and ('".$key_value."' = xsdmf_key_match) and xsdmf_xsdsel_id = ".$xsdsel_id;
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	/**
	  * look for key match on the attribute value - this is where the matchfield needs the 
	  * attribute to be set to a certain value to match, by element SEL ID.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   integer $xsdsel_id The xsdsel ID to search by.
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
	function getXSDMF_IDByElementSEL_ID($xsdmf_element, $xsdsel_id, $xdis_str) {
		$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_element = '".$xsdmf_element."' and xsdmf_xdis_id in (".$xdis_str.") and xsdmf_xsdsel_id=" . $xsdsel_id;

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	function getXSDMF_IDByElementSEL_Title($xsdmf_element, $xsdsel_title, $xdis_id) {
		$stmt = "SELECT
		                    xsdmf_id
		                FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields
						INNER JOIN " . APP_TABLE_PREFIX . "xsd_loop_subelement on xsdmf_xsdsel_id = xsdsel_id
		                 WHERE
		                    xsdmf_element = '".$xsdmf_element."' and xsdmf_xdis_id = ".$xdis_id." and xsdsel_title='" . $xsdsel_title."'";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	/**
	  * getXSDMF_IDByParentKeyXDIS_ID
	  * look for key match on the attribute value - this is where the matchfield needs the 
	  * attribute to be set to a certain value to match, by parent key.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   array $xsdsel_id The list of xsdsel IDs to search by.
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
	function getXSDMF_IDByElementSEL_IDArray($xsdmf_element, $xsdsel_ids, $xsdmf_xdis_id) {
		if (!is_array($xsdsel_ids)) {
			return false;
		}
		$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_element = '".$xsdmf_element."' and xsdmf_xdis_id = ".$xsdmf_xdis_id." and xsdmf_xsdsel_id in (" . implode("," . $xsdsel_id) . ")";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	/**
	  * getXSDMF_IDByOriginalXSDMF_ID
	  * Returns the new xsdmf_id of an entry by its previous (original from a clone) xsdmf_id. Mainly used by the XSD Display clone function.
		 *
	  * @access  public
		 * @param   integer $original_xsdmf_id The xsdmf id element to search for
		 * @param   integer $xdis_id The xdis_id display ID to search in
	  * @return  integer The new XSDMF ID, or false if not found or more than one was found.
	  */
	function getXSDMF_IDByOriginalXSDMF_ID($original_xsdmf_id, $xdis_id) {
		if (!is_numeric($original_xsdmf_id)) {
			return false;
		}
		$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_original_xsdmf_id = " . $original_xsdmf_id . " AND xsdmf_xdis_id = " . $xdis_id;
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt);

		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0][0];
			}
		}
	}

	
	/**
	  * getXSDMF_IDsBySekTitle
	  * Returns a list of XSDMF_IDs matching a sek title
	  *
	  * @access  public
	  * @param   string $sek_title
	  * @return  array $res The list of xsdmf_ids
	  */
	function getXSDMF_IDsBySekTitle($sek_title, $nocache = false) {
		static $returns;				
		if (!$sek_title) {
			return array();
		}
		if (!empty($returns[$sek_title]) && !$nocache) { 
			return $returns[$sek_title];
		} else {		
			$stmt = "SELECT
	                   xsdmf_id
	                FROM
	                   " . APP_TABLE_PREFIX . "xsd_display_matchfields x1
					INNER JOIN " . APP_TABLE_PREFIX . "search_key AS s1  	
	                ON
	                   x1.xsdmf_sek_id = s1.sek_id and s1.sek_title = '".Misc::escapeString($sek_title)."'";			
			$res = $GLOBALS["db_api"]->dbh->getCol($stmt);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array (
				$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				return "";
			} else {
				if ($GLOBALS['app_cache']) {
				    $returns[$sek_title] = $res;
                }
				return $res;
			}
		}
	}

	/**
	 * getXSDMF_IDsBySekID
	 * Returns a list of XSDMF_IDs matching a sek ID
	 *
	 * @access  public
	 * @param   integer $sek_id
	 * @return  array $res The list of xsdmf_ids
	 */
	function getXSDMF_IDsBySekID($sek_id, $nocache = false) {
		static $returns;
		if (!is_numeric($sek_id)) {
			return array();
		}
		if (!empty($returns[$sek_id]) && !$nocache) {
			return $returns[$sek_id];
		} else {
			$stmt = "SELECT
	                   xsdmf_id
	                FROM
	                   " . APP_TABLE_PREFIX . "xsd_display_matchfields x1
					WHERE
	                   x1.xsdmf_sek_id = ".$sek_id;			
			$res = $GLOBALS["db_api"]->dbh->getCol($stmt);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array (
				$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				return "";
			} else {
				if ($GLOBALS['app_cache']) {
				    $returns[$sek_id] = $res;
                }
				return $res;
			}
		}
	}
	
	/**
	 * Method used to get the list of XSD HTML Matching fields available in the 
	 * system.
	 *
	 * @access  public
	 * @return  array The list of custom fields
	 */
	function getListAssoc() {
		$stmt = "SELECT
		                    xsdmf_id, xsdmf_element
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 ORDER BY
		                    xsdmf_element ASC";
		$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return $res;
		}
	}

	function getList($xdis_id) {
		$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE xsdmf_xdis_id='".$xdis_id."'
		                 ORDER BY
		                    xsdmf_element ASC";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			return $res;
		}
	}

	/**
	 * Method used to get the details of a specific XSD HTML Matching Field.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @param   string $xml_element The XML element to search by.
	 * @return  array The field details
	 */
	function getDetails($xdis_id, $xml_element) {
		$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
		                 WHERE
							 xsdmf_element='".$xml_element."' AND (xsdmf_xsdsel_id IS NULL) AND xsdmf_xdis_id=" . $xdis_id;
		$res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
			return $res;
		}
	}

	/**
	 * Method used to get the details of a specific XSD HTML Matching Field, by XSDMF ID
	 *
	 * @access  public
	 * @param   integer $xsdmf_id
	 * @return  array The details
	 */
	function getDetailsByXSDMF_ID($xsdmf_id) {
		$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
		                 WHERE
		                    xsdmf_id=" . $xsdmf_id;
		$res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
			return $res;
		}
	}

	/**
	 * Method used to get the XSD Display ID with a given XSDMFID
	 *
	 * @access  public
	 * @param   integer $xdis_title The XSD title to search by.
	 * @return  array $res The xdis_id 
	 */
	function getXDIS_IDByXSDMF_ID($xsdmf_id) {
		$stmt = "SELECT
		                   xsdmf_xdis_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_id = ".$xsdmf_id;
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return $res;
		}
	}

	/**
	 * Method used to get the details of a specific XSD HTML Matching Field, by xml element and sublooping id.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID
	 * @param   string $xml_element The XML element to search by.
	 * @param   integer $xsdsel_id The sublooping element to search by
	 * @return  array The custom field details
	 */
	function getDetailsSubelement($xdis_id, $xml_element, $xsdsel_id) {
		$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
		                 WHERE
		                    xsdmf_element='".$xml_element."' AND xsdmf_xsdsel_id = ".$xsdsel_id." AND xsdmf_xdis_id=" . $xdis_id;
		$res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
			return $res;
		}
	}

	/**
	 * Method used to get the XSD_ID parent of a given XSD Display.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID to search by.
	 * @return  array The XSD ID
	 */
	function getXSD_ID($xdis_id) {
		$stmt = "SELECT
		                    xdis_xsd_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display
		                 WHERE
		                    xdis_id=".$xdis_id;
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			return $res;
		}
	}

	/**
	 * Method used to check if the XSD matching field is attached to other fields, therefore gets handled differently in FOXML::array_to_xml_instance
	 *
	 * @access  public
	 * @param   integer $xsdmf_id The XSD matching field ID to search for
	 * @return  boolean true or false
	 */
	function isAttachedXSDMF($xsdmf_id) {
		$stmt = "SELECT count(*) as 
		                    attach_count
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_attached_xsdmf_id=".$xsdmf_id." and xsdmf_id not in 
							(select distinct ifnull(xsdmf_attached_xsdmf_id, 0)
		        			   from " . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_id = ".$xsdmf_id.");";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			if (is_numeric($res) && ($res > 0)) {
				return true;
			} else {
				return false;
			}
		}
	}

	function getChildren($xsdmf_id) {
		$stmt = "SELECT *  
		                 FROM " . APP_TABLE_PREFIX . "xsd_display_attach
		                 WHERE att_parent_xsdmf_id = '".$xsdmf_id."'";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			return $res;
		}
	}

	function setChild($xsdmf_id, $att_child_xsdmf_id, $att_order) {
		$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "xsd_display_attach" .
		"(att_parent_xsdmf_id, att_child_xsdmf_id, att_order)" .
		"VALUES" .
		"('".$xsdmf_id."', '".$att_child_xsdmf_id."', '".$att_order."')";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1;
		} else {
			return $GLOBALS["db_api"]->get_last_insert_id();
		}
	}

	/**
	 * Method used to get the list of matching field options associated
	 * with a given matching field ID.
	 *
	 * @access  public
	 * @param   integer $fld_id The matching field ID
	 * @param   array $fld_id If the fld_id is an array of fld_ids, then the fucntion will return a list
	 *                        of matching options for all the fields.
	 * @return  array The list of matching field options as array(mfo_id => mfo_value), 
	 *                 or if an array was passed, array(fld_id => array(mfo_id, mfo_value))
	 */
	function getOptions($fld_id) {
		static $mfo_returns;
		if (!empty ($mfo_returns[$fld_id])) { // check if this has already been found and set to a static variable		
			return $mfo_returns[$fld_id];
		} else {

			if (is_array($fld_id)) {
				$fld_id_str = implode(',', $fld_id);
				$stmt = "SELECT
										mfo_id,
										mfo_value
									FROM
										" . APP_TABLE_PREFIX . "xsd_display_mf_option
									WHERE
										mfo_fld_id IN (".$fld_id_str.")
									ORDER BY
										mfo_value ASC";				
				$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
			} else {

				$stmt = "SELECT
									mfo_id,
									mfo_value
									FROM
										" . APP_TABLE_PREFIX . "xsd_display_mf_option
										WHERE
										mfo_fld_id='".$fld_id."'
										ORDER BY
										mfo_value ASC";				
				$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
				if (!PEAR::isError($res)) {
					if ($GLOBALS['app_cache']) {
					    $mfo_returns[$fld_id] = $res;
                    }
				}
			}
			if (PEAR::isError($res)) {
				Error_Handler::logError(array (
				$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				return array ();
			}
			return $res;
		}
	}

	/**
	 * Method used to get the of matching field option value with a given field id
	 *
	 * @access  public
	 * @param   integer $fld_id The matching field ID
	 * @return  array The list of matching field options as array(mfo_id => mfo_value), 
	 *                 or if an array was passed, array(fld_id => array(mfo_id, mfo_value))
	 */
	function getOptionsValueOnly($fld_id) {
		$stmt = "SELECT
		                    mfo_value, mfo_value
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 WHERE
		                    mfo_fld_id=".$fld_id."
		                 ORDER BY
		                    mfo_value ASC";
		$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return "";
		} else {
			$res2 = array ();
			foreach ($res as $key => $value) {
				$res2[utf8_encode($key)] = $value;
			}
			return $res;
		}
	}

	/**
	 * Method used to parse the special format used in the combo boxes
	 * in the administration section of the system, in order to be 
	 * used as a way to flag the system for whether the matching field
	 * option is a new one or one that should be updated.
	 *
	 * @access  private
	 * @param   string $value The matching field option format string
	 * @return  array Parameters used by the update/insert methods
	 */
	function parseParameters($value) {
		if (substr($value, 0, 4) == 'new:') {
			return array (
				"type" => "new",
				"value" => substr($value,
				4
			));
		} else {
			$value = substr($value, strlen("existing:"));
			return array (
				"type" => "existing",
				"id" => substr($value,
				0,
				strpos($value,
				":"
			)), "value" => substr($value, strpos($value, ":") + 1));
		}
	}

	/**
	 * Method used to get list of XSDMF elements belonging to a XSD Display
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID to search by.
	 * @return  array The list of XSDMF elements
	 */
	function getElementMatchList($xdis_id) {
		$stmt = "SELECT 
		                    xsdmf_element
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_xdis_id='".$xdis_id."'
		                    ";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			$ret = array ();
			foreach ($res as $record) {
				$ret[] = $record['xsdmf_element'];
			}
			return $ret;
		}
	}

	/**
	 * Method used to get list of XSDMF elements belonging to a XSD Display plus some extra display information
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID to search by.
	 * @return  array The list of XSDMF elements
	 */
	function getElementMatchListDetails($xdis_id) {
		$stmt = "SELECT 
		                    xsdmf_id, xsdmf_element, xsdmf_title, xsdmf_id_ref, xsdmf_html_input, xsdmf_enabled, xsdmf_order, xsdmf_dynamic_text, xsdmf_static_text, xsdmf_xsdsel_id, xsdsel_title
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
		                 WHERE
		                    xsdmf_xdis_id='".$xdis_id."'
		                    ";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			$ret = array ();
			foreach ($res as $record) {
				$ret[$record['xsdmf_element']][] = $record;
			}
			return $ret;
		}
	}

	/**
	 * Method used to get list of XSDMF elements belonging to a XSD Display, but not found in the XSD Source itself.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID to search by.
	 * @return  array The list of orphaned XSDMF elements (for deletion usually).
	 */
	function getElementOrphanList($xdis_id, $xsd_array) {
		$xsd_list = Misc::array_flatten($xsd_array);
		$xsd_list = implode("', '", $xsd_list);
        $sel_list = XSD_HTML_Match::getSubloopingElementByXDIS_ID($xdis_id);
        $sel_list = implode(",", $sel_list);
		$stmt = "SELECT 
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
							" . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
		                 WHERE
		                    x1.xsdmf_xdis_id=".$xdis_id." and (x1.xsdmf_element not in ('".$xsd_list."') or x1.xsdmf_xsdsel_id not in (
                                ".$sel_list."
								))
		                    ";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			return $res;
		}
	}

   function getSubloopingElementsByXDIS_ID($xdis_id){
     if (!is_numeric($xdis_id)) {
       return array(0);
     }

     $stmt = " SELECT distinct(s2.xsdsel_id) FROM
                                     " . APP_TABLE_PREFIX . "xsd_loop_subelement s2 left join
                                     " . APP_TABLE_PREFIX . "xsd_display_matchfields x2 on (x2.xsdmf_xsdsel_id = s2.xsdsel_id)
                                  WHERE x2.xsdmf_xdis_id = ".$xdis_id;

        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array (
            $res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array(0);
        } else {
            return $res;
        }
    }

	/**
	 * Method used to get the count of XSDMF elements belonging to a XSD Display, but not found in the XSD Source itself.
	 *
	 * @access  public
	 * @param   integer $xdis_id The XSD Display ID to search by.
	 * @return  integer The count of orphaned XSDMF elements (for deletion usually).
	 */
	function getElementOrphanCount($xdis_id, $xsd_array) {
		$xsd_list = Misc::array_flatten($xsd_array);
		$xsd_list = implode("', '", $xsd_list);
        $sel_list = XSD_HTML_Match::getSubloopingElementsByXDIS_ID($xdis_id);
        $sel_list = implode(",", $sel_list);
		$stmt = "SELECT 
		                    count(*) as orphan_count
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
							" . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
		                 WHERE
		                    x1.xsdmf_xdis_id=".$xdis_id;
        if ($xsd_list !== "" || $sel_list !== "") {
            $stmt .= " and (";            
            if ($xsd_list !== "") {
                $stmt .= "x1.xsdmf_element not in ('".$xsd_list."') ";
            }
            if ($sel_list !== "") {
                if ($xsd_list !== "") {
                    $stmt .= " or ";
                }
                $stmt .= "x1.xsdmf_xsdsel_id not in (".$sel_list.")";
            }
            $stmt .= ")";
        }

		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			return $res;
		}
	}

	function exportMatchFields(& $xdis, $xdis_id) {
		$list = XSD_HTML_Match::getList($xdis_id);
		foreach ($list as $item) {
			$xmatch = $xdis->ownerDocument->createElement('matchfield');
			foreach (XSD_HTML_Match::$xsdmf_columns as $field) {
				$xmatch->setAttribute($field, $item[$field]);
			}
			$att_list = XSD_HTML_Match::getChildren($item['xsdmf_id']);
			if (!empty ($att_list)) {
				foreach ($att_list as $att) {
					$xatt = $xdis->ownerDocument->createElement('attach');
					$xatt->setAttribute('att_id', $att['att_id']);
					$xatt->setAttribute('att_child_xsdmf_id', $att['att_child_xsdmf_id']);
					$xatt->setAttribute('att_order', $att['att_order']);
					$xmatch->appendChild($xatt);
				}
			}
			$mfo_list = XSD_HTML_Match::getOptions($item['xsdmf_id']);
			if (is_array($mfo_list)) {
				foreach ($mfo_list as $mfo_key => $mfo_value) {
					if (!empty ($mfo_value)) {
						$xmfo = $xdis->ownerDocument->createElement('option');
						$xmfo->setAttribute('mfo_id', $mfo_key);
						$xmfo->setAttribute('mfo_value', $mfo_value);
						$xmatch->appendChild($xmfo);
					}
				}
			}
			$subs = XSD_Loop_Subelement::getSimpleListByXSDMF($item['xsdmf_id']);
			if (!empty ($subs)) {
				foreach ($subs as $sub) {
					$xsub = $xdis->ownerDocument->createElement('loop_subelement');
					$xsub->setAttribute('xsdsel_id', $sub['xsdsel_id']);
					$xsub->setAttribute('xsdsel_title', $sub['xsdsel_title']);
					$xsub->setAttribute('xsdsel_type', $sub['xsdsel_type']);
					$xsub->setAttribute('xsdsel_order', $sub['xsdsel_order']);
					$xsub->setAttribute('xsdsel_attribute_loop_xdis_id', $sub['xsdsel_attribute_loop_xdis_id']);
					$xsub->setAttribute('xsdsel_attribute_loop_xsdmf_id', $sub['xsdsel_attribute_loop_xsdmf_id']);
					$xsub->setAttribute('xsdsel_indicator_xdis_id', $sub['xsdsel_indicator_xdis_id']);
					$xsub->setAttribute('xsdsel_indicator_xsdmf_id', $sub['xsdsel_indicator_xsdmf_id']);
					$xsub->setAttribute('xsdsel_indicator_value', $sub['xsdsel_indicator_value']);
					$xmatch->appendChild($xsub);
				}
			}
			$rels = XSD_Relationship::getSimpleListByXSDMF($item['xsdmf_id']);
			if (!empty ($rels)) {
				foreach ($rels as $rel) {
					$xrel = $xdis->ownerDocument->createElement('relationship');
					$xrel->setAttribute('xsdrel_id', $rel['xsdrel_id']);
					$xrel->setAttribute('xsdrel_xdis_id', $rel['xsdrel_xdis_id']);
					$xrel->setAttribute('xsdrel_order', $rel['xsdrel_order']);
					$xmatch->appendChild($xrel);
				}
			}
			$xdis->appendChild($xmatch);
		}
	}
	function importMatchFields($xdis, $xdis_id, & $maps) {
		$xpath = new DOMXPath($xdis->ownerDocument);
		$xmatches = $xpath->query('matchfield', $xdis);
		foreach ($xmatches as $xmatch) {
			$params = array ();
			foreach (XSD_HTML_Match::$xsdmf_columns as $field) {
				$params[$field] = $xmatch->getAttribute($field);
			}
			$params['xsdmf_xdis_id'] = $xdis_id;
			$xsdmf_id = XSD_HTML_Match::insertFromArray($xdis_id, $params);
			$maps['xsdmf_map'][$xmatch->getAttribute('xsdmf_id')] = $xsdmf_id;
			$xpath = new DOMXPath($xmatch->ownerDocument);
			$xatts = $xpath->query('attach', $xmatch);
			foreach ($xatts as $xatt) {
				XSD_HTML_Match::setChild($xsdmf_id, $xatt->getAttribute('att_child_xsdmf_id'), $xatt->getAttribute('att_order'));
			}
			$xopts = $xpath->query('option', $xmatch);
			$opts = array ();
			foreach ($xopts as $xopt) {
				$opts[] = $xopt->getAttribute('mfo_value');
			}
			XSD_HTML_Match::addOptions($xsdmf_id, $opts);
			XSD_Loop_Subelement::importSubelements($xmatch, $xsdmf_id, $maps);
			XSD_Relationship::importRels($xmatch, $xsdmf_id, $maps);
		}

	}

	/**
	 * This is the second pass of the import which looks for inserted records which reference other records
	 * These references need to be updated to point to the ids used in the DB instead of in the XML file
	 */
	function remapImport(& $maps, & $bgp) {
		if (empty ($maps['xsdmf_map'])) {
			return;
		}
		// find all the stuff that references the new displays
		$xsdmf_ids = array_values(@ $maps['xsdmf_map']);
		$xsdmf_ids_str = Misc::arrayToSQL($xsdmf_ids);
        
        $bgp->setStatus("Remapping XSDMF ids");
        foreach ($maps['xsdmf_map'] as $xsdmf_id) {
        	$stmt = "SELECT * FROM ". APP_SQL_DBNAME . "." . APP_TABLE_PREFIX ."xsd_display_matchfields " .
                    "WHERE xsdmf_id='".$xsdmf_id."' ";
            $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            } else {
                Misc::arraySearchReplace($res, 
                    array('xsdmf_xdis_id_ref','xsdmf_parent_option_xdis_id','xsdmf_asuggest_xdis_id'),
                    $maps['xdis_map']);
                Misc::arraySearchReplace($res, 
                    array('xsdmf_original_xsdmf_id',
                    'xsdmf_attached_xsdmf_id',
                    'xsdmf_parent_option_child_xsdmf_id',
                    'xsdmf_org_fill_xsdmf_id',
                    'xsdmf_asuggest_xsdmf_id',
                    'xsdmf_id_ref'),
                    $maps['xsdmf_map']);
                Misc::arraySearchReplace($res, 
                    array('xsdmf_xsdsel_id'),
                    $maps['xsdsel_map']);
                XSD_HTML_Match::updateFromArray($xsdmf_id,$res);
            }
            // remap attachments
            $stmt = "SELECT * FROM ". APP_SQL_DBNAME . "." . APP_TABLE_PREFIX ."xsd_display_attach " .
                    "WHERE att_parent_xsdmf_id='".$xsdmf_id."' ";
            $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            } else {
            	if (!empty($res)) {
                    Misc::arraySearchReplace($res, 
                        array('att_child_xsdmf_id'),
                        $maps['xsdmf_map']);
                    $stmt = "UPDATE ". APP_SQL_DBNAME . "." . APP_TABLE_PREFIX ."xsd_display_attach " .
                            "SET " ;
                    foreach ($res as $key => $value) {
                        $stmt .= " ".$key." = '".$value."', ";        	
                    }
                    $stmt = rtrim($stmt, ', ');
                    $stmt .= " WHERE att_parent_xsdmf_id='".$xsdmf_id."' ";
                    $res = $GLOBALS["db_api"]->dbh->query($stmt);
                    if (PEAR::isError($res)) {
                        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                    }
                }
            }
        }
			
		// remap the sublooping elements
		$bgp->setStatus("Remapping Sub Looping Elements");
		XSD_Loop_Subelement::remapImport($maps);
		//remap the relationships
		$bgp->setStatus("Remapping XSD Relationships");
		XSD_Relationship::remapImport($maps);
		$bgp->setStatus("Remapping Citation templates");
		Citation::remapImport($maps);

	}

	function escapeXPath($xpath) {
		$element_text = str_replace('/', '!', $xpath);
		// get rid of root element
		$element_text = preg_replace('/![^!]+/', '', $element_text, 1);
		$element_text = preg_replace('/@/', '', $element_text);
		// get rid of prefixes (unless it's dc) 
		if (!strstr($element_text, '!dc:')) {
			$element_text = preg_replace('/![^:]+:/', '!', $element_text);
		}
		return $element_text;
	}


} // end class XSD_HTML_Match

/**
 * XSD_HTML_MatchObject
 * Object for managing display fields matching against XML datastreams.
 */
class XSD_HTML_MatchObject {
	var $xdis_str;
//	var $xdis_array = array(); // doesnt appear to be used in here anymore - CK

	/** 
	 * XSD_HTML_MatchObject
	 * Instantiate object with a list of displays that relate to the main display being matched
	 */
	function XSD_HTML_MatchObject($xdis_str) {
		$this->xdis_str = $xdis_str;
	}

	/**
	 * getMatchCols
	 * Retrieve the matchfields records that relate to the current display and store them locally.  This 
	 * method keeps a local copy of the results to save making multiple queries for the same information.
	 */
	function getMatchCols() {
		// Check for a global var
		if (!empty($GLOBALS['match_cols'][$this->xdis_str])) {
			return $GLOBALS['match_cols'][$this->xdis_str];
		}
		
		// stop this global array growing too large.
		if (count($GLOBALS['match_cols']) > 10) {
			$GLOBALS['match_cols'] = array();
		}
		
			// do query to get all the match cols for this display set
			$stmt = "SELECT
			                   m1.*,
							   s1.xsdsel_title,
                               s1.xsdsel_indicator_xsdmf_id,
                               s1.xsdsel_indicator_value,
                               x1.xsd_element_prefix,
                               m2.xsdmf_element as indicator_element,
			   				   m2.xsdmf_xsdsel_id as indicator_xsdsel_id
			                FROM
			                    " . APP_TABLE_PREFIX . "xsd_display_matchfields m1 left join
								" . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (m1.xsdmf_id = s1.xsdsel_xsdmf_id) left join
								" . APP_TABLE_PREFIX . "xsd_display d1 on (m1.xsdmf_xdis_id = d1.xdis_id)  left join
								" . APP_TABLE_PREFIX . "xsd x1 on (d1.xdis_xsd_id = x1.xsd_id) left join 
								" . APP_TABLE_PREFIX . "xsd_display_matchfields m2 on (m2.xsdmf_id = s1.xsdsel_indicator_xsdmf_id)
			                WHERE
			                    m1.xsdmf_xdis_id in (".$this->xdis_str.")";

			$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array (
				$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			$GLOBALS['match_cols'][$this->xdis_str] = array ();
		} else {
			$GLOBALS['match_cols'][$this->xdis_str] = $res;
		}
		return $GLOBALS['match_cols'][$this->xdis_str];
	}


	/**
	 * getXSDMF_IDByParentKeyXDIS_ID
	 * Find a match for an element that has a parent key element with the matched value.
	 */
	function getXSDMF_IDByParentKeyXDIS_ID($xsdmf_element, $parent_key) {
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if (($xsdmf['xsdmf_element'] == $xsdmf_element) 
					&& ($xsdmf['xsdmf_parent_key_match'] == $parent_key) && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf['xsdmf_id'];
			}
		}
		return null;
	}

	/**
	 * getXSDMF_IDByXDIS_ID
	 * Find a match for the given element
	 */
	function getXSDMF_IDByXDIS_ID($xsdmf_element) {
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if (($xsdmf['xsdmf_element'] == $xsdmf_element) && !$xsdmf['xsdmf_is_key'] && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf['xsdmf_id']; // just returns the first one if there are many
			}
		}
		return null;
	}

	/**
	 * getXSDMFByElement
	 * Find a match for the given element
     * @param string $xsdmf_element - xml element to search for (escaped with '!')
     * @param string $xdis_ids - Comma delimited list. Specify sub XSD disply mappings to use.  
     *                          If null will default to all for the current display.
	 */
	function getXSDMFByElement($xsdmf_element, $xdis_ids=null) {
		if (empty($xdis_ids)) {
		    $xdis_str = $this->xdis_str;
		} else {
		    $xdis_str = $xdis_ids;
		}
        
        $stmt = "SELECT
		                   m1.xsdmf_element, 
		                   m1.xsdmf_id,  
		                   m1.xsdmf_is_key, 
		                   m1.xsdmf_key_match, 
		                   m1.xsdmf_parent_key_match, 
		                   m1.xsdmf_xsdsel_id,
		                   m1.xsdmf_value_prefix,
						   m1.xsdmf_html_input,
						   s1.xsdsel_title,
						   s1.xsdsel_indicator_xsdmf_id,
						   s1.xsdsel_indicator_value,
						   x1.xsd_element_prefix,
						   m2.xsdmf_element as indicator_element,
		   				   m2.xsdmf_xsdsel_id as indicator_xsdsel_id
		                FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields m1 left join
							" . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (m1.xsdmf_id = s1.xsdsel_xsdmf_id) left join
							" . APP_TABLE_PREFIX . "xsd_display d1 on (m1.xsdmf_xdis_id = d1.xdis_id)  left join
							" . APP_TABLE_PREFIX . "xsd x1 on (d1.xdis_xsd_id = x1.xsd_id) left join 
							" . APP_TABLE_PREFIX . "xsd_display_matchfields m2 on (m2.xsdmf_id = s1.xsdsel_indicator_xsdmf_id)
		                WHERE
		                    m1.xsdmf_xdis_id in (".$xdis_str.") and m1.xsdmf_element = '".$xsdmf_element."'";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return array ();
		} else {
			return $res;
		}
	}

	/**
	 * getXSDMF_IDByKeyXDIS_ID 
	 * Find a match field for an element that matches a key on the element value 
	 *
	 * @access  public
	 * @param   string $xsdmf_element 
	 * @param   string $element_value
	 * @return  integer The xsdmf_id
	 */
	function getXSDMF_IDByKeyXDIS_ID($xsdmf_element, $element_value) {
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if (($xsdmf['xsdmf_element'] == $xsdmf_element) && ($xsdmf['xsdmf_key_match'] == $element_value) && $xsdmf['xsdmf_is_key'] && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf['xsdmf_id'];
			}
		}
		return null;
	}

	/**
	 * getXSDMF_IDByKeyXDIS_ID 
	 * Find a match field for an element that matches a subelement loop on the element value 
	 *
	 * @access  public
	 * @param   string $xsdmf_element 
	 * @param   string $element_value
	 * @return  integer The xsdmf_id
	 */
	function getXSDMF_IDBySELXDIS_ID($xsdmf_element, $xsdsel_id) {
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if (($xsdmf['xsdmf_element'] == $xsdmf_element) && ($xsdmf['xsdmf_xsdsel_id'] == $xsdsel_id) && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf['xsdmf_id'];
			}
		}
		return null;
	}
	
	function getXSDMF_IDBySEK($sek_id)
	{
         foreach ($this->getMatchCols() as $xsdmf ) {
	         if ($xsdmf['xsdmf_sek_id'] == $sek_id) {
				return $xsdmf['xsdmf_id'];
			}
		}
	}
	/**
	 * getDetailsByXSDMF_ID 
	 * Retrieve the details of a match field
	 *
	 * @access  public
	 * @param   integer $xsdmf_id 
	 * @return  array The details
	 */
	function getDetailsByXSDMF_ID($xsdmf_id) {
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if ($xsdmf['xsdmf_id'] == $xsdmf_id) {
				return $xsdmf;
			}
		}
		return null;
	}
	function getDetailsByElement($xsdmf_element) {
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if ($xsdmf['xsdmf_element'] == $xsdmf_element && !$xsdmf['xsdmf_is_key'] && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf;
			}
		}
		return null;
	}
	
	/**
	 * Finds an xsdmf_id by element name within a sub-looping element.
	 * @param string $loop_base_element - the element that is the base of the sub looping mapping
	 * @param string $loop_name - title of the sub looping element
	 * @param string $element - element to find within the sub looping element
	 * @return integer The xsdmf_id or a negative number if there was an error or the item isn't mapped
	 */
	function getXSDMF_ID_ByElementInSubElement($loop_base_element, $loop_name, $element)
    {
		// use the static function because it excludes members of sublooping elements - so will get the base
		// sublooping element even if there are mappings on the base
    	$sub_xsdmf_id = $this->getSELBaseXSDMF_IDByElement($loop_base_element);
		if (!empty($sub_xsdmf_id)) {							
			$subs = XSD_Loop_Subelement::getSimpleListByXSDMF($sub_xsdmf_id);
		}
		if (!empty($subs)) {
			foreach ($subs as $sub) {
				if ($sub['xsdsel_title'] == $loop_name) {
					$sub_id = $sub['xsdsel_id'];
				}
			}
		}
		if (!empty($sub_id)) {
			$xsdmf_id = $this->getXSDMF_IDBySELXDIS_ID($element, $sub_id);
			return $xsdmf_id;
		} else {
			return -1;
		}
    }
    
    function getSELBaseXSDMF_IDByElement($loop_base_element, $xdis_ids = '')
    {
		if (empty($xdis_ids)) {
		    $xdis_str = $this->xdis_str;
		} else {
		    $xdis_str = $xdis_ids;
		}
        
        $stmt = "SELECT
		                   m1.xsdmf_id
		                FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields m1
		                WHERE
		                    m1.xsdmf_xdis_id in (".$xdis_str.") 
							AND m1.xsdmf_element = '".$loop_base_element."'
							AND m1.xsdmf_xsdsel_id IS NULL
							";

		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array (
			$res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return false;
		} else {
			return $res;
		}
    }
}

// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
	$GLOBALS['bench']->setMarker('Included XSD_HTML_Match Class');
}
?>
