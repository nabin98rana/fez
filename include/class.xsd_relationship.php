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
 * of XSD relationships in the system.
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



class XSD_Relationship
{
    /**
     * Method used to get the list of XSD Matching Fields and XSD Relationships from a given XSD matching field ID.
     *
     * @access  public
     * @param   integer $xsdmf_id
     * @return  array The list 
     */
    function getListByXSDMF($xsdmf_id)
    {
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdrel_xsdmf_id=$xsdmf_id and xsdrel_xsdmf_id = xsdmf_id and xsdrel_xdis_id = xdis_id ";
		$stmt .= " ORDER BY xsdrel_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of XSD Relationships from a given XSD matching field ID.
     *
     * @access  public
     * @param   integer $xsdmf_id
     * @return  array The list 
     */
    function getSimpleListByXSDMF($xsdmf_id)
    {
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship
                 WHERE
                    xsdrel_xsdmf_id=$xsdmf_id ";
		$stmt .= " ORDER BY xsdrel_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of XSD Relationships associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
    function getListByXDIS($xdis_id)
    {
        $stmt = "SELECT
					xsdrel_xdis_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                   xsdmf_xdis_id = $xdis_id and xsdrel_xsdmf_id = xsdmf_id and xsdrel_xdis_id = xdis_id ";
		$stmt .= " ORDER BY xsdrel_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of XSD Relationships associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
    function getColListByXDIS($xdis_id)
    {
		if (!is_numeric($xdis_id)) {
			return array();
		}
        $stmt = "SELECT
					xsdrel_xdis_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                   xsdmf_xdis_id = $xdis_id and xsdrel_xsdmf_id = xsdmf_id and xsdrel_xdis_id = xdis_id ";
		$stmt .= " ORDER BY xsdrel_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    /**
     * Method used to remove a given list of XSD relationships.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
		global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);

        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship
                 WHERE
                    xsdrel_id  IN (" . $items . ")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {

		  return true;
        }
    }


    /**
     * Method used to add a new XSD relationship to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship
                 (
                    xsdrel_xsdmf_id,
                    xsdrel_xdis_id,
					xsdrel_order
                 ) VALUES (
                    " . Misc::escapeString($HTTP_POST_VARS["xsdrel_xsdmf_id"]) . ",
                    " . Misc::escapeString($HTTP_POST_VARS["xsd_display_id"]) . ",
                    " . Misc::escapeString($HTTP_POST_VARS["xsdrel_order"]) . "
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
     * Method used to add a new XSD relationship to the system from a given array.
     *
     * @access  public
	 * @param   integer $xsdmf_id 
	 * @param   array   $insertArray The array containing the values to be inserted into the XSD relationship
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insertFromArray($xsdmf_id, $insertArray)
    {
        global $HTTP_POST_VARS;

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship
                 (
                    xsdrel_xsdmf_id,
                    xsdrel_xdis_id,
					xsdrel_order
                 ) VALUES (
                    " . $xsdmf_id . ",
                    " . $insertArray["xsdrel_xdis_id"] . ",
                    " . $insertArray["xsdrel_order"] . "
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return $GLOBALS['db_api']->get_last_insert_id();
        }
    }


    /**
     * Method used to update a XSD relationship in the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update()
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship
                 SET 
                    xsdrel_xsdmf_id = " . Misc::escapeString($HTTP_POST_VARS["xsdrel_xsdmf_id"]) . ",
                    xsdrel_xsd_id = " . Misc::escapeString($HTTP_POST_VARS["xsdrel_xsd_id"]) . ",
                    xsdrel_order = " . Misc::escapeString($HTTP_POST_VARS["xsdrel_order"]) . "
                 WHERE xsdrel_id = " . Misc::escapeString($HTTP_POST_VARS["xsdrel_id"]) . "";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }
    
    function importRels($xmatch, $xsdmf_id, &$maps)
    {
        $xpath = new DOMXPath($xmatch->ownerDocument);
        $xrels = $xpath->query('relationship', $xmatch);
        foreach ($xrels as $xrel) {
            $params = array(
                'xsdrel_xsdmf_id' => $xsdmf_id,
                'xsdrel_xdis_id' => $xrel->getAttribute('xsdrel_xdis_id'),
                'xsdrel_order' => $xrel->getAttribute('xsdrel_order'),
            );
            $xsdrel_id = XSD_Relationship::insertFromArray($xsdmf_id, $params);
            $maps['xsdrel_map'][$xrel->getAttribute('xsdrel_id')] = $xsdrel_id;
        }
    }
    
    function remapImport(&$maps)
    {
        if (empty($maps['xsdrel_map'])) {
            return;
        }    
        // find all the stuff that references the new displays
        $xsdrel_ids = array_values($maps['xsdrel_map']);
        $xsdrel_ids_str = Misc::arrayToSQL($xsdrel_ids);
        Misc::tableSearchAndReplace('xsd_relationship',
            array('xsdrel_xdis_id'),
            $maps['xdis_map'], " xsdrel_id IN ($xsdrel_ids_str)");
    }
}
// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included XSD_Relationship Class');
}
?>
