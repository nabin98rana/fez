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
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdsel_xsdmf_id=$xsdmf_id and xsdsel_xsdmf_id = xsdmf_id and xsdmf_xdis_id = xdis_id ";
		// @@@ CK - Added order statement to sublooping elements displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
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
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                 WHERE
                    xsdsel_xsdmf_id=$xsdmf_id";
		$stmt .= " ORDER BY xsdsel_order ASC";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
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
        $stmt = "SELECT
					xsdsel_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                 WHERE
                    xsdsel_xsdmf_id=$xsdmf_id";
		// @@@ CK - Added order statement to sublooping elements displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the list of sublooping elements associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields 
     */
    function getDatastreamTitles($xdis_id)
    {

		// Get the datastream titles and xdisplay ids that are references to other display ids, and also get any binary content (file upload/select) datastreams
        $stmt = "SELECT DISTINCT
					m1.xsdmf_xdis_id,
					s1.xsdsel_title,
					s1.xsdsel_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields m1
				WHERE m1.xsdmf_xdis_id in (
					SELECT r2.xsdrel_xdis_id
					FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d2,
						 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields m2, 
						 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship r2
					WHERE m2.xsdmf_xsdsel_id = s1.xsdsel_id and m2.xsdmf_xdis_id = d2.xdis_id and m2.xsdmf_element like '!datastream!datastreamVersion!xmlContent' and r2.xsdrel_xsdmf_id = m2.xsdmf_id and m2.xsdmf_xdis_id=$xdis_id )
				OR m1.xsdmf_id in (
					SELECT m3.xsdmf_id 
					FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields m3,
						 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s3
					WHERE m3.xsdmf_xsdsel_id = s1.xsdsel_id and m3.xsdmf_element like '!datastream!datastreamVersion!contentLocation' and m3.xsdmf_xdis_id=$xdis_id 				
				)
				OR m1.xsdmf_id in (
					SELECT m3.xsdmf_id 
					FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields m3,
						 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s3
					WHERE m3.xsdmf_xsdsel_id = s1.xsdsel_id and m3.xsdmf_element like '!datastream!datastreamVersion!binaryContent' and m3.xsdmf_xdis_id=$xdis_id 				
				)";			
		// @@@ CK - Added order statement to sublooping elements displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
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

		// Get the datastream titles and xdisplay ids that are references to other display ids, and also get any binary content (file upload/select) datastreams
        $stmt = "SELECT DISTINCT
					m1.xsdmf_xdis_id,
					s1.xsdsel_title,
					s1.xsdsel_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields m1
				WHERE s1.xsdsel_title = '$dsTitle' and m1.xsdmf_xdis_id in (
					SELECT r2.xsdrel_xdis_id
					FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d2,
						 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields m2, 
						 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship r2
					WHERE m2.xsdmf_xsdsel_id = s1.xsdsel_id and m2.xsdmf_xdis_id = d2.xdis_id and m2.xsdmf_element like '!datastream!datastreamVersion!xmlContent' and r2.xsdrel_xsdmf_id = m2.xsdmf_id and m2.xsdmf_xdis_id=$xdis_id )
				OR m1.xsdmf_id in (
					SELECT m3.xsdmf_id 
					FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields m3,
						 " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s3
					WHERE m3.xsdmf_xsdsel_id = s1.xsdsel_id and m3.xsdmf_element like '!datastream!datastreamVersion!binaryContent' and m3.xsdmf_xdis_id=$xdis_id 				
				)";			
		// @@@ CK - Added order statement to sublooping elements displayed in a desired order
		$stmt .= " ORDER BY xsdsel_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
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
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdsel_xsdmf_id = xsdmf_id and xsdmf_xdis_id = $xdis_id 
					and (INSTR('$xml_element', xsdmf_element) > 0) and (xsdmf_element != '$xml_element') and xsdmf_html_input = 'xsd_loop_subelement'";
		$stmt .= " ORDER BY xsdsel_order ASC";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to remove a given list of sublooping elements, and any child matching fields under that element.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
		global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);

        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                 WHERE
                    xsdsel_id  IN (" . $items . ")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_xsdsel_id  IN (" . $items . ")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }


    /**
     * Method used to add a new sublooping element to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                 (
                    xsdsel_xsdmf_id,
                    xsdsel_title,
                    xsdsel_type,";
			if (is_numeric($HTTP_POST_VARS["xsdsel_attribute_loop_xsdmf_id"])) {
                $stmt .= "xsdsel_attribute_loop_xsdmf_id,";
			}
				$stmt .="
					xsdsel_order
                 ) VALUES (
                    " . Misc::escapeString($HTTP_POST_VARS["xsdsel_xsdmf_id"]) . ",
                    '" . Misc::escapeString($HTTP_POST_VARS["xsdsel_title"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["xsdsel_type"]) . "',";
			if (is_numeric($HTTP_POST_VARS["xsdsel_attribute_loop_xsdmf_id"])) {
               $stmt .=  Misc::escapeString($HTTP_POST_VARS["xsdsel_attribute_loop_xsdmf_id"]) . ",";
			}
               $stmt .=
                    Misc::escapeString($HTTP_POST_VARS["xsdsel_order"]) . "
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			//
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
        global $HTTP_POST_VARS;

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                 (
                    xsdsel_xsdmf_id,
                    xsdsel_title,
                    xsdsel_type,";
			if (is_numeric($insertArray["xsdsel_attribute_loop_xsdmf_id"])) {
                $stmt .= "xsdsel_attribute_loop_xsdmf_id,";
			}
				$stmt .="
					xsdsel_order
                 ) VALUES (
                    " . $xsdmf_id . ",
                    '" . Misc::escapeString($insertArray["xsdsel_title"]) . "',
                    '" . Misc::escapeString($insertArray["xsdsel_type"]) . "',";
			if (is_numeric($insertArray["xsdsel_attribute_loop_xsdmf_id"])) {
               $stmt .= Misc::escapeString($insertArray["xsdsel_attribute_loop_xsdmf_id"]).",";
			}
               $stmt .=
                    Misc::escapeString($insertArray["xsdsel_order"]) . "
                 )";
				 

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			//
        }
    }

    /**
     * Method used to update a sublooping element in the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update()
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                 SET 
                    xsdsel_title = '" . Misc::escapeString($HTTP_POST_VARS["xsdsel_title"]) . "',
                    xsdsel_order = " . Misc::escapeString($HTTP_POST_VARS["xsdsel_order"]) . ",";
				if (is_numeric($HTTP_POST_VARS["xsdsel_attribute_loop_xsdmf_id"])) {
                 	$stmt .= "   xsdsel_attribute_loop_xsdmf_id = " . Misc::escapeString($HTTP_POST_VARS["xsdsel_attribute_loop_xsdmf_id"]) . ",";
				}
					$stmt .= "
                    xsdsel_type = '" . Misc::escapeString($HTTP_POST_VARS["xsdsel_type"]) . "'
                 WHERE xsdsel_id = " . Misc::escapeString($HTTP_POST_VARS["xsdsel_id"]) . "";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }
	
    /**
     * Method used to update a sublooping element attribute loop candidate.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function updateAttributeLoopCandidate($xsdsel_id, $attribute_loop_candidate)
    {
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                 SET 
                    xsdsel_attribute_loop_xsdmf_id = ".$attribute_loop_candidate."
                 WHERE xsdsel_id = " . $xsdsel_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
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
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                 WHERE
                    xsdsel_id=".$xsdsel_id;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to check if a sublooping element has any related xsd matching fields of a given html input type
     * This is mainly used to check if a datastream is a file upload so the label and ID and possibly mimetype can be read from the uploaded file
     *
     * @access  public
     * @param   integer $xsdsel_id
     * @param   string $input_type
     * @return  boolean Whether any xsdmf's in the xsdsel where of the given type
     */
    function getXSDMFInputType($xsdsel_id, $input_type, $exclude_attrib_loops = false)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdmf_html_input = '$input_type'  AND xsdmf_xsdsel_id = xsdsel_id AND xsdsel_id=".$xsdsel_id;
		if ($exclude_attrib_loops == true) {
			$stmt .= " AND xsdmf_id <> xsdsel_attribute_loop_xsdmf_id";
		} 
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included XSD_Loop_Subelement Class');
}
?>
