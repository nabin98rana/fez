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
 * Class to handle the business logic related to the XSD to HTML Matching in the system
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
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
     * Method used to remove a group of matching field options.
     *
     * @access  public
     * @param   array $fld_id The list of matching field IDs
     * @param   array $mfo_id The list of matching field option IDs
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
     * Method used to add possible options into a given matching field.
     *
     * @access  public
     * @param   integer $fld_id The matching field ID
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
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
    function updateOption($mfo_id, $mfo_value)
    {
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
                 SET
                    mfo_value='" . Misc::escapeString($mfo_value) . "'
                 WHERE
                    mfo_id=" . $mfo_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
    function getListByDisplaySpecify($xdis_id, $specify_titles=array('FezACML')) {
        $stmt = "SELECT
                    xsdmf_id,
                    xsdmf_element,
                    xsdmf_title,
                    xsdmf_description,
                    xsdmf_long_description,
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
	// @@@ CK - Added order statement to custom fields displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order, xsdmf_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
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
    function getBasicListByDisplay($xdis_id)
    {
        $stmt = "SELECT
                    xsdmf_id,
                    xsdmf_element,
                    xsdmf_title,
                    xsdmf_description,
                    xsdmf_long_description,					
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
					xsdmf_required,					
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
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
			// Add any reference displays
			foreach ($res as $rkey => $record) {
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

    /**
     * Method used to get the list of matching fields that are not sublooping elements associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
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

    /**
     * Method used to get the list of matching fields that are sublooping elements associated with
     * a given display id and sublooping element id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @param   integer $xsdsel_id The sublooping element ID	 
     * @return  array The list of matching fields fields
     */
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
     * Method used to get the list of matching fields.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
	 function getListByDisplay($xdis_id) {
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
     * Method used to get the matching field option value.
     *
     * @access  public
     * @param   integer $fld_id The matching field ID
     * @param   integer $value The matching field option ID
     * @return  string The matching field option value
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
     * Method used to get the matching field option value by ID.
     *
     * @access  public
     * @param   integer $mfo_id The custom field ID
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
     * Method used to remove a XSD matching field.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @param   string  $xml_element The XML element
     * @return  boolean
     */
    function remove($xdis_id, $xml_element)
    {
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_xdis_id = $xdis_id AND xsdmf_element='".$xml_element."'";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }

    /**
     * Method used to remove a XSD matching fields by their XSDMF IDs.
     *
     * @access  public
     * @return  boolean
     */
    function removeByXSDMF_IDs()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
		if (@strlen($items) < 1) {
			return false;
		}
	
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_id in ($items)";

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
		  return 1;
        }
    }

    /**
     * Method used to add a new XSD matching field to the system, from form post variables.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @param   string  $xml_element The XML element
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
                    xsdmf_long_description,					
                    xsdmf_html_input,
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
                    '" . Misc::escapeString($HTTP_POST_VARS["long_description"]) . "',					
                    '" . Misc::escapeString($HTTP_POST_VARS["field_type"]) . "',
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
     * Method used to add a new XSD matching field to the system, from and array.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @param   array  $insertArray The array of values to be entered.
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
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
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
					xsdmf_dynamic_text,
					xsdmf_original_xsdmf_id";
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
                    '" . Misc::escapeString($insertArray["xsdmf_dynamic_text"]) . "',
					".$insertArray["xsdmf_id"];

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
                    xsdmf_long_description = '" . Misc::escapeString($HTTP_POST_VARS["long_description"]) . "',
                    xsdmf_html_input = '" . Misc::escapeString($HTTP_POST_VARS["field_type"]) . "',
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
     * Method used to try and get a XSDMF ID from an xsdmf_element
     *
     * @access  public
	 * @param   string $xsdmf_element The xsdmf element to search for
	 * @param   string $xsdmf_xdis_id The XSD display to search for
     * @return  array The XSDMF ID, or false if not found or more than one was found.
     */
    function getXSDMF_IDByElement($xsdmf_element, $xsdmf_xdis_id)
    {
        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_element = '$xsdmf_element' and xsdmf_xdis_id = $xsdmf_xdis_id and xsdmf_xsdsel_id IS NULL";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
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
     * Method used to try and get a XSDMF ID from an xsdmf_element
     *
     * @access  public
	 * @param   string $xsdmf_element The xsdmf element to search for
	 * @param   string $xdis_str The string comma separated list of xdis_id's to search through
     * @return  array The XSDMF ID, or false if not found or more than one was found.
     */
    function getXSDMF_IDByXDIS_ID($xsdmf_element, $xdis_str)
    {
        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                     xsdmf_element = '$xsdmf_element' and xsdmf_xdis_id in ($xdis_str) and (xsdmf_is_key != 1 || xsdmf_is_key is null)";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
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
	 *
     * @access  public
	 * @param   string $xsdmf_element The xsdmf element to search for
	 * @param   string $element_value 
	 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
     * @return  array The XSDMF ID, or false if not found or more than one was found.
     */
    function getXSDMF_IDByKeyXDIS_ID($xsdmf_element, $element_value, $xdis_str)
    {
        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    ('$xsdmf_element' = xsdmf_element) and xsdmf_xdis_id in ($xdis_str) and xsdmf_is_key = 1 and ('$element_value' = xsdmf_key_match)";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
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
     * look for key match on the attribute value - this is where the matchfield needs the 
     * attribute to be set to a certain value to match, by parent key.
	 *
     * @access  public
	 * @param   string $xsdmf_element The xsdmf element to search for
	 * @param   string $parent_key The parent key to use in the search 
	 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
     * @return  array The XSDMF ID, or false if not found or more than one was found.
     */
    function getXSDMF_IDByParentKeyXDIS_ID($xsdmf_element, $parent_key, $xdis_str)
    {

        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    '$xsdmf_element' = xsdmf_element and xsdmf_xdis_id in ($xdis_str) and xsdmf_parent_key_match = '$parent_key'";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
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
    function getXSDMF_IDByKeyXDIS_IDSEL_ID($xsdmf_element, $key_value, $xdis_str, $xsdsel_id)
    {
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
     * look for key match on the attribute value - this is where the matchfield needs the 
     * attribute to be set to a certain value to match, by element SEL ID.
	 *
     * @access  public
	 * @param   string $xsdmf_element The xsdmf element to search for
	 * @param   integer $xsdsel_id The xsdsel ID to search by.
	 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
     * @return  array The XSDMF ID, or false if not found or more than one was found.
     */
    function getXSDMF_IDByElementSEL_ID($xsdmf_element, $xsdsel_id, $xdis_str)
    {
        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_element = '$xsdmf_element' and xsdmf_xdis_id in ($xdis_str) and xsdmf_xsdsel_id=".$xsdsel_id;
						
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
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
     * look for key match on the attribute value - this is where the matchfield needs the 
     * attribute to be set to a certain value to match, by parent key.
	 *
     * @access  public
	 * @param   string $xsdmf_element The xsdmf element to search for
	 * @param   array $xsdsel_id The list of xsdsel IDs to search by.
	 * @param   string $xdis_str The string comma separated list of xdis_id's to search through	 
     * @return  array The XSDMF ID, or false if not found or more than one was found.
     */
    function getXSDMF_IDByElementSEL_IDArray($xsdmf_element, $xsdsel_ids, $xsdmf_xdis_id)
    {
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
     * getXSDMF_IDByOriginalXSDMF_ID
     * Returns the new xsdmf_id of an entry by its previous (original from a clone) xsdmf_id. Mainly used by the XSD Display clone function.
	 *
     * @access  public
	 * @param   integer $original_xsdmf_id The xsdmf id element to search for
     * @return  integer The new XSDMF ID, or false if not found or more than one was found.
     */
	function getXSDMF_IDByOriginalXSDMF_ID($original_xsdmf_id) {
		if (!is_array($original_xsdmf_id)) {
			return false;
		}
        $stmt = "SELECT
                    xsdmf_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_original_xsdmf_id = ".$original_xsdmf_id; 
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
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
     * Method used to get the list of XSD HTML Matching fields available in the 
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
     * Method used to get the details of a specific XSD HTML Matching Field, by xml element and sublooping id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @param   string $xml_element The XML element to search by.
     * @param   integer $xsdsel_id The sublooping element to search by
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
     * Method used to get the list of matching field options associated
     * with a given matching field ID.
     *
     * @access  public
     * @param   integer $fld_id The matching field ID
     * @param   array $fld_id If the fld_id is an array of fld_ids, then the fucntion will return a list
     *                        of matching options for all the fields.
     * @return  array The list of matching field options as array(mfo_id => mfo_value), 
                      or if an array was passed, array(fld_id => array(mfo_id, mfo_value))
     */
    function getOptions($fld_id)
    {
		static $mfo_returns;			
        if (!empty($mfo_returns[$fld_id])) { // check if this has already been found and set to a static variable		
			return $mfo_returns[$fld_id];
		} else {
			
			if (is_array($fld_id)) {
				$fld_id_str = implode(',',$fld_id);
				$stmt = "SELECT
						mfo_id,
						mfo_value
					FROM
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
					WHERE
						mfo_fld_id IN ($fld_id_str)
					ORDER BY
						mfo_value ASC";
				$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
			} else {
	
				$stmt = "SELECT
					mfo_id,
					mfo_value
					FROM
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_mf_option
						WHERE
						mfo_fld_id=$fld_id
						ORDER BY
						mfo_value ASC";
				$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
				if (!PEAR::isError($res)) {			
					$mfo_returns[$fld_id] = $res;
				}
			}
			if (PEAR::isError($res)) {
				Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				return array();
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
                      or if an array was passed, array(fld_id => array(mfo_id, mfo_value))
     */
    function getOptionsValueOnly($fld_id)
    {
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
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			$res2 = array();
			foreach($res as $key => $value) {
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
     * Method used to get list of XSDMF elements belonging to a XSD Display.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID to search by.
     * @return  array The list of XSDMF elements
     */
    function getElementMatchList($xdis_id)
    {
        $stmt = "SELECT 
                    xsdmf_element
                 FROM
                    ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_display_matchfields
                 WHERE
                    xsdmf_xdis_id='$xdis_id'
                    ";
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

    /**
     * Method used to get list of XSDMF elements belonging to a XSD Display, but not found in the XSD Source itself.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID to search by.
     * @return  array The list of orphaned XSDMF elements (for deletion usually).
     */
    function getElementOrphanList($xdis_id, $xsd_array)
    {
		$xsd_list = Misc::array_flatten($xsd_array);
		$xsd_list = implode("', '", $xsd_list);
        $stmt = "SELECT 
                    *
                 FROM
                    ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_display_matchfields x1 left join
					".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
                 WHERE
                    x1.xsdmf_xdis_id=$xdis_id and (x1.xsdmf_element not in ('$xsd_list') or x1.xsdmf_xsdsel_id not in (
					     SELECT distinct(s2.xsdsel_id) FROM
							 ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_loop_subelement s2 left join
							 ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_display_matchfields x2 on (x2.xsdmf_xsdsel_id = s2.xsdsel_id)
						  WHERE x2.xsdmf_xdis_id = $xdis_id
						))
                    ";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
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
    function getElementOrphanCount($xdis_id, $xsd_array)
    {
		$xsd_list = Misc::array_flatten($xsd_array);
		$xsd_list = implode("', '", $xsd_list);
        $stmt = "SELECT 
                    count(*) as orphan_count
                 FROM
                    ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_display_matchfields x1 left join
					".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
                 WHERE
                    x1.xsdmf_xdis_id=$xdis_id and (x1.xsdmf_element not in ('$xsd_list') or x1.xsdmf_xsdsel_id not in (
					     SELECT distinct(s2.xsdsel_id) FROM
							 ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_loop_subelement s2 left join
							 ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."xsd_display_matchfields x2 on (x2.xsdmf_xsdsel_id = s2.xsdsel_id)
						  WHERE x2.xsdmf_xdis_id = $xdis_id
						))
                    ";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res['orphan_count'];
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
                    && ($xsdmf['xsdmf_parent_key_match'] == $parent_key) 
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
	 *
     * @access  public
     * @param   string $xsdmf_element 
     * @param   string $element_value
     * @return  integer The xsdmf_id
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
	 *
     * @access  public
     * @param   integer $xsdmf_id 
     * @return  array The details
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
