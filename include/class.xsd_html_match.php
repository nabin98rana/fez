<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Record Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.class.custom_field.php 1.28 03/12/31 17:29:00-00:00 jpradomaia $
//


/**
 * Class to handle the business logic related to the administration
 * of custom fields in the system.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.xsd_relationship.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");

class XSD_HTML_Match
{
    /**
     * Method used to remove a group of custom field options.
     *
     * @access  public
     * @param   array $fld_id The list of custom field IDs
     * @param   array $fld_id The list of custom field option IDs
     * @return  boolean
     */
    function removeOptions($fld_id, $mfo_id)
    {
        if (!is_array($fld_id)) {
            $fld_id = array($fld_id);
        }
        if (!is_array($mfo_id)) {
            $mfo_id = array($mfo_id);
        }
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                 WHERE
                    mfo_id IN (" . implode(",", $mfo_id) . ")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            // also remove any custom field option that is currently assigned to an issue
            // XXX: review this
            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                     WHERE
                        icf_fld_id IN (" . implode(", ", $fld_id) . ") AND
                        icf_value IN (" . implode(", ", $mfo_id) . ")";
            $GLOBALS["db_api"]->dbh->query($stmt);
            return true;
        }
    }

    /**
     * Method used to add possible options into a given custom field.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @param   array $options The list of options that need to be added
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function addOptions($fld_id, $options)
    {
        if (!is_array($options)) {
            $options = array($options);
        }
        foreach ($options as $option) {
            $stmt = "INSERT INTO
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                     (
                        mfo_fld_id,
                        mfo_value
                     ) VALUES (
                        $fld_id,
                        '" . Misc::escapeString($option) . "'
                     )";
//			echo $stmt;
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            }
        }
        return 1;
    }


    /**
     * Method used to update an existing custom field option value.
     *
     * @access  public
     * @param   integer $mfo_id The custom field option ID
     * @param   string $mfo_value The custom field option value
     * @return  boolean
     */
    function updateOption($mfo_id, $mfo_value)
    {
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                 SET
                    mfo_value='" . Misc::escapeString($mfo_value) . "'
                 WHERE
                    mfo_id=" . $mfo_id;
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method used to update the values stored in the database. 
     *
     * @access  public
     * @return  integer 1 if the update worked properly, any other value otherwise
     */
    function updateValues()
    {
        global $HTTP_POST_VARS;

        // get the types for all of the custom fields being submitted
        $stmt = "SELECT
                    fld_id,
                    fld_type
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field
                 WHERE
                    fld_id IN (" . implode(", ", @array_keys($HTTP_POST_VARS['custom_fields'])) . ")";
        $field_types = $GLOBALS["db_api"]->dbh->getAssoc($stmt);

        foreach ($HTTP_POST_VARS["custom_fields"] as $fld_id => $value) {
            if (($field_types[$fld_id] != 'multiple') && ($field_types[$fld_id] != 'combo') && $fld_id != 6 && $fld_id != 8) {
                // first check if there is actually a record for this field for the issue
                $stmt = "SELECT
                            icf_id
                         FROM
                            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                         WHERE
                            icf_iss_id=" . $HTTP_POST_VARS["issue_id"] . " AND
                            icf_fld_id=$fld_id";
                $icf_id = $GLOBALS["db_api"]->dbh->getOne($stmt);
                if (PEAR::isError($icf_id)) {
                    Error_Handler::logError(array($icf_id->getMessage(), $icf_id->getDebugInfo()), __FILE__, __LINE__);
                    return -1;
                }
                if (empty($icf_id)) {
                    // record doesn't exist, insert new record
                    $stmt = "INSERT INTO
                                " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                             (
                                icf_iss_id,
                                icf_fld_id,
                                icf_value
                             ) VALUES (
                                " . $HTTP_POST_VARS["issue_id"] . ",
                                $fld_id,
                                '" . Misc::escapeString($value) . "'
                             )";
                    $res = $GLOBALS["db_api"]->dbh->query($stmt);
                    if (PEAR::isError($res)) {
                        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                        return -1;
                    }
                } else {
					// record exists, update it
					$stmt = "UPDATE
								" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
							 SET
								icf_value='" . Misc::escapeString($value) . "'
							 WHERE
									icf_id=$icf_id";
					$res = $GLOBALS["db_api"]->dbh->query($stmt);
					if (PEAR::isError($res)) {
						Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
						return -1;
					}
            	}				
            } elseif ($fld_id != 6 && $fld_id != 8){
                // need to remove all associated options from issue_custom_field and then 
                // add the selected options coming from the form
                XSD_HTML_Match::removeRecordAssociation($fld_id, $HTTP_POST_VARS["issue_id"]);
                if (@count($value) > 0) {
                    XSD_HTML_Match::associateRecord($HTTP_POST_VARS["issue_id"], $fld_id, $value);
                }
            }
        } // end of foreach

		// @@@ - CK - 7/9/2004 added so custom6, 8 can update their values
		$remainder[0] = $HTTP_POST_VARS["custom6"];
		$remainder[1] = $HTTP_POST_VARS["custom8"];
		$remainder = array(6 => $HTTP_POST_VARS["custom6"], 8 => $HTTP_POST_VARS["custom8"]);
			foreach ($remainder as $fld_id => $value) {
				$stmt = "SELECT
                            icf_id
                         FROM
                            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                         WHERE
                            icf_iss_id=" . $HTTP_POST_VARS["issue_id"] . " AND
                            icf_fld_id=$fld_id";
                $icf_id = $GLOBALS["db_api"]->dbh->getOne($stmt);
                if (PEAR::isError($icf_id)) {
                    Error_Handler::logError(array($icf_id->getMessage(), $icf_id->getDebugInfo()), __FILE__, __LINE__);
                    return -1;
                }
                if (empty($icf_id)) {
                    // record doesn't exist, insert new record
                    $stmt = "INSERT INTO
                                " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                             (
                                icf_iss_id,
                                icf_fld_id,
                                icf_value
                             ) VALUES (
                                " . $HTTP_POST_VARS["issue_id"] . ",
                                $fld_id,
                                '" . Misc::escapeString($value) . "'
                             )";
                    $res = $GLOBALS["db_api"]->dbh->query($stmt);
                    if (PEAR::isError($res)) {
                        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                        return -1;
                    }
                } else {
					// record exists, update it
					$stmt = "UPDATE
								" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
							 SET
								icf_value='" . Misc::escapeString($value) . "'
							 WHERE
									icf_id=$icf_id";
					$res = $GLOBALS["db_api"]->dbh->query($stmt);
					if (PEAR::isError($res)) {
						Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
						return -1;
					}
            	}				
			}
		// @@@ - End of custom 6 and 8 code


        Record::markAsUpdated($HTTP_POST_VARS["issue_id"]);
        // need to save a history entry for this
        History::add($HTTP_POST_VARS["issue_id"], Auth::getUserID(), History::getTypeID('custom_field_updated'), 'Custom field updated by ' . User::getFullName(Auth::getUserID()));
        return 1;
    }


    /**
     * Method used to associate a custom field value to a given
     * issue ID.
     *
     * @access  public
     * @param   integer $iss_id The issue ID
     * @param   integer $fld_id The custom field ID
     * @param   string  $value The custom field value
     * @return  boolean Whether the association worked or not
     */
    function associateRecord($iss_id, $fld_id, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        foreach ($value as $item) {
            $stmt = "INSERT INTO
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                     (
                        icf_iss_id,
                        icf_fld_id,
                        icf_value
                     ) VALUES (
                        $iss_id,
                        $fld_id,
                        '" . Misc::escapeString($item) . "'
                     )";
            $GLOBALS["db_api"]->dbh->query($stmt);
        }
        return true;
    }

    /**
     * Method used to get the list of custom fields associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
    function getListByDisplaySpecify($xdis_id, $specify_titles=array('FezACML'))
    {
        $stmt = "SELECT
                    xsdmf_id,
                    xsdmf_element,
                    xsdmf_title,
                    xsdmf_description,
                    xsdmf_html_input,
                    xsdmf_order,
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
                    xsdmf_id_ref,
					xsdsel_order
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdsel_id = xsdmf_xsdsel_id)
                 WHERE
                   xsdmf_xdis_id=$xdis_id AND xsdmf_enabled=1";
//		foreach ($exclude_titles as $ex_title) {
//			$stmt .= " AND xsdmf_xdis_id not in (".implode(",", $exclude_titles).") "
//		}


		
	// @@@ CK - Added order statement to custom fields displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order, xsdmf_order ASC";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {





/*			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
*/
			//print_r($res);
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
//							$res = array_merge($res, $ref_display); // @@@ CK - 19/5/2005 swapped these around for now so that file upload would show at the bottom
						}
					}
				}
				//@@@ CK - 29/4/2005 - Added multiple_array as an element so smarty could look the html input elements

				if (($record['xsdmf_multiple'] == 1) && (is_numeric($record['xsdmf_multiple_limit']))) {
					$res[$rkey]['multiple_array'] = array();
					for($x=1; $x<($record['xsdmf_multiple_limit']+1); $x++) {
						array_push($res[$rkey]['multiple_array'], $x);
					}
				}
			}			

		if (count($res) == 0) {
			return "";
		} else {
            echo "About to do ".strval(count($res) * 2)." queries on line ".__LINE__."\n";
			for ($i = 0; $i < count($res); $i++) {
				$res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["xsdmf_id"]);
				$res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);
			}
			return $res;
		}

			

