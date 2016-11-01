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
    	$log = FezLog::get();
		$db = DB_API::get();

        $stmt = "SELECT
					*
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_relationship,
                    " . APP_TABLE_PREFIX . "xsd_display,
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                    xsdrel_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER')." and xsdrel_xsdmf_id = xsdmf_id and xsdrel_xdis_id = xdis_id ";
        $stmt .= " ORDER BY xsdrel_order ASC";
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
     * Method used to get the list of XSD Relationships from a given XSD matching field ID.
     *
     * @access  public
     * @param   integer $xsdmf_id
     * @return  array The list
     */
    function getSimpleListByXSDMF($xsdmf_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();

        $stmt = "SELECT
					*
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_relationship
                 WHERE
                    xsdrel_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER')." ";
		$stmt .= " ORDER BY xsdrel_order ASC";
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
     * Method used to get the list of XSD Relationships associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
    public static function getListByXDIS($xdis_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();

        $stmt = "SELECT
					xsdrel_xdis_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_relationship,
                    " . APP_TABLE_PREFIX . "xsd_display,
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                   xsdmf_xdis_id = ".$db->quote($xdis_id, 'INTEGER')." and xsdrel_xsdmf_id = xsdmf_id and xsdrel_xdis_id = xdis_id ";
		$stmt .= " ORDER BY xsdrel_order ASC";
        try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
    }

    /**
     * Method used to get the list of XSD Relationships associated with
     * a given display id.
     *
     * @access  public
     * @param   integer $xdis_id The XSD Display ID
     * @return  array The list of matching fields fields
     */
    public static function getColListByXDIS($xdis_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($xdis_id)) {
			return array();
		}
        $stmt = "SELECT
					xsdrel_xdis_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_relationship,
                    " . APP_TABLE_PREFIX . "xsd_display,
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
                 WHERE
                   xsdmf_xdis_id = ".$db->quote($xdis_id, 'INTEGER')." and xsdrel_xsdmf_id = xsdmf_id and xsdrel_xdis_id = xdis_id ";
		$stmt .= " ORDER BY xsdrel_order ASC";
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
    }


    function getColListByXDISMinimal($xdis_id, $exclude_xdis_str = '', $specify_xdis_str = '')
    {
    	$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($xdis_id)) {
			return array();
		}

        $stmt = "SELECT
					xsdrel_xdis_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd_relationship,
                    " . APP_TABLE_PREFIX . "xsd_display,
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields,
                    " . APP_TABLE_PREFIX . "xsd
                 WHERE
                   xsd_id = xdis_xsd_id AND xsdmf_xdis_id = ".$db->quote($xdis_id, 'INTEGER')." and xsdrel_xsdmf_id = xsdmf_id and xsdrel_xdis_id = xdis_id and xsdmf_html_input != 'xsdmf_id_ref'";

        if ($exclude_xdis_str != '') {
        	$stmt .= " AND xsd_title not in (".$db->quote($exclude_xdis_str).")";
        }
        if ($specify_xdis_str != '') {
          $specify_list = explode(',', $specify_xdis_str);
          if (APP_FEDORA_BYPASS == 'ON' && in_array("FezACML", $specify_list)) {
              $xdis_list = XSD_Relationship::getListByXDIS($xdis_id);
              $xdis_str = Misc::sql_array_to_string($xdis_list);
              $xdis_title = XSD_Display::getMatchingFezACMLTitle($xdis_str);
              $specify_xdis_str = ',' . $xdis_title;
          }
        	$stmt .= " AND xsd_title in (".$db->quote($specify_xdis_str).")";
        }

		$stmt .= " ORDER BY xsdrel_order ASC";

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
     * Method used to remove a given list of XSD relationships.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
    	$log = FezLog::get();
		$db = DB_API::get();

        $items = @implode(", ", $_POST["items"]);

        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "xsd_relationship
                 WHERE
                    xsdrel_id  IN (" . Misc::arrayToSQLBindStr($_POST["items"]) . ")";
		try {
			$db->query($stmt, $_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
    }


    /**
     * Method used to add a new XSD relationship to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
    	$log = FezLog::get();
		$db = DB_API::get();

        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "xsd_relationship
                 (
                    xsdrel_xsdmf_id,
                    xsdrel_xdis_id,
					xsdrel_order
                 ) VALUES (
                    " . $db->quote($_POST["xsdrel_xsdmf_id"]) . ",
                    " . $db->quote($_POST["xsd_display_id"]) . ",
                    " . $db->quote($_POST["xsdrel_order"]) . "
                 )";
    	try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
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
    	$log = FezLog::get();
		$db = DB_API::get();

        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "xsd_relationship
                 (
                    xsdrel_xsdmf_id,
                    xsdrel_xdis_id,
					xsdrel_order
                 ) VALUES (
                    " . $db->quote($xsdmf_id, 'INTEGER') . ",
                    " . $db->quote($insertArray["xsdrel_xdis_id"], 'INTEGER') . ",
                    " . $db->quote($insertArray["xsdrel_order"], 'INTEGER').
                 ")";
        try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return $db->lastInsertId(APP_TABLE_PREFIX . "xsd_relationship", "xsdrel_id");
    }


    /**
     * Method used to update a XSD relationship in the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($xsdrel_id='',$params=array())
    {
    	$log = FezLog::get();
		$db = DB_API::get();

        if (empty($params)) {
        	$params = &$_POST;
        }
        if (empty($xsdrel_id)) {
        	$xsdrel_id = $params["xsdrel_id"];
        }
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "xsd_relationship
                 SET 
                    xsdrel_xsdmf_id = " . $db->quote($params["xsdrel_xsdmf_id"], 'INTEGER') . ",
                    xsdrel_xdis_id = " . $db->quote($params["xsdrel_xdis_id"], 'INTEGER') . ",
                    xsdrel_order = " . $db->quote($params["xsdrel_order"], 'INTEGER') . "
                 WHERE xsdrel_id = " . $db->quote($xsdrel_id, 'INTEGER');
    	try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
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
    	$log = FezLog::get();
		$db = DB_API::get();

        if (empty($maps['xsdrel_map'])) {
            return;
        }
        foreach ($maps['xsdrel_map'] as $xsdrel_id) {
            $stmt = "SELECT * FROM ". APP_SQL_DBNAME . "." . APP_TABLE_PREFIX ."xsd_relationship " .
                    "WHERE xsdrel_id=".$db->quote($xsdrel_id, 'INTEGER');

			try {
				$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return false;
			}
            Misc::arraySearchReplace($res,
                    array('xsdrel_xdis_id'),
                    $maps['xdis_map']);
            		XSD_Relationship::update($xsdrel_id, $res);
        }
    }
}
