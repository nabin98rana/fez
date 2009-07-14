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
	function remove($params = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($params)) {
			$params = $_POST;
		}
		$items = $params["items"];

		// delete the displays
		$xdis_items = array();
		foreach ($items as $item) {
			$xdis_items = array_merge(array_keys(Misc::keyArray(XSD_Display::getList($item), 'xdis_id')), $xdis_items);
		}
		if (!empty($xdis_items)) {
			XSD_Display::remove(array('items' => $xdis_items));
		}
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "xsd
                 WHERE
                    xsd_id IN (".Misc::arrayToSQLBindStr($params["items"]).")";
		try {
			$db->query($stmt, $params["items"]);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}
		return true;
	}


	/**
	 * Method used to add a new Document Type XSD to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert($params = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($params)) {
			$filename = @$_FILES["xsd_file"]["tmp_name"];
			if (empty($filename)) {
				$blob = "";
			} else {
				$blob = Misc::getFileContents($filename);
			}
			$params = &$_POST;
		} else {
			$blob = $params['xsd_file'];
		}

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "xsd
                 (
                    xsd_title,
                    xsd_version,
                    xsd_top_element_name,
                    xsd_element_prefix,
                    xsd_extra_ns_prefixes,
                    xsd_file
                 ) VALUES (?,?,?,?,?,?)";
		try {
			$db->query($stmt, array($params["xsd_title"], $params["xsd_version"], $params["xsd_top_element_name"] ,
									$params["xsd_element_prefix"], $params["xsd_extra_ns_prefixes"], $blob));
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
		return 1;
	}

	function insertAtId($xsd_id,$params)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "xsd
                 (
                    xsd_id,
                    xsd_title,
                    xsd_version,
                    xsd_top_element_name,
                    xsd_element_prefix,
                    xsd_extra_ns_prefixes,
                    xsd_file
                 ) VALUES (?,?,?,?,?,?,?)";
		try {
			$db->query($stmt, array($xsd_id, $params["xsd_title"], $params["xsd_version"], $params["xsd_top_element_name"],
									$params["xsd_element_prefix"], $params["xsd_extra_ns_prefixes"],$params['xsd_file']));
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
		return 1;
	}

	/**
	 * Method used to update a Document Type XSD in the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function update($xsd_id, $params = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($params)) {
			$files = array();
			for ($i = 0; $i < count($_FILES["xsd_file"]); $i++) {
				$filename = @$_FILES["xsd_file"]["name"][$i];
				if (empty($filename)) {
					continue;
				}
				$blob = Misc::getFileContents($_FILES["xsd_file"]["tmp_name"][$i]);
				$files[] = array(
                    "filename"  =>  $filename,
                    "type"      =>  $_FILES['xsd_file']['type'][$i],
                    "blob"      =>  $blob
				);
			}
			// If no file was uploaded then just use the textarea
			if (strlen($blob) == 0) {
				$blob = $_POST["xsd_source"];
			}
			$params = &$_POST;
		} else {
			$blob = $params['xsd_file'];
		}

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "xsd
                 SET 
                    xsd_title = ?,
                    xsd_version = ?,
                    xsd_top_element_name = ?,
                    xsd_element_prefix = ?,
                    xsd_extra_ns_prefixes = ?,
                    xsd_file = ?
                 WHERE xsd_id = ?";
		try {
			$db->query($stmt, array($params["xsd_title"], $params["xsd_version"], $params["xsd_top_element_name"],
									$params["xsd_element_prefix"], $params["xsd_extra_ns_prefixes"], $blob, $xsd_id));
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
		return 1;		
	}



	/**
	 * Method used to get the list of Document Type XSDs in the
	 * system.
	 *
	 * @access  public
	 * @return  array The list of Document Type XSDs
	 */
	function getList($select = '*', $where="")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    ".$select."
                 FROM
                    " . APP_TABLE_PREFIX . "xsd
                 ".$where."
                        ORDER BY
                    xsd_title ASC";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return array();
		}
		return $res;
	}

	/**
	 * getFoxmlXsdId - Special case to get the xsd_id of the foxml XSD which is the parent of
	 * all other XSDs.
	 * @return integer xsd_id of Foxml XSD
	 */
	function getFoxmlXsdId()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    xsd_id
                 FROM
                    " . APP_TABLE_PREFIX . "xsd
                 where xsd_top_element_name='digitalObject' ";
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return null;
		}
		return $res;
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
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "xsd
                 WHERE
                    xsd_id=".$db->quote($xsd_id, 'INTEGER');
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
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
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "xsd
                 WHERE
                    xsd_id=".$db->quote($xsd_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
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
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                   xsd_title
                 FROM
                    " . APP_TABLE_PREFIX . "xsd
                 WHERE
                    xsd_id = ".$db->quote($xsd_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	function exportXSDs($xdis_ids)
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
			$xnode->setAttribute('xsd_title', $xsd['xsd_title']);
			$xnode->setAttribute('xsd_version', $xsd['xsd_version']);
			$xnode->setAttribute('xsd_top_element_name', $xsd['xsd_top_element_name']);
			$xnode->setAttribute('xsd_element_prefix', $xsd['xsd_element_prefix']);
			$xnode->setAttribute('xsd_extra_ns_prefixes', $xsd['xsd_extra_ns_prefixes']);
			$xsd_file = $doc->createElement('xsd_file');
			$xsd_file->appendChild($doc->createCDATASection($xsd['xsd_file']));
			$xnode->appendChild($xsd_file);
			$xcount = XSD_Display::exportDisplays($xnode, $xsd['xsd_id'], $xdis_ids);
			if ($xcount > 0) {
				$root->appendChild($xnode);
			}
		}
		return $doc->saveXML();
	}
	 
	 
	/**
	 * This lists all the XSDs and accompanying displays in the XML file given.
	 * The items where the xdis_id and the xdis_title match will be flagged as overwrites.
	 * Same for where the xsd_id and the xsd_title match.
	 */
	function listImportFile($filename)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$doc = DOMDocument::load($filename);
		$xpath = new DOMXPath($doc);
		$xdocs = $xpath->query('/fez_xsds/fez_xsd');
		$list = array();
		foreach ($xdocs as $idx => $xdoc) {
			$item['xsd_id'] = $db->quote($xdoc->getAttribute('xsd_id'));
			$item['xsd_title'] = $db->quote($xdoc->getAttribute('xsd_title'));
			$item['xsd_version'] = $db->quote($xdoc->getAttribute('xsd_version'));
			$item['exist_list'] = Doc_Type_XSD::getList("xsd_id, xsd_title, xsd_version",
                "WHERE xsd_title=".$item['xsd_title']." AND xsd_version=".$item['xsd_version']);
			if (!empty($item['exist_list'])) {
				$item['overwrite'] = true;
			} else {
				$item['overwrite'] = false;
			}
			$item['displays'] = XSD_Display::listImportFile($item['xsd_id'], $xdoc);
			$item['displays_count'] = count($item['displays']);
			$list[] = $item;
		}
		return $list;
	}

	/**
	 * Import XSDs from a XML doc that was previously exported
	 */
	function importXSDs($filename, $xdis_ids, &$bgp)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$doc = DOMDocument::load($filename);
		$xpath = new DOMXPath($doc);
		$xdocs = $xpath->query('/fez_xsds/fez_xsd');
		$maps = array();
		$idx = 0;
		foreach ($xdocs as $xdoc) {
			$bgp->setProgress(intval(++$idx / count($xdocs) * 100 + 0.5));
			$title = $xdoc->getAttribute('xsd_title');
			$version = $xdoc->getAttribute('xsd_version');

			// skip this if the user doesn't want any of it's displays
			$import = false;
			foreach ($xdis_ids as $xdis_id) {
				$res = $xpath->query('display[@xdis_id='.$xdis_id.']', $xdoc);
				if ($res->length > 0) {
					$import = true;
				}
			}
			if (!$import) {
				$bgp->setStatus("Skipping XSD $title $version");
				continue;
			}

			// There are two things to consider when importing
			// 1) Upgrade docs which match on xsd_title and have version < import doc .  Remap any references in import doc to xdis)
			// 2) Insert new doc which don't match title.  Remap references in imported stuff
			$found_matching_title = false;
			$exist_list = Doc_Type_XSD::getList("*","WHERE xsd_title=".$db->quote($title)." AND xsd_version=".$db->quote($version));
			$doc_id = null;
			if (!empty($exist_list)) {
				$found_matching_title = true;
				$doc_id = $exist_list[0]['xsd_id'];
			}

			$xsd_file_nodelist =  $xpath->query('xsd_file',$xdoc);
			$xsd_file_node = $xsd_file_nodelist->item(0);
			$xsd_file = $xsd_file_node->textContent;

			$params = array(
                'xsd_title' => $title,
                'xsd_version' => $xdoc->getAttribute('xsd_version'),
                'xsd_file' => $xsd_file,
                'xsd_top_element_name' => $xdoc->getAttribute('xsd_top_element_name'),
                'xsd_element_prefix' => $xdoc->getAttribute('xsd_element_prefix'),
                'xsd_extra_ns_prefixes' => $xdoc->getAttribute('xsd_extra_ns_prefixes'),
			);
			if ($found_matching_title) {
				$bgp->setStatus("Overwriting XSD $title $version");
				Doc_Type_XSD::update($doc_id, $params);
			} else {
				$bgp->setStatus("Inserting XSD $title $version");
				// need to try and insert at the XML doc_id.  If there's something there already
				// then we know it doesn't match so do a insert with new id in that case
				$det =  Doc_Type_XSD::getDetails($xdoc->getAttribute('xsd_id'));
				if (empty($det)) {
					$doc_id = $xdoc->getAttribute('xsd_id');
					Doc_Type_XSD::insertAtId($doc_id,$params);
				} else {
					$doc_id = Doc_Type_XSD::insert($params);
				}
			}

			if ($doc_id) {
				// check for new displays
				XSD_Display::importDisplays($xdoc, $doc_id, $xdis_ids, $maps,$bgp);
			}
		}
		//print_r($maps);
		$bgp->setStatus("Remapping ids");
		XSD_HTML_Match::remapImport($maps, $bgp);
		$bgp->setStatus("Done");
	}
}