//            return $res;
        }
    }



    /**
     * Method used to get the list of custom fields associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
    function getListByDisplayExclude($xdis_id, $exclude_titles=array('FezACML'))
    {
        $stmt = "SELECT
                    xsdmf_id,
                    xsdmf_element,
                    xsdmf_title,
                    xsdmf_description,
                    xsdmf_html_input,
                    xsdmf_order,
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
					xsdmf_data_type,
					xsdmf_value_prefix,
                    xsdmf_id_ref,
					xsdsel_order
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdsel_id = xsdmf_xsdsel_id)
                 WHERE
                   xsdmf_xdis_id=$xdis_id AND xsdmf_enabled=1";
//		foreach ($exclude_titles as $ex_title) {
//			$stmt .= " AND xsdmf_xdis_id not in (".implode(",", $exclude_titles).") "
//		}


		
	// @@@ CK - Added order statement to custom fields displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order, xsdmf_order ASC";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {





/*			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
*/
			//print_r($res);
			// Add any reference displays
			$exclude_ids = XSD_Display::getIDs($exclude_titles);
			foreach ($res as $rkey => $record) {
				if ($record['xsdmf_html_input'] == 'xsd_ref') {
					$ref = XSD_Relationship::getListByXSDMF($record['xsdmf_id']);
					if (is_array($ref)) {
						foreach ($ref as $reference) {
							if (is_array($exclude_ids) && (count($exclude_ids) > 0)) {
								if (!in_array($reference['xsdrel_xdis_id'], $exclude_ids)) {
									$ref_display = XSD_HTML_Match::getListByDisplay($reference['xsdrel_xdis_id']);
									$res = array_merge($ref_display, $res);
								}
							} else {
								$ref_display = XSD_HTML_Match::getListByDisplay($reference['xsdrel_xdis_id']);
								$res = array_merge($ref_display, $res);
							}
//							$res = array_merge($res, $ref_display); // @@@ CK - 19/5/2005 swapped these around for now so that file upload would show at the bottom
						}
					}
				}
				//@@@ CK - 29/4/2005 - Added multiple_array as an element so smarty could look the html input elements

				if (($record['xsdmf_multiple'] == 1) && (is_numeric($record['xsdmf_multiple_limit']))) {
					$res[$rkey]['multiple_array'] = array();
					for($x=1; $x<($record['xsdmf_multiple_limit']+1); $x++) {
						array_push($res[$rkey]['multiple_array'], $x);
					}
				}
			}			

		if (count($res) == 0) {
			return "";
		} else {
            echo "About to do ".strval(count($res) * 2)." queries on line ".__LINE__."\n";
			for ($i = 0; $i < count($res); $i++) {
				$res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["xsdmf_id"]);
				$res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);
			}
			return $res;
		}

			

