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
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");



class XSD_Display
{
    /**
     * Method used to remove a group of custom field options.
     *
     * @access  public
     * @param   array $fld_id The list of custom field IDs
     * @param   array $fld_id The list of custom field option IDs
     * @return  boolean
     */
    function removeOptions($fld_id, $cfo_id)
    {
        if (!is_array($fld_id)) {
            $fld_id = array($fld_id);
        }
        if (!is_array($cfo_id)) {
            $cfo_id = array($cfo_id);
        }
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option
                 WHERE
                    cfo_id IN (" . implode(",", $cfo_id) . ")";
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
                        icf_value IN (" . implode(", ", $cfo_id) . ")";
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
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option
                     (
                        cfo_fld_id,
                        cfo_value
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
     * Method used to update an existing custom field option value.
     *
     * @access  public
     * @param   integer $cfo_id The custom field option ID
     * @param   string $cfo_value The custom field option value
     * @return  boolean
     */
    function updateOption($cfo_id, $cfo_value)
    {
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option
                 SET
                    cfo_value='" . Misc::escapeString($cfo_value) . "'
                 WHERE
                    cfo_id=" . $cfo_id;
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
                XSD_XSL_Transform::removeRecordAssociation($fld_id, $HTTP_POST_VARS["issue_id"]);
                if (@count($value) > 0) {
                    XSD_XSL_Transform::associateRecord($HTTP_POST_VARS["issue_id"], $fld_id, $value);
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
                    $res[$i]["field_options"] = XSD_XSL_Transform::getOptions($res[$i]["fld_id"]);
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
                    cfo_value
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option
                 WHERE
                    cfo_fld_id=$fld_id AND
                    cfo_id=$value";
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
                        $res[$i]["selected_cfo_id"] = $res[$i]["icf_value"];
                        $res[$i]["icf_value"] = XSD_XSL_Transform::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                        $res[$i]["field_options"] = XSD_XSL_Transform::getOptions($res[$i]["fld_id"]);
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
                            $res[$i]["selected_cfo_id"] = array($res[$i]["icf_value"]);
                            $res[$i]["icf_value"] = XSD_XSL_Transform::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                            $res[$i]["field_options"] = XSD_XSL_Transform::getOptions($res[$i]["fld_id"]);
                            $fields[] = $res[$i];
                        } else {
                            $fields[$found_index]['icf_value'] .= ', ' . XSD_XSL_Transform::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                            $fields[$found_index]['selected_cfo_id'][] = $res[$i]["icf_value"];
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
                        $res[$i]["selected_cfo_id"] = $res[$i]["icf_value"];
                        $res[$i]["icf_value"] = XSD_XSL_Transform::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                        $res[$i]["field_options"] = XSD_XSL_Transform::getOptions($res[$i]["fld_id"]);
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
                            $res[$i]["selected_cfo_id"] = array($res[$i]["icf_value"]);
                            $res[$i]["icf_value"] = XSD_XSL_Transform::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                            $res[$i]["field_options"] = XSD_XSL_Transform::getOptions($res[$i]["fld_id"]);
                            $fields[] = $res[$i];
                        } else {
                            $fields[$found_index]['icf_value'] .= ', ' . XSD_XSL_Transform::getOptionValue($res[$i]["fld_id"], $res[$i]["icf_value"]);
                            $fields[$found_index]['selected_cfo_id'][] = $res[$i]["icf_value"];
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
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id IN ($items)";
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
                                " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option
                             WHERE
                                cfo_fld_id IN ($items)";
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
    function insert($xsd_id)
    {
        global $HTTP_POST_VARS;

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 (
                    xdis_title,
                    xdis_xsd_id,
                    xdis_version
                 ) VALUES (
                    '" . Misc::escapeString($HTTP_POST_VARS["xdis_title"]) . "',
                    $xsd_id,
                    '" . Misc::escapeString($HTTP_POST_VARS["xdis_version"]) . "'
                 )";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			//
        }
    }

    /**
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($xdis_id)
    {
//		echo $HTTP_POST_VARS["xsd_source"];
        global $HTTP_POST_VARS;
		
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 SET 
                    xdis_title = '" . Misc::escapeString($HTTP_POST_VARS["xdis_title"]) . "',
                    xdis_version = '" . Misc::escapeString($HTTP_POST_VARS["xdis_version"]) . "',
                 WHERE xdis_id = $xdis_id";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			//
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
    function getList($xsd_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_xsd_id = $xsd_id
                 ORDER BY
                    xdis_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
//		echo $stmt;
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
/*            for ($i = 0; $i < count($res); $i++) {
                $res[$i]["projects"] = @implode(", ", array_values(XSD_XSL_Transform::getAssociatedCollections($res[$i]["fld_id"])));
                if (($res[$i]["fld_type"] == "combo") || ($res[$i]["fld_type"] == "multiple")) {
                    $res[$i]["field_options"] = @implode(", ", array_values(XSD_XSL_Transform::getOptions($res[$i]["fld_id"])));
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
    function getAssocList()
    {
        $stmt = "SELECT
                    xdis_id,
					concat(xdis_title, ' Version ', xdis_version) as xdis_desc
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 ORDER BY
                    xdis_title, xdis_version ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
//		echo $stmt;
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
/*            for ($i = 0; $i < count($res); $i++) {
                $res[$i]["projects"] = @implode(", ", array_values(XSD_XSL_Transform::getAssociatedCollections($res[$i]["fld_id"])));
                if (($res[$i]["fld_type"] == "combo") || ($res[$i]["fld_type"] == "multiple")) {
                    $res[$i]["field_options"] = @implode(", ", array_values(XSD_XSL_Transform::getOptions($res[$i]["fld_id"])));
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
                $res[$i]["projects"] = @implode(", ", array_values(XSD_XSL_Transform::getAssociatedCollections($res[$i]["fld_id"])));
                if (($res[$i]["fld_type"] == "combo") || ($res[$i]["fld_type"] == "multiple")) {
                    $res[$i]["field_options"] = @implode(", ", array_values(XSD_XSL_Transform::getOptions($res[$i]["fld_id"])));
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
    function getDetails($fld_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field
                 WHERE
                    fld_id=$fld_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            $res["projects"] = @array_keys(XSD_XSL_Transform::getAssociatedCollections($fld_id));
            $t = array();
            $options = XSD_XSL_Transform::getOptions($fld_id);
            foreach ($options as $cfo_id => $cfo_value) {
                $res["field_options"]["existing:" . $cfo_id . ":" . $cfo_value] = $cfo_value;
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
    function getParentXSDID($xdis_id)
    {
        $stmt = "SELECT
                    xdis_xsd_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id=$xdis_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
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
    function getIDs($xdis_titles)
    {
        $stmt = "SELECT
                   xdis_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_title in ('".implode("','", $xdis_titles)."')";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
//		echo $stmt;
//		print_r($res);
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
     * @return  array The list of custom field options
     * @@@ CK - changed order by to cfo_value instead of cfo_id as per request in eventum issue 1647
     */
    function getOptions($fld_id)
    {
        $stmt = "SELECT
                    cfo_id,
                    cfo_value
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option
                 WHERE
                    cfo_fld_id=$fld_id
                 ORDER BY
                    cfo_value ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
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
        $old_details = XSD_XSL_Transform::getDetails($HTTP_POST_VARS["id"]);
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
                            cfo_id
                         FROM
                            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option
                         WHERE
                            cfo_fld_id=" . $HTTP_POST_VARS["id"];
                $current_options = $GLOBALS["db_api"]->dbh->getCol($stmt);
            }
            // gotta remove all custom field options if the field is being changed from a combo box to a text field
            if (($old_details["fld_type"] != $HTTP_POST_VARS["field_type"]) &&
                      (!in_array($old_details['fld_type'], array('text', 'textarea'))) &&
                      (!in_array($HTTP_POST_VARS["field_type"], array('combo', 'multiple')))) {
                XSD_XSL_Transform::removeOptionsByFields($HTTP_POST_VARS["id"]);
            }
            // update the custom field options, if any
            if (($HTTP_POST_VARS["field_type"] == "combo") || ($HTTP_POST_VARS["field_type"] == "multiple")) {
                $updated_options = array();
                foreach ($HTTP_POST_VARS["field_options"] as $option_value) {
                    $params = XSD_XSL_Transform::parseParameters($option_value);
                    if ($params["type"] == 'new') {
                        XSD_XSL_Transform::addOptions($HTTP_POST_VARS["id"], $params["value"]);
                    } else {
                        $updated_options[] = $params["id"];
                        // check if the user is trying to update the value of this option
                        if ($params["value"] != XSD_XSL_Transform::getOptionValue($HTTP_POST_VARS["id"], $params["id"])) {
                            XSD_XSL_Transform::updateOption($params["id"], $params["value"]);
                        }
                    }
                }
            }
            // get the diff between the current options and the ones posted by the form
            // and then remove the options not found in the form submissions
            if (in_array($HTTP_POST_VARS["field_type"], array('combo', 'multiple'))) {
                $diff_ids = @array_diff($current_options, $updated_options);
                if (@count($diff_ids) > 0) {
                    XSD_XSL_Transform::removeOptions($HTTP_POST_VARS['id'], array_values($diff_ids));
                }
            }
            // now we need to check for any changes in the project association of this custom field
            // and update the mapping table accordingly
            $old_proj_ids = @array_keys(XSD_XSL_Transform::getAssociatedCollections($HTTP_POST_VARS["id"]));
            // COMPAT: this next line requires PHP > 4.0.4
            $diff_ids = @array_diff($old_proj_ids, $HTTP_POST_VARS["projects"]);
            for ($i = 0; $i < count($diff_ids); $i++) {
                $fld_ids = @XSD_XSL_Transform::getFieldsByCollection($diff_ids[$i]);
                if (count($fld_ids) > 0) {
                    XSD_XSL_Transform::removeRecordAssociation($fld_ids);
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
                    XSD_XSL_Transform::associateCollection($HTTP_POST_VARS["projects"][$i], $HTTP_POST_VARS["id"]);
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
     * Method used to remove the issue associations related to a given
     * custom field ID.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @param   integer $issue_id The issue ID (not required)
     * @return  boolean
     */
    function removeRecordAssociation($fld_id, $issue_id = FALSE)
    {
        if (is_array($fld_id)) {
            $fld_id = implode(", ", $fld_id);
        }
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "issue_custom_field
                 WHERE
                    icf_fld_id IN ($fld_id)";
        if ($issue_id) {
            $stmt .= " AND icf_iss_id=$issue_id";
        }
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method used to remove the custom field options associated with
     * a given list of custom field IDs.
     *
     * @access  public
     * @param   array $ids The list of custom field IDs
     * @return  boolean
     */
    function removeOptionsByFields($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $items = implode(", ", $ids);
        $stmt = "SELECT
                    cfo_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option
                 WHERE
                    cfo_fld_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            XSD_XSL_Transform::removeOptions($ids, $res);
            return true;
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


    /**
     * Method used to remove all custom fields associated with 
     * a given set of projects.
     *
     * @access  public
     * @param   array $ids The array of project IDs
     * @return  boolean
     */
    function removeByCollections($ids)
    {
        $items = implode(", ", $ids);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "project_custom_field
                 WHERE
                    pcf_prj_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }
}

class XSD_DisplayObject
{
    var $xdis_id;
    var $matchfields;
    var $retrieved_mf = false;

    function XSD_DisplayObject($xdis_id)
    {
        $this->xdis_id = $xdis_id;
    }

    function refresh()
    {
        $this->retrieved_mf = false;
    }

    function getMatchFieldsList()
    {
        if ($this->retrieved_mf) {
            return $this->matchfields;
        }
        $res = XSD_HTML_Match::getBasicListByDisplay($this->xdis_id);


        if (count($res) > 0) {
            // make a list of mf_ids so we can get the options in one query
            foreach ($res as $r) {
                $xsdmf_ids[] = $r['xsdmf_id'];
            }
            $xsdmf_options = XSD_HTML_Match::getOptions($xsdmf_ids);
            // reformat the options for smarty
            foreach ($res as &$r) {
                if (isset($xsdmf_options[$r['xsdmf_id']][0])) {
                    $r["field_options"] 
                        = array($xsdmf_options[$r['xsdmf_id']][0] => $xsdmf_options[$r['xsdmf_id']][1]);
                    $r["field_options_value_only"] 
                        = array($xsdmf_options[$r['xsdmf_id']][1] => $xsdmf_options[$r['xsdmf_id']][1]);
                }
            }
        }
        $this->retrieved_mf = true;
        $this->matchfields = $res;
        return $res;
    }

    function getXsdAsReferencedArray()
    {
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
        return $array_ptr;
    }

    function getDatastreamTitles()
    {
		return XSD_Loop_Subelement::getDatastreamTitles($this->xdis_id);
    }

    function clearXSDMF_Values()
    {
        $this->xsdmf_array = array();
    }

    function getXSDMF_Values()
    {
        return $this->xsdmf_array;
    }

    function processXSDMF($pid) 
    {
        $this->clearXSDMF_Values();
        // Find datastreams that may be used by this display
		$datastreamTitles = $this->getDatastreamTitles();
		foreach ($datastreamTitles as $dsValue) {
            // find out if this record has the datastream 
			$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsValue['xsdsel_title']);
            if (isset($DSResultArray['stream'])) {
                $xmlDatastream = $DSResultArray['stream'];
                // get the matchfields for the datastream (using the sub-display for this stream)
                $this->processXSDMFDatastream($xmlDatastream, $dsValue['xsdmf_xdis_id']);
            }
		}
    }

    /**
      * processXSDMFDatastream
      * Find values for all the matchfields on a given Datastream and xdis_id
      */
    function processXSDMFDatastream($xmlDatastream, $xsdmf_xdis_id) 
    {
        $xsd_id = XSD_Display::getParentXSDID($xsdmf_xdis_id);
        $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
        $this->xsd_element_prefix = $xsd_details['xsd_element_prefix'];
        $this->xsd_top_element_name = $xsd_details['xsd_top_element_name'];

        $xmlnode = new DomDocument();
        $xmlnode->loadXML($xmlDatastream);

        $xdis_list = XSD_Relationship::getListByXDIS($this->xdis_id);
        array_push($xdis_list, array("0" => $this->xdis_id));
        $xdis_str = Misc::sql_array_to_string($xdis_list);
        $this->xsd_html_match = new XSD_HTML_MatchObject($xdis_str);
        $cbdata = array('parentContent' => '', 'parent_key' => '');
        $this->mfcb_rootdone = false;
        Misc::XML_Walk($xmlnode, $this, 'matchFieldsCallback', $cbdata);
    }

    /**
      * matchFieldsCallback
      * Used by XML_Walk to recurse through an xsd and work out the match fields.
      *
      * @param array $cbdata - data that is passed to each callback but is part of the recursive data - i.e. it is 
      * not remembered when recursing out.  The record object itself stores data that should persist while recursing.
      */
    function matchFieldsCallback($domNode, $cbdata, $context=null)
    {
        $clean_nodeName = Misc::strip_element_name($domNode->nodeName);
        $xsdmf_ptr = &$this->xsdmf_array;
        $xsdmf_id = null;
        // look for the xsdmf_id
        switch ($domNode->nodeType)
        {
            case XML_ELEMENT_NODE:
                switch ($context) {
                    case 'startopen':
                        // this is processed before we have walked the attributes for this element
                        // Store the current node name for use when called back for the attributes.
                        $cbdata['clean_nodeName'] = $clean_nodeName;
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
                                // if there are passed parent keys then use them in the search
                                $xsdmf_id = $this->xsd_html_match->getXSDMF_IDByParentKeyXDIS_ID($new_element, 
                                        $cbdata['parent_key']);		
                            } else {
                                $xsdmf_id = $this->xsd_html_match->getXSDMF_IDByXDIS_ID($new_element);
                            }
                        }

                        break;
                    case 'close':
                        // this is processed after have walked the attributes and children for this element
                        break;
                }
                break;
            case XML_ATTRIBUTE_NODE:
                $new_element = "!{$cbdata['parentContent']}!{$cbdata['clean_nodeName']}!$clean_nodeName";
                // Is there a match field for this attribute?
                // look for key match on the attribute value first - this is where the matchfield needs the 
                // attribute to be set to a certain value to match.
                $xsdmf_id = $this->xsd_html_match->getXSDMF_IDByKeyXDIS_ID($new_element, $domNode->nodeValue); 
                if (empty($xsdmf_id)) {
                    // look for a straight attribute match
                    $xsdmf_id = $this->xsd_html_match->getXSDMF_IDByXDIS_ID($new_element);
                }
                break;
            default:
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

        if (($domNode->nodeType == XML_ELEMENT_NODE) && ($context == 'endopen')) {

            // Store the parent key for key match fields.
            //
            if (!empty($xsdmf_details)) {
                if (($xsdmf_details['xsdmf_is_key'] == 1) && ($xsdmf_details['xsdmf_key_match'] != '')) {
                    $cbdata['parent_key'] = $xsdmf_details['xsdmf_key_match'];
                }
            }

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

}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included XSD_XSL_Transform Class');
}
?>
