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
 * Class to handle document type to XSD matchings.
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


class Doc_Type_XSD
{

    /**
     * Method used to remove a given list of Document Type XSDs.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd
                 WHERE
                    xsd_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }


    /**
     * Method used to add a new Document Type XSD to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS, $HTTP_POST_FILES;

        $files = array();
        for ($i = 0; $i < count($HTTP_POST_FILES["xsd_file"]); $i++) {
            $filename = @$HTTP_POST_FILES["xsd_file"]["name"][$i];
            if (empty($filename)) {
                continue;
            }
            $blob = Misc::getFileContents($HTTP_POST_FILES["xsd_file"]["tmp_name"][$i]);
            $files[] = array(
                "filename"  =>  $filename,
                "type"      =>  $HTTP_POST_FILES['xsd_file']['type'][$i],
                "blob"      =>  $blob
            );
        }
		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd
                 (
                    xsd_title,
                    xsd_version,
                    xsd_top_element_name,
                    xsd_element_prefix,
                    xsd_extra_ns_prefixes,
                    xsd_file
                 ) VALUES (
                    '" . Misc::escapeString($HTTP_POST_VARS["xsd_title"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["xsd_version"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["xsd_top_element_name"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["xsd_element_prefix"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["xsd_extra_ns_prefixes"]) . "',
                    '" . addslashes($blob) . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {

        }
    }

    /**
     * Method used to update a Document Type XSD in the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($xsd_id)
    {
        global $HTTP_POST_VARS, $HTTP_POST_FILES;
        $files = array();
        for ($i = 0; $i < count($HTTP_POST_FILES["xsd_file"]); $i++) {
            $filename = @$HTTP_POST_FILES["xsd_file"]["name"][$i];
            if (empty($filename)) {
                continue;
            }
            $blob = Misc::getFileContents($HTTP_POST_FILES["xsd_file"]["tmp_name"][$i]);
            $files[] = array(
                "filename"  =>  $filename,
                "type"      =>  $HTTP_POST_FILES['xsd_file']['type'][$i],
                "blob"      =>  $blob
            );
        }

		// If no file was uploaded then just use the textarea
		if (strlen($blob) == 0) {
			$blob = $HTTP_POST_VARS["xsd_source"];
		}
		
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd
                 SET 
                    xsd_title = '" . Misc::escapeString($HTTP_POST_VARS["xsd_title"]) . "',
                    xsd_version = '" . Misc::escapeString($HTTP_POST_VARS["xsd_version"]) . "',
                    xsd_top_element_name = '" . Misc::escapeString($HTTP_POST_VARS["xsd_top_element_name"]) . "',
                    xsd_element_prefix = '" . Misc::escapeString($HTTP_POST_VARS["xsd_element_prefix"]) . "',
                    xsd_extra_ns_prefixes = '" . Misc::escapeString($HTTP_POST_VARS["xsd_extra_ns_prefixes"]) . "',
                    xsd_file = '" . addslashes($blob) . "'
                 WHERE xsd_id = $xsd_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			//
        }
    }



    /**
     * Method used to get the list of Document Type XSDs in the 
     * system.
     *
     * @access  public
     * @return  array The list of Document Type XSDs
     */
    function getList()
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd
                 ORDER BY
                    xsd_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }



    /**
     * Method used to get the source of an XSD in the 
     * system.
     *
     * @access  public
	 * @param   integer $xsd_id The XSD ID of the record
     * @return  array The XSD Source
     */
    function getXSDSource($xsd_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd
                 WHERE
                    xsd_id=$xsd_id";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;

        }
    }

    /**
     * Method used to get the details of a specific Document Type XSD.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The Document Type XSD details
     */
    function getDetails($xsd_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd
                 WHERE
                    xsd_id=$xsd_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the XSD title when given an ID
     *
     * @access  public
	 * @param   integer $xdis_id The XSD ID to search by.
     * @return  array $res The title 
     */
    function getTitle($xsd_id)
    {
        $stmt = "SELECT
                   xsd_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd
                 WHERE
                    xsd_id = $xsd_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    
    function exportXSDs()
    {
        $doc = new DOMDocument('1.0','utf-8');
        $doc->formatOutput = true;
        $doc->appendChild($doc->createElement('fez_xsds'));
        $root = $doc->documentElement;
        $root->setAttribute('schema_version','1.0');
        $xsds = Doc_Type_XSD::getList();
        foreach ($xsds as $xsd) {
        	$xnode = $doc->createElement('fez_xsd');
            $xnode->setAttribute('xsd_id', $xsd['xsd_id']);                          
            $xnode->setAttribute('xsd_version', $xsd['xsd_version']);                          
            $xnode->setAttribute('xsd_top_element_name', $xsd['xsd_top_element_name']);                          
            $xnode->setAttribute('xsd_element_prefix', $xsd['xsd_element_prefix']);                          
            $xnode->setAttribute('xsd_extra_ns_prefixes', $xsd['xsd_extra_ns_prefixes']);
            $xsd_file = $doc->createElement('xsd_file');
            $xsd_file->appendChild($doc->createCDATASection($xsd['xsd_file']));
            $xnode->appendChild($xsd_file);
            XSD_Display::exportDisplays($xnode, $xsd['xsd_id']);
            
            $root->appendChild($xnode);                          
        }
        return $doc->saveXML();         
    } 
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Doc_Type_XSD Class');
}
?>