//            return $res;
        }
    }

    function getBasicListByDisplay($xdis_id)
    {
        $stmt = "SELECT
                    xsdmf_id,
                    xsdmf_element,
                    xsdmf_title,
                    xsdmf_description,
                    xsdmf_html_input,
                    xsdmf_order,
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
					xsdmf_data_type,
					xsdmf_key_match,
					xsdmf_parent_key_match,
					xsdmf_value_prefix,
					xsdmf_static_text,
					xsdmf_xsdsel_id,
					xsdmf_image_location,
                    xsdmf_id_ref,
					xsdsel_order,
					xsdmf_cvo_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields as m1 left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement as s1 on (xsdsel_id = xsdmf_xsdsel_id)
                 WHERE
                   m1.xsdmf_xdis_id=$xdis_id AND xsdmf_enabled=1 OR m1.xsdmf_xdis_id in (
					 SELECT r2.xsdrel_xdis_id
					 FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship r2
					 WHERE r2.xsdrel_xsdmf_id in (
						 SELECT m3.xsdmf_id FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields as m3 WHERE m3.xsdmf_xdis_id=$xdis_id
					 )
				   )";
		// @@@ CK - Added order statement to custom fields displayed in a desired order
		$stmt .= " ORDER BY xsdmf_order, xsdsel_order ASC";
//		$stmt .= " ORDER BY xsdsel_order, xsdmf_order ASC";
//		echo $stmt;

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
//		print_r($res);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {





/*			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
*/
			//print_r($res);
			// Add any reference displays
			foreach ($res as $rkey => $record) {
/*				if ($record['xsdmf_html_input'] == 'xsd_ref') {
					$ref = XSD_Relationship::getListByXSDMF($record['xsdmf_id']);
					if (is_array($ref)) {
						foreach ($ref as $reference) {
							$ref_display = XSD_HTML_Match::getListByDisplay($reference['xsdrel_xdis_id']);							
//							$res = array_merge($ref_display, $res);
							$res = array_merge($res, $ref_display); // @@@ CK - 19/5/2005 swapped these around for now so that file upload would show at the bottom
						}
					}
				} */
				//@@@ CK - 29/4/2005 - Added multiple_array as an element so smarty could look the html input elements

				if (($record['xsdmf_multiple'] == 1) && (is_numeric($record['xsdmf_multiple_limit']))) {
					$res[$rkey]['multiple_array'] = array();
					for($x=1; $x<($record['xsdmf_multiple_limit']+1); $x++) {
						array_push($res[$rkey]['multiple_array'], $x);
					}
				}
			}			
            return $res;
        }

    }

    function getNonSELChildListByDisplay($xdis_id)
    {
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
				 WHERE ISNULL(m1.xsdmf_xsdsel_id) AND m1.xsdmf_xdis_id = ".$xdis_id;
		$stmt .= " ORDER BY xsdmf_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }

    }

    function getSELChildListByDisplay($xdis_id, $xsdsel_id)
    {
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
				 WHERE (m1.xsdmf_xsdsel_id = $xsdsel_id) AND m1.xsdmf_xdis_id = ".$xdis_id;
		$stmt .= " ORDER BY xsdmf_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }

    }
    
    /**
     * Method used to get the list of custom fields associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
    function getListByDisplay($xdis_id)
    {
        $res = XSD_HTML_Match::getBasicListByDisplay($xdis_id);

		if (count($res) == 0) {
			return array();
		} else {
			for ($i = 0; $i < count($res); $i++) {
				$res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["xsdmf_id"]);
				$res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);
			}
			return $res;
		}
    }



    /**
     * Method used to get the list of custom fields associated with
     * a given project.
     *
     * @access  public
     * @param   integer $prj_id The project ID
     * @param   string $form_type The type of the form
     * @param   string $fld_type The type of field (optional)
     * @return  array The list of custom fields
     */
    function getListByCollection($prj_id, $form_type, $fld_type = false)
    {
        $stmt = "SELECT
                    fld_id,
                    fld_title,
                    fld_description,
                    fld_type,
                    fld_report_form_required,
                    fld_anonymous_form_required
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                 WHERE
                    pcf_fld_id=fld_id AND
                    pcf_prj_id=$prj_id";
        if ($form_type != '') {
            $stmt .= " AND\nfld_$form_type=1";
        }
        if ($fld_type != '') {
            $stmt .= " AND\nfld_type='$fld_type'";
        }
		// @@@ CK - Added order statement to custom fields displayed in a desired order
		$stmt .= " ORDER BY fld_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (count($res) == 0) {
                return "";
            } else {
                for ($i = 0; $i < count($res); $i++) {
                    $res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["fld_id"]);
                }
                return $res;
            }
        }
    }


    /**
     * Method used to get the custom field option value.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @param   integer $value The custom field option ID
     * @return  string The custom field option value
     */
    function getOptionValue($fld_id, $value)
    {
        if (empty($value)) {
            return "";
        }
        $stmt = "SELECT
                    mfo_value
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                 WHERE
                    mfo_fld_id=$fld_id AND
                    mfo_id=$value";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
     * Method used to get the custom field option value.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @param   integer $value The custom field option ID
     * @return  string The custom field option value
     */
    function getOptionValueByMFO_ID($mfo_id)
    {
        if (!is_numeric($mfo_id)) {
            return "";
        }
        $stmt = "SELECT
                    mfo_value
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                 WHERE
                    mfo_id=$mfo_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
     * Method used to get the list of custom fields and custom field
     * values associated with a given issue ID.
     *
     * @access  public
     * @param   integer $prj_id The project ID
     * @param   integer $iss_id The issue ID
     * @return  array The list of custom fields
     */
    function getListByRecord($prj_id, $iss_id)
    {
        $stmt = "SELECT
                    fld_id,
                    fld_title,
                    fld_type,
                    fld_report_form_required,
                    fld_anonymous_form_required,
                    icf_value
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                 ON
                    pcf_fld_id=icf_fld_id AND
                    icf_iss_id=$iss_id
                 WHERE
                    pcf_fld_id=fld_id AND
                    pcf_prj_id=$prj_id";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (count($res) == 0) {
                return "";
            } else {
                $fields = array();
                for ($i = 0; $i < count($res); $i++) {
                    if (($res[$i]['fld_type'] == 'text') || ($res[$i]['fld_type'] == 'textarea')) {
                        $fields[] = $res[$i];
                    } elseif ($res[$i]["fld_type"] == "combo") {
                        $res[$i]["selected_mfo_id"] = $res[$i]["icf_value"];
                        $res[$i]["icf_value"] = XSD_HTML_Match::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                        $res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["fld_id"]);
                        $fields[] = $res[$i];
                    } elseif ($res[$i]['fld_type'] == 'multiple') {
                        // check whether this field is already in the array
                        $found = 0;
                        for ($y = 0; $y < count($fields); $y++) {
                            if ($fields[$y]['fld_id'] == $res[$i]['fld_id']) {
                                $found = 1;
                                $found_index = $y;
                            }
                        }
                        if (!$found) {
                            $res[$i]["selected_mfo_id"] = array($res[$i]["icf_value"]);
                            $res[$i]["icf_value"] = XSD_HTML_Match::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                            $res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["fld_id"]);
                            $fields[] = $res[$i];
                        } else {
                            $fields[$found_index]['icf_value'] .= ', ' . XSD_HTML_Match::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                            $fields[$found_index]['selected_mfo_id'][] = $res[$i]["icf_value"];
                        }
                    }
                }
                return $fields;
            }
        }
    }


    /**
     * Method used to get the list of custom fields and custom field
     * values associated with a given issue ID, and a given custom field
     *
     * @@@ CK - 21/10/2004 - Created this function
     *
     * @access  public
     * @param   integer $prj_id The project ID
     * @param   integer $iss_id The issue ID
     * @param   integer $fld_id The custom field ID
     * @return  array The list of custom fields
     */
    function getValueByRecordField($prj_id, $iss_id, $fld_id)
    {
        $stmt = "SELECT
                    fld_id,
                    fld_title,
                    fld_type,
                    fld_report_form_required,
                    fld_anonymous_form_required,
                    icf_value
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                 ON
                    pcf_fld_id=icf_fld_id AND
                    icf_iss_id=$iss_id
                 WHERE
                    pcf_fld_id=fld_id AND
					pcf_fld_id=".$fld_id." AND
                    pcf_prj_id=$prj_id";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
//        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (count($res) == 0) {
                return "";
            } else {
                $fields = array();
                for ($i = 0; $i < count($res); $i++) {
                    if (($res[$i]['fld_type'] == 'text') || ($res[$i]['fld_type'] == 'textarea')) {
                        $fields[] = $res[$i];
                    } elseif ($res[$i]["fld_type"] == "combo") {
                        $res[$i]["selected_mfo_id"] = $res[$i]["icf_value"];
                        $res[$i]["icf_value"] = XSD_HTML_Match::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                        $res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["fld_id"]);
                        $fields[] = $res[$i];
                    } elseif ($res[$i]['fld_type'] == 'multiple') {
                        // check whether this field is already in the array
                        $found = 0;
                        for ($y = 0; $y < count($fields); $y++) {
                            if ($fields[$y]['fld_id'] == $res[$i]['fld_id']) {
                                $found = 1;
                                $found_index = $y;
                            }
                        }
                        if (!$found) {
                            $res[$i]["selected_mfo_id"] = array($res[$i]["icf_value"]);
                            $res[$i]["icf_value"] = XSD_HTML_Match::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                            $res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["fld_id"]);
                            $fields[] = $res[$i];
                        } else {
                            $fields[$found_index]['icf_value'] .= ', ' . XSD_HTML_Match::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                            $fields[$found_index]['selected_mfo_id'][] = $res[$i]["icf_value"];
                        }
                    }
                }
                return $fields;
            }
        }
    }


    /**
     * Method used to remove a given list of custom fields.
     *
     * @access  public
     * @return  boolean
     */
    function remove($xdis_id, $xml_element)
    {
//        global $HTTP_POST_VARS;

//        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_xdis_id = $xdis_id AND xsdmf_element='".$xml_element."'";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
/*            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                     WHERE
                        pcf_fld_id IN ($items)";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return false;
            } else {
                $stmt = "DELETE FROM
                            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                         WHERE
                            icf_fld_id IN ($items)";
                $res = $GLOBALS["db_api"]->dbh->query($stmt);
                if (PEAR::isError($res)) {
                    Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                    return false;
                } else {
                    $stmt = "DELETE FROM
                                " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                             WHERE
                                mfo_fld_id IN ($items)";
                    $res = $GLOBALS["db_api"]->dbh->query($stmt);
                    if (PEAR::isError($res)) {
                        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                        return false;
                    } else {
                        return true;
                    }
                }
            }
*/
		  return true;
        }
    }


    /**
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert($xdis_id, $xml_element)
    {
        global $HTTP_POST_VARS;

		if (@$HTTP_POST_VARS["enabled"]) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		if (@$HTTP_POST_VARS["multiple"]) {
			$multiple = 1;
		} else {
			$multiple = 0;
		}

		if (@$HTTP_POST_VARS["indexed"]) {
			$indexed = 1;
		} else {
			$indexed = 0;
		}

		
		if (@$HTTP_POST_VARS["required"]) {
			$required = 1;
		} else {
			$required = 0;
		}

		if (@$HTTP_POST_VARS["show_in_view"]) {
			$show_in_view = 1;
		} else {
			$show_in_view = 0;
		}

		if (@$HTTP_POST_VARS["valueintag"]) {
			$valueintag = 1;
		} else {
			$valueintag = 0;
		}
		if (@$HTTP_POST_VARS["is_key"]) {
			$is_key = 1;
		} else {
			$is_key = 0;
		}

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 (
                    xsdmf_xdis_id,
                    xsdmf_element,
                    xsdmf_title,
                    xsdmf_description,
                    xsdmf_html_input,
                    xsdmf_xml_order,
                    xsdmf_order,
                    xsdmf_validation_type,
                    xsdmf_enabled,
                    xsdmf_indexed,
                    xsdmf_required,
                    xsdmf_multiple,";
		if ($HTTP_POST_VARS["multiple_limit"] != "") {
          $stmt .= "xsdmf_multiple_limit,";
		}
		if ($HTTP_POST_VARS["xsdmf_sek_id"] != "") {
          $stmt .= "xsdmf_sek_id,";
		}

		$stmt .= "
                    xsdmf_valueintag,
                    xsdmf_is_key,
                    xsdmf_data_type,
                    xsdmf_parent_key_match,
                    xsdmf_key_match,";
		if ($HTTP_POST_VARS["xsdmf_id_ref"] != "") {
          $stmt .= "xsdmf_id_ref,";
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
					
		  $stmt .= "xsdmf_show_in_view,
					xsdmf_enforced_prefix,
					xsdmf_value_prefix,
					xsdmf_image_location,
					xsdmf_static_text,
					xsdmf_dynamic_text,
					xsdmf_cvo_id";
		if (is_numeric($HTTP_POST_VARS["xsdsel_id"])) {
			$stmt .= ", xsdmf_xsdsel_id";
		}
		$stmt .= "
                 ) VALUES (
                    $xdis_id,
                    '$xml_element',
                    '" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["description"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["field_type"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["xml_order"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["order"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["validation_types"]) . "',
                    " . $enabled . ",
                    " . $required . ",
                    " . $indexed . ",
                    " . $multiple . ",";
			if ($HTTP_POST_VARS["multiple_limit"] != "") {
               $stmt .= Misc::escapeString($HTTP_POST_VARS["multiple_limit"]) . ",";
			}
			if ($HTTP_POST_VARS["xsdmf_sek_id"] != "") {
               $stmt .= Misc::escapeString($HTTP_POST_VARS["xsdmf_sek_id"]) . ",";
			}
			$stmt .=
                    $valueintag . ",
                    " . $is_key . ",
                    '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_data_type"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["parent_key_match"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["key_match"]) . "',";

			if ($HTTP_POST_VARS["xsdmf_id_ref"] != "") {
              $stmt .=  Misc::escapeString($HTTP_POST_VARS["xsdmf_id_ref"]) . ",";
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

			$stmt .=  $show_in_view . ",
                    '" . Misc::escapeString($HTTP_POST_VARS["enforced_prefix"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["value_prefix"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["image_location"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["static_text"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["dynamic_text"]) . "',
                    " . $HTTP_POST_VARS["xsdmf_cvo_id"];

		if (is_numeric($HTTP_POST_VARS["xsdsel_id"])) {
			$stmt .= ", " . Misc::escapeString($HTTP_POST_VARS["xsdsel_id"]);
		}
		$stmt .= "
                 )";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insertFromArray($xdis_id, $insertArray)
    {


        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 (
                    xsdmf_xdis_id,
                    xsdmf_element,
                    xsdmf_title,
                    xsdmf_description,
                    xsdmf_html_input,
                    xsdmf_xml_order,
                    xsdmf_order,
                    xsdmf_validation_type,";
		if (!empty($insertArray["xsdmf_enabled"])) {
            $stmt .= " xsdmf_enabled,";
		}
		if (!empty($insertArray["xsdmf_indexed"])) {
            $stmt .= " xsdmf_indexed,";
		}
		if (!empty($insertArray["xsdmf_required"])) {
            $stmt .= " xsdmf_required,";
		}
		if (!empty($insertArray["xsdmf_multiple"])) {
            $stmt .= " xsdmf_multiple,";
		}
		if (!empty($insertArray["xsdmf_multiple_limit"])) {
          $stmt .= "xsdmf_multiple_limit,";
		}
		if (!empty($insertArray["xsdmf_sek_id"])) {
          $stmt .= "xsdmf_sek_id,";
		}
		if (!empty($insertArray["xsdmf_valueintag"])) {
          $stmt .= "xsdmf_valueintag,";
		}
		if (!empty($insertArray["xsdmf_is_key"])) {
          $stmt .= "xsdmf_is_key,";
		}


		$stmt .= "
                    xsdmf_parent_key_match,
                    xsdmf_key_match,";
		if (!empty($insertArray["xsdmf_id_ref"])) {
          $stmt .= "xsdmf_id_ref,";
		}
		if (!empty($insertArray["xsdmf_smarty_variable"])) {
          $stmt .= "xsdmf_smarty_variable,";
		}
		if (!empty($insertArray["xsdmf_fez_variable"])) {
          $stmt .= "xsdmf_fez_variable,";
		}
		if (!empty($insertArray["xsdmf_dynamic_selected_option"])) {
          $stmt .= "xsdmf_dynamic_selected_option,";
		}
		if (!empty($insertArray["xsdmf_selected_option"])) {
          $stmt .= "xsdmf_selected_option,";
		}
		if (!empty($insertArray["xsdmf_show_in_view"])) {
          $stmt .= "xsdmf_show_in_view,";
		}
					
		  $stmt .= "xsdmf_enforced_prefix,
					xsdmf_value_prefix,
					xsdmf_image_location,
					xsdmf_static_text,
					xsdmf_dynamic_text";
		if (is_numeric($insertArray["xsdmf_xsdsel_id"])) {
			$stmt .= ", xsdmf_xsdsel_id";
		}
		$stmt .= "
                 ) VALUES (
                    $xdis_id,
                    '" . Misc::escapeString($insertArray["xsdmf_element"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_title"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_description"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_html_input"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_xml_order"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_order"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_validation_type"]) . "',";

			if (!empty($insertArray["xsdmf_enabled"])) {
               $stmt .=$insertArray["xsdmf_enabled"] . ",";
			}

			if (!empty($insertArray["xsdmf_required"])) {
               $stmt .= $insertArray["xsdmf_required"] . ",";
			}

			if (!empty($insertArray["xsdmf_indexed"])) {
               $stmt .= $insertArray["xsdmf_indexed"] . ",";
			}
			if (!empty($insertArray["xsdmf_multiple"])) {
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

			$stmt .= "
                    '" . Misc::escapeString($insertArray["xsdmf_parent_key_match"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_key_match"]) . "',";

			if (!empty($insertArray["xsdmf_id_ref"])) {
              $stmt .=  Misc::escapeString($insertArray["xsdmf_id_ref"]) . ",";
			}
			if (!empty($insertArray["xsdmf_smarty_variable"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_smarty_variable"]) . "',";
			}
			if (!empty($insertArray["xsdmf_fez_variable"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_fez_variable"]) . "',";
			}
			if (!empty($insertArray["xsdmf_dynamic_selected_option"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_dynamic_selected_option"]) . "',";
			}
			if (!empty($insertArray["xsdmf_selected_option"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_selected_option"]) . "',";
			}
			if (!empty($insertArray["xsdmf_show_in_view"])) {
              $stmt .= $insertArray["xsdmf_show_in_view"] . ",";
			}


			$stmt .= "
                    '" . Misc::escapeString($insertArray["xsdmf_enforced_prefix"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_value_prefix"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_image_location"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_static_text"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_dynamic_text"]) . "'";

		if (is_numeric($insertArray["xsdmf_xsdsel_id"])) {
			$stmt .= ", " .$insertArray["xsdmf_xsdsel_id"];
		}
		$stmt .= "
                 )";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
			//
/*			$new_id = $GLOBALS["db_api"]->get_last_insert_id();
            if (($insertArray["xsdmf_field_type"] == 'combo') || ($insertArray["xsdmf_field_type"] == 'multiple')) {
                foreach ($insertArray["field_options"] as $option_value) {
                    $params = XSD_HTML_Match::parseParameters($option_value);
                    XSD_HTML_Match::addOptions($new_id, $params["value"]);
                }
            }*/
        }
    }

    /**
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insertFromArraySEL($xdis_id, $xsdsel_id, $insertArray)
    {


        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 (
                    xsdmf_xdis_id,
                    xsdmf_element,
                    xsdmf_title,
                    xsdmf_description,
                    xsdmf_html_input,
                    xsdmf_xml_order,
                    xsdmf_order,
                    xsdmf_validation_type,";
		if (!empty($insertArray["xsdmf_enabled"])) {
            $stmt .= " xsdmf_enabled,";
		}
		if (!empty($insertArray["xsdmf_indexed"])) {
            $stmt .= " xsdmf_indexed,";
		}
		if (!empty($insertArray["xsdmf_required"])) {
            $stmt .= " xsdmf_required,";
		}
		if (!empty($insertArray["xsdmf_multiple"])) {
            $stmt .= " xsdmf_multiple,";
		}

		if (!empty($insertArray["xsdmf_multiple_limit"])) {
          $stmt .= "xsdmf_multiple_limit,";
		}
		if (!empty($insertArray["xsdmf_sek_id"])) {
          $stmt .= "xsdmf_sek_id,";
		}
		if (!empty($insertArray["xsdmf_valueintag"])) {
          $stmt .= "xsdmf_valueintag,";
		}
		if (!empty($insertArray["xsdmf_is_key"])) {
          $stmt .= "xsdmf_is_key,";
		}


		$stmt .= "

                    xsdmf_parent_key_match,
                    xsdmf_key_match,";
		if (!empty($insertArray["xsdmf_id_ref"])) {
          $stmt .= "xsdmf_id_ref,";
		}
		if (!empty($insertArray["xsdmf_smarty_variable"])) {
          $stmt .= "xsdmf_smarty_variable,";
		}
		if (!empty($insertArray["xsdmf_fez_variable"])) {
          $stmt .= "xsdmf_fez_variable,";
		}
		if (!empty($insertArray["xsdmf_dynamic_selected_option"])) {
          $stmt .= "xsdmf_dynamic_selected_option,";
		}
		if (!empty($insertArray["xsdmf_selected_option"])) {
          $stmt .= "xsdmf_selected_option,";
		}
		if (!empty($insertArray["xsdmf_show_in_view"])) {
          $stmt .= "xsdmf_show_in_view,";
		}
					
		  $stmt .= "xsdmf_enforced_prefix,
					xsdmf_value_prefix,
					xsdmf_image_location,
					xsdmf_static_text,
					xsdmf_dynamic_text";
		if (is_numeric($xsdsel_id)) {
			$stmt .= ", xsdmf_xsdsel_id";
		}
		$stmt .= "
                 ) VALUES (
                    $xdis_id,
                    '" . Misc::escapeString($insertArray["xsdmf_element"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_title"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_description"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_html_input"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_xml_order"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_order"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_validation_type"]) . "',";

			if (!empty($insertArray["xsdmf_enabled"])) {
               $stmt .=$insertArray["xsdmf_enabled"] . ",";
			}

			if (!empty($insertArray["xsdmf_required"])) {
               $stmt .= $insertArray["xsdmf_required"] . ",";
			}

			if (!empty($insertArray["xsdmf_indexed"])) {
               $stmt .= $insertArray["xsdmf_indexed"] . ",";
			}
			if (!empty($insertArray["xsdmf_multiple"])) {
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

			$stmt .= "
                    '" . Misc::escapeString($insertArray["xsdmf_parent_key_match"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_key_match"]) . "',";

			if (!empty($insertArray["xsdmf_id_ref"])) {
              $stmt .=  Misc::escapeString($insertArray["xsdmf_id_ref"]) . ",";
			}
			if (!empty($insertArray["xsdmf_smarty_variable"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_smarty_variable"]) . "',";
			}
			if (!empty($insertArray["xsdmf_fez_variable"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_fez_variable"]) . "',";
			}
			if (!empty($insertArray["xsdmf_dynamic_selected_option"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_dynamic_selected_option"]) . "',";
			}
			if (!empty($insertArray["xsdmf_selected_option"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_selected_option"]) . "',";
			}
			if (!empty($insertArray["xsdmf_show_in_view"])) {
              $stmt .= "'" . Misc::escapeString($insertArray["xsdmf_show_in_view"]) . "',";
			}


			$stmt .= "
                    '" . Misc::escapeString($insertArray["xsdmf_enforced_prefix"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_value_prefix"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_image_location"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_static_text"]) . "',
                    '" . Misc::escapeString($insertArray["xsdmf_dynamic_text"]) . "'";

		if (is_numeric($xsdsel_id)) {
			$stmt .= ", " . $xsdsel_id;
		}
		$stmt .= "
                 )";

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
			//
/*			$new_id = $GLOBALS["db_api"]->get_last_insert_id();
            if (($insertArray["xsdmf_field_type"] == 'combo') || ($insertArray["xsdmf_field_type"] == 'multiple')) {
                foreach ($insertArray["field_options"] as $option_value) {
                    $params = XSD_HTML_Match::parseParameters($option_value);
                    XSD_HTML_Match::addOptions($new_id, $params["value"]);
                }
            }
			*/
        }
    }


    /**
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($xdis_id, $xml_element)
    {
        global $HTTP_POST_VARS;

		if (@$HTTP_POST_VARS["enabled"]) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		if (@$HTTP_POST_VARS["multiple"]) {
			$multiple = 1;
		} else {
			$multiple = 0;
		}
		if (@$HTTP_POST_VARS["required"]) {
			$required = 1;
		} else {
			$required = 0;
		}
		if (@$HTTP_POST_VARS["indexed"]) {
			$indexed= 1;
		} else {
			$indexed = 0;
		}

		if (@$HTTP_POST_VARS["valueintag"]) {
			$valueintag = 1;
		} else {
			$valueintag = 0;
		}

		if (@$HTTP_POST_VARS["show_in_view"]) {
			$show_in_view = 1;
		} else {
			$show_in_view = 0;
		}

		if (@$HTTP_POST_VARS["is_key"]) {
			$is_key = 1;
		} else {
			$is_key = 0;
		}

		if (is_numeric($HTTP_POST_VARS["xsdsel_id"])) {
			$extra_where = " AND xsdmf_xsdsel_id = ".$HTTP_POST_VARS["xsdsel_id"];
		} else {
			$extra_where = " AND xsdmf_xsdsel_id IS NULL";
		}

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 SET 
                    xsdmf_title = '" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
                    xsdmf_description = '" . Misc::escapeString($HTTP_POST_VARS["description"]) . "',
                    xsdmf_html_input = '" . Misc::escapeString($HTTP_POST_VARS["field_type"]) . "',
                    xsdmf_xml_order = " . Misc::escapeString($HTTP_POST_VARS["xml_order"]) . ",
                    xsdmf_validation_type = '" . Misc::escapeString($HTTP_POST_VARS["validation_types"]) . "',
                    xsdmf_order = " . Misc::escapeString($HTTP_POST_VARS["order"]) . ",
                    xsdmf_cvo_id = " . $HTTP_POST_VARS["xsdmf_cvo_id"] . ",					
                    xsdmf_required = " . $required . ",
                    xsdmf_indexed = " . $indexed . ",
                    xsdmf_enabled = " . $enabled . ",
                    xsdmf_multiple = " . $multiple . ",";
		if ($HTTP_POST_VARS["multiple_limit"] != "") {
        	$stmt .= " xsdmf_multiple_limit = " . Misc::escapeString($HTTP_POST_VARS["multiple_limit"]) . ",";
		}
		if (is_numeric($HTTP_POST_VARS["xsdmf_sek_id"])) {
        	$stmt .= " xsdmf_sek_id = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_sek_id"]) . ",";
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
		if (!empty($HTTP_POST_VARS["selected_option"])) {
        	$stmt .= " xsdmf_selected_option = '" . Misc::escapeString($HTTP_POST_VARS["selected_option"]) . "',";
		}


			$stmt .= "
                    xsdmf_valueintag = " . $valueintag . ",
                    xsdmf_is_key = " . $is_key . ",
                    xsdmf_show_in_view = " . $show_in_view . ",
                    xsdmf_key_match = '" . Misc::escapeString($HTTP_POST_VARS["key_match"]) . "',
                    xsdmf_parent_key_match = '" . Misc::escapeString($HTTP_POST_VARS["parent_key_match"]) . "',
                    xsdmf_data_type = '" . Misc::escapeString($HTTP_POST_VARS["xsdmf_data_type"]) . "',					
                    xsdmf_id_ref = " . Misc::escapeString($HTTP_POST_VARS["xsdmf_id_ref"]) . ",
                    xsdmf_enforced_prefix = '" . Misc::escapeString($HTTP_POST_VARS["enforced_prefix"]) . "',
                    xsdmf_value_prefix = '" . Misc::escapeString($HTTP_POST_VARS["value_prefix"]) . "',
                    xsdmf_image_location = '" . Misc::escapeString($HTTP_POST_VARS["image_location"]) . "',
                    xsdmf_dynamic_text = '" . Misc::escapeString($HTTP_POST_VARS["dynamic_text"]) . "',
                    xsdmf_static_text = '" . Misc::escapeString($HTTP_POST_VARS["static_text"]) . "'";
		$stmt .= " WHERE xsdmf_xdis_id = $xdis_id AND xsdmf_element = '".$xml_element."'".$extra_where;
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // update the custom field options, if any
            if (($HTTP_POST_VARS["field_type"] == "combo") || ($HTTP_POST_VARS["field_type"] == "multiple")) {
                $stmt = "SELECT
                            mfo_id
                         FROM
                            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                         WHERE
                            mfo_fld_id=" . $HTTP_POST_VARS['xsdmf_id'];
	            $current_options = $GLOBALS["db_api"]->dbh->getCol($stmt);
                $updated_options = array();

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
            if (in_array($HTTP_POST_VARS["field_type"], array('combo', 'multiple'))) {
                $params = XSD_HTML_Match::parseParameters($option_value);
                $diff_ids = @array_diff($current_options, $updated_options);
                if (@count($diff_ids) > 0) { 
                    XSD_HTML_Match::removeOptions($HTTP_POST_VARS['xsdmf_id'], array_values($diff_ids));
                }
            }

        }
    }


    /**
     * Method used to associate a custom field to a project.
     *
     * @access  public
     * @param   integer $prj_id The project ID
     * @param   integer $fld_id The custom field ID
     * @return  boolean
     */
    function associateCollection($prj_id, $fld_id)
    {
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                 (
                    pcf_prj_id,
                    pcf_fld_id
                 ) VALUES (
                    $prj_id,
                    $fld_id
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }




    /**
     * Method used to get the list of custom fields available in the 
     * system.
     *
     * @access  public
     * @return  array The list of custom fields
     */
    function getXSDMF_IDByElement($xsdmf_element, $xsdmf_xdis_id)
    {
		// @@@ CK 1/4/2005 - Will probably have to add xsd_id as part of the search..
		// @@@ CK 6/5/2005 - Added xsdmf_xdis_id as part of the search
        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_element = '$xsdmf_element' and xsdmf_xdis_id = $xsdmf_xdis_id and xsdmf_xsdsel_id IS NULL";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
//echo $stmt."\n";
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if (count($res) != 1) {
				return false;
			} else {
	            return $res[0][0];
			}
        }
    }

    function getXSDMF_IDByXDIS_ID($xsdmf_element, $xdis_str)
    {
		// @@@ CK 1/4/2005 - Will probably have to add xsd_id as part of the search..
        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                     xsdmf_element = '$xsdmf_element' and xsdmf_xdis_id in ($xdis_str) and (xsdmf_is_key != 1 || xsdmf_is_key is null)";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
//echo $stmt."<br />\n";
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
      * @return int Matchfield Id.
      */
    function getXSDMF_IDByKeyXDIS_ID($xsdmf_element, $element_value, $xdis_str)
    {
		// @@@ CK 1/4/2005 - Will probably have to add xsd_id as part of the search..
		// @@@ CK 1/6/2005 - Seem to have done the above at some stage. gw.

        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    ('$xsdmf_element' = xsdmf_element) and xsdmf_xdis_id in ($xdis_str) and xsdmf_is_key = 1 and ('$element_value' = xsdmf_key_match)";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
//echo $stmt."<br />\n";
//                    (INSTR('$xsdmf_element', xsdmf_element) > 0) and xsdmf_xdis_id in ($xdis_str) and xsdmf_is_key = 1 and INSTR('$element_value', xsdmf_key_match)";
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
      * Find element matchfields that have a parent key match setting.  I.e. the $parent_key is the value of
      * an ancestor element.  If our element key value matches the ancestor value then we will return a xsdmf_id.
      */
    function getXSDMF_IDByParentKeyXDIS_ID($xsdmf_element, $parent_key, $xdis_str)
    {
		// @@@ CK 1/4/2005 - Will probably have to add xsd_id as part of the search..
		// @@@ CK 1/6/2005 - Seem to have done the above at some stage. gw.

        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    '$xsdmf_element' = xsdmf_element and xsdmf_xdis_id in ($xdis_str) and xsdmf_parent_key_match = '$parent_key'";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
//echo $stmt."<br />\n";
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if (count($res) != 1) {
				return false;
			} else {
	            return $res[0][0];
			}
        }
    }

    function getXSDMF_IDByKeyXDIS_IDSEL_ID($xsdmf_element, $key_value, $xdis_str, $xsdsel_id)
    {
		// @@@ CK 1/4/2005 - Will probably have to add xsd_id as part of the search..
		// @@@ CK 1/6/2005 - Seem to have done the above at some stage. gw.

		if (!is_array($xsdsel_ids)) {
			return false;
		}

        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    ('$xsdmf_element' = xsdmf_element) and xsdmf_xdis_id in ($xdis_str) and xsdmf_is_key = 1 and ('$key_value' = xsdmf_key_match) and xsdmf_xsdsel_id = $xsdsel_id";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
//                    (INSTR('$xsdmf_element', xsdmf_element) > 0) and xsdmf_xdis_id in ($xdis_str) and xsdmf_is_key = 1 and INSTR('$key_value', xsdmf_key_match) and xsdmf_xsdsel_id = $xsdsel_id";
//echo $stmt."<br />\n";
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
     * Method used to get the list of custom fields available in the 
     * system.
     *
     * @access  public
     * @return  array The list of custom fields
     */
    function getXSDMF_IDByElementSEL_ID($xsdmf_element, $xsdsel_id, $xdis_str)
    {
		// @@@ CK 1/4/2005 - Will probably have to add xsd_id as part of the search..
		// @@@ CK 31/5/2005 - Must have done the above at some stage. gw me.
        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_element = '$xsdmf_element' and xsdmf_xdis_id in ($xdis_str) and xsdmf_xsdsel_id=".$xsdsel_id;
						
//                    xsdmf_element = '$xsdmf_element' and xsdmf_xdis_id = $xsdmf_xdis_id and xsdmf_xsdsel_id=".$xsdsel_id;
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
//echo $stmt;
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
     * Method used to get the list of custom fields available in the 
     * system.
     *
     * @access  public
     * @return  array The list of custom fields
     */
    function getXSDMF_IDByElementSEL_IDArray($xsdmf_element, $xsdsel_ids, $xsdmf_xdis_id)
    {
		// @@@ CK 1/4/2005 - Will probably have to add xsd_id as part of the search..
		// @@@ CK 31/5/2005 - Must have done the above at some stage. gw me.
		if (!is_array($xsdsel_ids)) {
			return false;
		}

        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_element = '$xsdmf_element' and xsdmf_xdis_id = $xsdmf_xdis_id and xsdmf_xsdsel_id in (".implode(",".$xsdsel_id).")";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
//echo $stmt;
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
     * Method used to get the list of custom fields available in the 
     * system.
     *
     * @access  public
     * @return  array The list of custom fields
     */
    function getListAssoc()
    {
        $stmt = "SELECT
                    xsdmf_id, xsdmf_element
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 ORDER BY
                    xsdmf_element ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
/*            for ($i = 0; $i < count($res); $i++) {
                $res[$i]["projects"] = @implode(", ", array_values(XSD_HTML_Match::getAssociatedCollections($res[$i]["fld_id"])));
                if (($res[$i]["fld_type"] == "combo") || ($res[$i]["fld_type"] == "multiple")) {
                    $res[$i]["field_options"] = @implode(", ", array_values(XSD_HTML_Match::getOptions($res[$i]["fld_id"])));
                }
            }
*/
            return $res;
        }
    }

    /**
     * Method used to get the list of custom fields available in the 
     * system.
     *
     * @access  public
     * @return  array The list of custom fields
     */
    function getXSLSource($xsl_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_xsl
                 WHERE
                    xsl_id=$xsl_id";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
/*            for ($i = 0; $i < count($res); $i++) {
                $res[$i]["projects"] = @implode(", ", array_values(XSD_HTML_Match::getAssociatedCollections($res[$i]["fld_id"])));
                if (($res[$i]["fld_type"] == "combo") || ($res[$i]["fld_type"] == "multiple")) {
                    $res[$i]["field_options"] = @implode(", ", array_values(XSD_HTML_Match::getOptions($res[$i]["fld_id"])));
                }
            }
*/
//			$res[0]['xsd_file'] = ($res[0]['xsd_file']);
            return $res;

        }
    }


    /**
     * Method used to get the list of associated projects with a given
     * custom field ID.
     *
     * @access  public
     * @param   integer $fld_id The project ID
     * @return  array The list of associated projects
     */
    function getAssociatedCollections($fld_id)
    {
        $stmt = "SELECT
                    prj_id,
                    prj_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                 WHERE
                    pcf_prj_id=prj_id AND
                    pcf_fld_id=$fld_id
                 ORDER BY
                    prj_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the details of a specific custom field.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The custom field details
     */
    function getDetails($xdis_id, $xml_element)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
                 WHERE
					 xsdmf_element='$xml_element' AND (xsdmf_xsdsel_id IS NULL) AND xsdmf_xdis_id=".$xdis_id ;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
//            $res["projects"] = @array_keys(XSD_HTML_Match::getAssociatedCollections($fld_id));
//            $t = array();
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
     * Method used to get the details of a specific custom field.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The custom field details
     */
    function getDetailsByXSDMF_ID($xsdmf_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
                 WHERE
                    xsdmf_id=".$xsdmf_id ;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
//            $res["projects"] = @array_keys(XSD_HTML_Match::getAssociatedCollections($fld_id));
//            $t = array();
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
     * Method used to get the details of a specific custom field.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The custom field details
     */
    function getDetailsSubelement($xdis_id, $xml_element, $xsdsel_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
                 WHERE
                    xsdmf_element='$xml_element' AND xsdmf_xsdsel_id = $xsdsel_id AND xsdmf_xdis_id=".$xdis_id;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
//            $res["projects"] = @array_keys(XSD_HTML_Match::getAssociatedCollections($fld_id));
//            $t = array();
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
     * Method used to get the details of a specific custom field.
     *
     * @@@ CK - 13/8/2004 - added so custom field reports could use the getGridCustomFieldReport function and get the ID from this function
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The custom field details
     */
    function getID($fld_title)
    {
        $stmt = "SELECT
                    fld_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field
                 WHERE
                    fld_title='$fld_title'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the details of a specific custom field.
     *
     * @@@ CK - 13/8/2004 - added so custom field reports could use the getGridCustomFieldReport function and get the ID from this function
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The custom field details
     */
    function getXSD_ID($xdis_id)
    {
        $stmt = "SELECT
                    xdis_xsd_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id=$xdis_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the details of a specific custom field.
     *
     * @@@ CK - 27/10/2004 - added so issue:savesearchparam would be able to loop through all possible custom fields
     *
     * @access  public
     * @return  array The custom field max fld id
     */
    function getMaxID()
    {
        $stmt = "SELECT
                    max(fld_id)
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }



    /**
     * Method used to get the list of custom field options associated
     * with a given custom field ID.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @param   array $fld_id If the fld_id is an array of fld_ids, then the fucntion will return a list
     *                        of matching options for all the fields.
     * @return  array The list of custom field options as array(mfo_id => mfo_value), 
                      or if an array was passed, array(fld_id => array(mfo_id, mfo_value))
     * @@@ CK - changed order by to mfo_value instead of mfo_id as per request in eventum issue 1647
     */
    function getOptions($fld_id)
    {
        if (is_array($fld_id)) {
            $fld_id_str = implode(',',$fld_id);
            $stmt = "SELECT
                    fld_id,
                    mfo_id,
                    mfo_value,
                FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                WHERE
                    mfo_fld_id IN ($fld_id_str)
                ORDER BY
                    mfo_value ASC";
            $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        } else {
            // @@@ CK 22/4/2005 - below change stuffed it up so changed it back
            //@@@  CK - Changed mfo_id to mfo_value as the first select value so that edit xml objects could match in select boxes
            $stmt = "SELECT
                mfo_id,
                mfo_value,
                FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                    WHERE
                    mfo_fld_id=$fld_id
                    ORDER BY
                    mfo_value ASC";
            $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
            //		echo $stmt;

        }
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        return $res;
    }

    function getOptionsValueOnly($fld_id)
    {
		// @@@ CK 22/4/2005 - below change stuffed it up so changed it back
		//@@@  CK - Changed mfo_id to mfo_value as the first select value so that edit xml objects could match in select boxes
        $stmt = "SELECT
                    mfo_value,
                    mfo_value
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                 WHERE
                    mfo_fld_id=$fld_id
                 ORDER BY
                    mfo_value ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
//		echo $stmt;

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			$res2 = array();
			foreach($res as $key => $value) {
//				$res2["'".Misc::escapeString(addslashes($key))."'"] = $value;
				$res2[utf8_encode($key)] = $value;
			}
//			print_r($res2);
            return $res;
        }
    }

    /**
     * Method used to parse the special format used in the combo boxes
     * in the administration section of the system, in order to be 
     * used as a way to flag the system for whether the custom field
     * option is a new one or one that should be updated.
     *
     * @access  private
     * @param   string $value The custom field option format string
     * @return  array Parameters used by the update/insert methods
     */
    function parseParameters($value)
    {
        if (substr($value, 0, 4) == 'new:') {
            return array(
                "type"  => "new",
                "value" => substr($value, 4)
            );
        } else {
            $value = substr($value, strlen("existing:"));
            return array(
                "type"  => "existing",
                "id"    => substr($value, 0, strpos($value, ":")),
                "value" => substr($value, strpos($value, ":")+1)
            );
        }
    }


    /**
     * Method used to update the details for a specific custom field.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
/*    function update()
    {
        global $HTTP_POST_VARS;

        if (empty($HTTP_POST_VARS["report_form"])) {
            $HTTP_POST_VARS["report_form"] = 0;
        }
        if (empty($HTTP_POST_VARS["report_form_required"])) {
            $HTTP_POST_VARS["report_form_required"] = 0;
        }
        if (empty($HTTP_POST_VARS["anon_form"])) {
            $HTTP_POST_VARS["anon_form"] = 0;
        }
        if (empty($HTTP_POST_VARS["anon_form_required"])) {
            $HTTP_POST_VARS["anon_form_required"] = 0;
        }
        $old_details = XSD_HTML_Match::getDetails($HTTP_POST_VARS["id"]);
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field
                 SET
                    fld_title='" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
                    fld_description='" . Misc::escapeString($HTTP_POST_VARS["description"]) . "',
                    fld_type='" . Misc::escapeString($HTTP_POST_VARS["field_type"]) . "',
                    fld_report_form=" . $HTTP_POST_VARS["report_form"] . ",
                    fld_report_form_required=" . $HTTP_POST_VARS["report_form_required"] . ",
                    fld_anonymous_form=" . $HTTP_POST_VARS["anon_form"] . ",
                    fld_anonymous_form_required=" . $HTTP_POST_VARS["anon_form_required"] . "
                 WHERE
                    fld_id=" . $HTTP_POST_VARS["id"];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // if the current custom field is a combo box, get all of the current options
            if (in_array($HTTP_POST_VARS["field_type"], array('combo', 'multiple'))) {
                $stmt = "SELECT
                            mfo_id
                         FROM
                            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                         WHERE
                            mfo_fld_id=" . $HTTP_POST_VARS["id"];
                $current_options = $GLOBALS["db_api"]->dbh->getCol($stmt);
            }
            // gotta remove all custom field options if the field is being changed from a combo box to a text field
            if (($old_details["fld_type"] != $HTTP_POST_VARS["field_type"]) &&
                      (!in_array($old_details['fld_type'], array('text', 'textarea'))) &&
                      (!in_array($HTTP_POST_VARS["field_type"], array('combo', 'multiple')))) {
                XSD_HTML_Match::removeOptionsByFields($HTTP_POST_VARS["id"]);
            }
            // update the custom field options, if any
            if (($HTTP_POST_VARS["field_type"] == "combo") || ($HTTP_POST_VARS["field_type"] == "multiple")) {
                $updated_options = array();
                foreach ($HTTP_POST_VARS["field_options"] as $option_value) {
                    $params = XSD_HTML_Match::parseParameters($option_value);
                    if ($params["type"] == 'new') {
                        XSD_HTML_Match::addOptions($HTTP_POST_VARS["id"], $params["value"]);
                    } else {
                        $updated_options[] = $params["id"];
                        // check if the user is trying to update the value of this option
                        if ($params["value"] != XSD_HTML_Match::getOptionValue($HTTP_POST_VARS["id"], $params["id"])) {
                            XSD_HTML_Match::updateOption($params["id"], $params["value"]);
                        }
                    }
                }
            }
            // get the diff between the current options and the ones posted by the form
            // and then remove the options not found in the form submissions
            if (in_array($HTTP_POST_VARS["field_type"], array('combo', 'multiple'))) {
                $diff_ids = @array_diff($current_options, $updated_options);
                if (@count($diff_ids) > 0) {
                    XSD_HTML_Match::removeOptions($HTTP_POST_VARS['id'], array_values($diff_ids));
                }
            }
            // now we need to check for any changes in the project association of this custom field
            // and update the mapping table accordingly
            $old_proj_ids = @array_keys(XSD_HTML_Match::getAssociatedCollections($HTTP_POST_VARS["id"]));
            // COMPAT: this next line requires PHP > 4.0.4
            $diff_ids = @array_diff($old_proj_ids, $HTTP_POST_VARS["projects"]);
            for ($i = 0; $i < count($diff_ids); $i++) {
                $fld_ids = @XSD_HTML_Match::getFieldsByCollection($diff_ids[$i]);
                if (count($fld_ids) > 0) {
                    XSD_HTML_Match::removeRecordAssociation($fld_ids);
                }
            }
            // update the project associations now
            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                     WHERE
                        pcf_fld_id=" . $HTTP_POST_VARS["id"];
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            } else {
                for ($i = 0; $i < count($HTTP_POST_VARS["projects"]); $i++) {
                    XSD_HTML_Match::associateCollection($HTTP_POST_VARS["projects"][$i], $HTTP_POST_VARS["id"]);
                }
            }
            return 1;
        }
    }
*/

    /**
     * Method used to get the list of custom fields associated with a
     * given project.
     *
     * @access  public
     * @param   integer $prj_id The project ID
     * @return  array The list of custom fields
     */
    function getFieldsByCollection($prj_id)
    {
        $stmt = "SELECT
                    pcf_fld_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                 WHERE
                    pcf_prj_id=$prj_id";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }



    /**
     * Method used to remove all custom field entries associated with 
     * a given set of issues.
     *
     * @access  public
     * @param   array $ids The array of issue IDs
     * @return  boolean
     */
    function removeByRecords($ids)
    {
		// @@@ CK - 27/10/2004 - We dont want to acidentally remove a lot of data so comment this out..

/*        $items = implode(", ", $ids);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                 WHERE
                    icf_iss_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt); 
/        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else { */
            return true;
//        }
    }



    function getElementMatchList($xdis_id)
    {
        $stmt = "SELECT 
                    xsdmf_element
                 FROM
                    ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_display_matchfields
                 WHERE
                    xsdmf_xdis_id='$xdis_id'
                    ";
                    //AND xsdmf_enabled=1";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            $ret = array();
            foreach ($res as $record) {
                $ret[] = $record['xsdmf_element'];
            }
            return $ret;
        }
    }
}




/**
 * XSD_HTML_MatchObject
 * Object for managing display fields matching against XML datastreams.
 */
class XSD_HTML_MatchObject
{
    var $gotMatchCols = false;
    var $matchCols;

    /** 
     * XSD_HTML_MatchObject
     * Instantiate object with a list of displays that relate to the main display being matched
     */
    function XSD_HTML_MatchObject($xdis_str)
    {
        $this->xdis_str = $xdis_str;
    }

    /**
     * getMatchCols
     * Retrieve the matchfields records that relate to the current display and store them locally.  This 
     * method keeps a local copy of the results to save making multiple queries for the same information.
     */
    function getMatchCols()
    {
        if (!$this->gotMatchCols) {
            // do query to get all the match cols for this display set
            $stmt = "SELECT
                   xsdmf_element, 
                   xsdmf_id,  
                   xsdmf_is_key, 
                   xsdmf_key_match, 
                   xsdmf_parent_key_match, 
                   xsdmf_xsdsel_id,
                   xsdmf_value_prefix
                FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                WHERE
                    xsdmf_xdis_id in ({$this->xdis_str})";
            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
               $this->matchCols = array();
            } else {
               $this->matchCols = $res;
            }
            $this->gotMatchCols = true;
        }
        return $this->matchCols;
    }

    /**
     * refresh
     * Make the object requery the database.  This should be called if there is expeced to be a change in the db
     */
    function refresh()
    {
        $this->gotMatchCols = false;
        $this->matchCols = array();
    }

    /**
     * getXSDMF_IDByParentKeyXDIS_ID
     * Find a match for an element that has a parent key element with the matched value.
     */
    function getXSDMF_IDByParentKeyXDIS_ID($xsdmf_element, $parent_key)
    {
        $this->getMatchCols();
        foreach ($this->matchCols as $xsdmf) {
            if (($xsdmf['xsdmf_element'] == $xsdmf_element) 
                    && ($xsdmf['xsdmf_parent_key'] == $parent_key) 
                    && !empty($xsdmf['xsdmf_id'])) {
                return $xsdmf['xsdmf_id'];
            }
        }
        return null;
    }

    /**
     * getXSDMF_IDByXDIS_ID
     * Find a match for the given element
     */
    function getXSDMF_IDByXDIS_ID($xsdmf_element)
    {
        $this->getMatchCols();
        foreach ($this->matchCols as $xsdmf) {
            if (($xsdmf['xsdmf_element'] == $xsdmf_element) 
                    && !$xsdmf['xsdmf_is_key']
                    && !empty($xsdmf['xsdmf_id'])) {
                return $xsdmf['xsdmf_id'];
            }
        }
        return null;
    }

    /**
     * getXSDMF_IDByKeyXDIS_ID 
     * Find a match field for an element that matches a key on the element value 
     */
    function getXSDMF_IDByKeyXDIS_ID($xsdmf_element, $element_value)
    {
        $this->getMatchCols();
        foreach ($this->matchCols as $xsdmf) {
            if (($xsdmf['xsdmf_element'] == $xsdmf_element) 
                    && ($xsdmf['xsdmf_key_match'] == $element_value) 
                    && $xsdmf['xsdmf_is_key']
                    && !empty($xsdmf['xsdmf_id'])) {
                return $xsdmf['xsdmf_id'];
            }
        }
        return null;
    }

    /**
     * getDetailsByXSDMF_ID 
     * Retrieve the details of a match field
     */
    function getDetailsByXSDMF_ID($xsdmf_id)
    {
        $this->getMatchCols();
        foreach ($this->matchCols as $xsdmf) {
            if ($xsdmf['xsdmf_id'] == $xsdmf_id) {
                return $xsdmf;
            }
        }
        return null;
    }
    
}


// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included XSD_HTML_Match Class');
}
?>
