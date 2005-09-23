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
include_once(APP_INC_PATH . "class.xsd_relationship.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");



class XSD_Display
{

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
            // also remove any xsdmf's, sels and relationships that are connected to this display
            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                     WHERE
                        xsdsel_xsdmf_id IN ( SELECT xsdmf_id FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_xdis_id IN (" . $items . "))";

            $GLOBALS["db_api"]->dbh->query($stmt);
            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship
                     WHERE
                        xsdrel_xsdmf_id IN ( SELECT xsdmf_id FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_xdis_id IN (" . $items . "))";

            $GLOBALS["db_api"]->dbh->query($stmt);

            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                     WHERE
                        xsdmf_xdis_id IN (" . $items . ")";
            $GLOBALS["db_api"]->dbh->query($stmt);

		  return true;
        }
    }

    /**
     * Method used to clone an existing display in the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function cloneDisplay($xdis_id) {
        global $HTTP_POST_VARS;

		$master_res = XSD_Display::getDetails($xdis_id);

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 (
                    xdis_title,
                    xdis_xsd_id,
                    xdis_version
                 ) VALUES (
                    'Clone of " .$master_res["xdis_title"] . "',
                    " .$master_res["xdis_xsd_id"] . ",
                    '1.0'
                 )";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
		    $new_xdis_id = $GLOBALS["db_api"]->get_last_insert_id();
			 // get a list of all the non-sel-child elements (where xsdmf_xsdsel_id = null)
			 $xsdmf_res = XSD_HTML_Match::getNonSELChildListByDisplay($xdis_id);
			 foreach ($xsdmf_res as $xsdmf_row) {
				// insert the record
				XSD_HTML_Match::insertFromArray($new_xdis_id, $xsdmf_row);
				// get the new xsdmf_id
				$new_xsdmf_id = $GLOBALS["db_api"]->get_last_insert_id();
				// get the sels for the current row
				$xsd_sel_res = XSD_Loop_Subelement::getSimpleListByXSDMF($xsdmf_row['xsdmf_id']);
				// is the xsdmf a parent in the xsd_loop_subelement table? if so then create a clone entry for its sel entry
				if (count($xsd_sel_res) > 0) {
					foreach ($xsd_sel_res as $xsd_sel_row) {
						XSD_Loop_Subelement::insertFromArray($new_xsdmf_id, $xsd_sel_row);
						$new_sel_id = $GLOBALS["db_api"]->get_last_insert_id();
						$child_xsdmf_sel_res = XSD_HTML_Match::getSELChildListByDisplay($xdis_id, $xsd_sel_row['xsdsel_id']);
						// does the clone parent SEL record have any child sel elements? if so then insert clones for those too
						foreach ($child_xsdmf_sel_res as $child_xsdmf_sel_row) {
							XSD_HTML_Match::insertFromArraySEL($new_xdis_id, $new_sel_id, $child_xsdmf_sel_row);
							$new_child_xsdmf_id = $GLOBALS["db_api"]->get_last_insert_id();
							// do any of the children have xsd relationships? if so then insert them
							$xsdrel_res = XSD_Relationship::getSimpleListByXSDMF($child_xsdmf_sel_row['xsdmf_id']);
							foreach ($xsdrel_res as $xsdrel_row) {
								XSD_Relationship::insertFromArray($new_child_xsdmf_id, $xsdrel_row);
							}
						}
					}						
				}
				// does the clone parent SEL have any xsd relationships? if so insert them
				$xsdrel_res = XSD_Relationship::getSimpleListByXSDMF($xsdmf_row['xsdmf_id']);				
				foreach ($xsdrel_res as $xsdrel_row) {
					XSD_Relationship::insertFromArray($new_xsdmf_id, $xsdrel_row);
				}
			 }
			 return 1; 
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
                    xdis_version = '" . Misc::escapeString($HTTP_POST_VARS["xdis_version"]) . "'
                 WHERE xdis_id = $xdis_id";

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
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
    function getAssocListDocTypes()
    {
        $stmt = "SELECT
                    xdis_id,
					concat(xdis_title, ' Version ', xdis_version) as xdis_desc
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
				 WHERE xdis_is_doc_type = 1				 
                 ORDER BY
                    xdis_title, xdis_version ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
//		echo $stmt;
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
                    max(xdis_id)
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display";
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
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The custom field details
     */
    function getDetails($xdis_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id=$xdis_id";

        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
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

}

/**
 * XSD_DisplayObject
 * Manage access to the display tables in the database.
 */
class XSD_DisplayObject
{
    var $xdis_id;
    var $matchfields;
    var $retrieved_mf = false;

    /**
     * XSD_DisplayObject
     * Instantiate with a display id
     */
    function XSD_DisplayObject($xdis_id)
    {
        $this->xdis_id = $xdis_id;
    }

    /**
     * refresh
     * Clear the local copies of query results.  Use to make the object requery the database if it has changed.
     */
    function refresh()
    {
        $this->retrieved_mf = false;
        $this->xsdmf_array = array();
    }

    /**
     * getMatchFieldsList
     * Get the list of fields that can be matched for this display.
     */ 
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

    /**
     * getXsdAsReferencedArray
     * Converts an XSD specification file to an array  
     */
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
        return array($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema);
    }

    /**
     * getDatastreamTitles
     * Get the datastreams that are used with this display.
     */ 
    function getDatastreamTitles()
    {
		return XSD_Loop_Subelement::getDatastreamTitles($this->xdis_id);
    }

    /**
     * getXSDMF_Values
     * Return a list of match fields with the values from the datastream for the record with the
     * given pid.
     */  
    function getXSDMF_Values($pid)
    {
        $this->processXSDMF($pid); 
        return $this->xsdmf_array[$pid];
    }

    /**
     * processXSDMF
     * Get the values from elements in the datastreams that match against the match fields
     * for this display
     */ 
    function processXSDMF($pid) 
    {
        if (!isset($this->xsdmf_array[$pid])) {
            $this->xsdmf_array[$pid] = array();
            $this->xsdmf_current = &$this->xsdmf_array[$pid];
            // Find datastreams that may be used by this display
            $datastreamTitles = $this->getDatastreamTitles();
            $xdis_list = XSD_Relationship::getListByXDIS($this->xdis_id);
            array_push($xdis_list, array("0" => $this->xdis_id));
            $xdis_str = Misc::sql_array_to_string($xdis_list);
            $this->xsd_html_match = new XSD_HTML_MatchObject($xdis_str);
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
        @$xmlnode->loadXML($xmlDatastream);

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
        $xsdmf_ptr = &$this->xsdmf_current;
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
