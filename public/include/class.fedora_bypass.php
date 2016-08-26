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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.misc.php");
require_once(APP_INC_PATH . "nusoap.php");
include_once(APP_PEAR_PATH . "/HTTP/Request.php");
require_once(APP_INC_PATH . "class.fedora_direct_access.php");
include_once(APP_INC_PATH . "class.dsresource.php");
include_once(APP_INC_PATH . "class.fedora_api_interface.php");

class Fedora_API implements FedoraApiInterface {

	/**
	 * Gets the next available persistent identifier.
	 *
	 * @return string $pid The next available PID in from the PID handler
	 */
	public static function getNextPID()
	{
		$digObj = new DigitalObject();
		$pid = $digObj->save(array());
		return $pid;
	}

	/**
	 * Gets the XML of a given object by PID.
	 *
	 * @param string $pid The persistent identifier
	 * @return string $result The XML of the object
	 */
	public static function getObjectXML($pid)
	{

	}

	/**
	 * Gets the audit trail for an object.
	 *
	 * @param string $pid The persistent identifier
	 * @return array of audit trail
	 */
	public static function getAuditTrail($pid)
	{

	}

	/**
	 * This function ingests a FOXML object and base64 encodes it
	 *
	 * @access  public
	 * @param string $foxml The XML object itself in FOXML format
	 * @param string $pid The persistent identifier
	 * @return bool
	 */
	public static function callIngestObject($foxml, $pid = "")
	{

	}

	/**
	 * Exports an associative array
	 *
	 * @param string $pid
	 * @param string $format
	 * @param string $context
	 * @return array
	 */
	public static function export($pid, $format = "info:fedora/fedora-system:FOXML-1.0", $context = "migrate")
	{

	}

	/**
	 * Returns an associative array
	 *
	 * @param array $resultFields
	 * @param int $maxResults
	 * @param string $query_terms
	 * @return array
	 */
	public static function callFindObjects($resultFields = array(
		'pid',
		'title',
		'identifier',
		'description',
		'state'
	), $maxResults = 10, $query_terms = "")
	{

	}

	/**
	 * Resumes a find
	 *
	 * @param string $token
	 * @return array
	 */
	public static function callResumeFindObjects($token)
	{

	}

	/**
	 * This function uses Fedora's simple search service which only really works against Dublin Core records.
	 * @param string $query The query by which the search will be carried out.
	 *		See http://www.fedora.info/wiki/index.php/API-A-Lite_findObjects#Parameters: for
	 *		documentation of the syntax of the query.
	 * @param array $fields The list of DC and Fedora basic fields to search against.
	 * @return array $resultList The search results.
	 */
	// Deprecate this function and replace calls to it
	//public function searchQuery($query, $fields = array('pid', 'title'));

	/**
	 * This function removes an object and all its datastreams from Fedora
	 *
	 * @param string $pid The persistent identifier of the object to be purged
	 * @return bool
	 */
	public static function callPurgeObject($pid)
	{

	}

	/**
	 * This function uses curl to upload a file into the fedora upload manager and calls the addDatastream or modifyDatastream as needed.
	 *
	 * @access  public
	 * @param string $pid The persistent identifier of the object to be purged
	 * @param string $dsIDName The datastream name
	 * @param string $file The file name
	 * @param string $dsLabel The datastream label
	 * @param string $mimetype The mimetype of the datastream
	 * @param string $controlGroup The control group of the datastream
	 * @param string $dsID The ID of the datastream
	 * @param bool|string $versionable Whether to version control this datastream or not
	 * @return string
	 */
	public static function getUploadLocation($pid, $dsIDName, $file, $dsLabel, $mimetype = 'text/xml', $controlGroup = 'M', $dsID = NULL, $versionable = FALSE)
	{
		$file_full = $file;

		$versionable = $versionable === true ? 'true' : $versionable === false ? 'false' : $versionable;
		$dsExists = Fedora_API::datastreamExists($pid, $dsIDName, true);

		if ($dsExists !== true) {
			Fedora_API::callAddDatastream($pid, $dsIDName, $file_full, $dsLabel, "A", $mimetype, $controlGroup, $versionable, '');
		} else {
			self::_callModifyDatastream($pid, $dsIDName, $file_full, $dsLabel, "A", $mimetype, $versionable, '');
		}

		if (is_file($file_full)) {
			unlink($file_full);
		}
		return $dsIDName;
	}

	/**
	 * This function uses curl to get a file from a local file location and upload it into the fedora upload manager and calls the addDatastream or modifyDatastream as needed.
	 *
	 * Developer Note: Mainly used by batch import of a SAN directory
	 *
	 * @param string $pid The persistent identifier of the object to be purged
	 * @param string $dsIDName The datastream name
	 * @param string $dsLocation The location of the file on a local server directory
	 * @param string $dsLabel The datastream label
	 * @param string $mimetype The mimetype of the datastream
	 * @param string $controlGroup The control group of the datastream
	 * @param string $dsID The ID of the datastream
	 * @param bool|string $versionable Whether to version control this datastream or not
	 * @return integer
	 */
	public static function getUploadLocationByLocalRef($pid, $dsIDName, $dsLocation, $dsLabel, $mimetype, $controlGroup = 'M', $dsID = NULL, $versionable = FALSE)
	{
		if (! Zend_Registry::isRegistered('version')) {
			Zend_Registry::set('version', Date_API::getCurrentDateGMT());
		}

		$now = Zend_Registry::get('version');

		$resourceDataLocation = $dsLocation;
		$filesDataSize = filesize($dsLocation);
		$meta = array('mimetype' => $mimetype,
			'filename' => $dsIDName,
			'label' => $dsLabel,
			'controlgroup' => 'M',
			'state' => 'A',
			'size' => $filesDataSize,
			'updateTS' => $now,
			'pid' => $pid);
		$dsr = new DSResource(APP_DSTREE_PATH, $resourceDataLocation, $meta);
		$dsr->save();
		$meta = $dsr->getMeta();
		return $meta['id'];
	}

	/**
	 * This function adds datastreams to object $pid.
	 *
	 * @param string $pid The persistent identifier of the object to be purged
	 * @param string $dsID The ID of the datastream
	 * @param string $dsLocation The location of the file to add
	 * @param string $dsLabel The datastream label
	 * @param string $dsState The datastream state
	 * @param string $mimetype The mimetype of the datastream
	 * @param string $controlGroup The control group of the datastream
	 * @param bool|string $versionable Whether to version control this datastream or not
	 * @param string $xmlContent If it an X based xml content file then it uses a var rather than a file location
	 * @param int $current_tries A counter of how many times this function has retried the addition of a datastream
	 * @return void
	 */
	public static function callAddDatastream($pid, $dsID, $dsLocation, $dsLabel, $dsState, $mimetype, $controlGroup = 'M', $versionable = FALSE, $xmlContent = "", $current_tries = 0)
	{
		if ($mimetype == "") {
			$mimetype = "text/xml";
		}
		$dsIDOld = $dsID;
		if (is_numeric(strpos($dsID, chr(92)))) {
			$dsID = substr($dsID, strrpos($dsID, chr(92))+1);
			if ($dsLabel == $dsIDOld) {
				$dsLabel = $dsID;
			}
		}
		$dsIDName = $dsID;
		if (is_numeric(strpos($dsIDName, "/"))) {
			$dsIDName = substr($dsIDName, strrpos($dsIDName, "/")+1);
		}

		if (! Zend_Registry::isRegistered('version')) {
			Zend_Registry::set('version', Date_API::getCurrentDateGMT());
		}

		$now = Zend_Registry::get('version');

		$resourceDataLocation = $dsLocation;
		$filesDataSize = filesize($dsLocation);
		$meta = array('mimetype' => $mimetype,
			'filename' => $dsIDName,
			'label' => $dsLabel,
			'controlgroup' => 'M',
			'state' => 'A',
			'size' => $filesDataSize,
			'updateTS' => $now,
			'pid' => $pid);
		$dsr = new DSResource(APP_DSTREE_PATH, $resourceDataLocation, $meta);
		$dsr->save();
	}

	/**
	 *This function creates an array of all the datastreams for a specific object.
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $createdDT Fedora timestamp of version to retrieve
	 * @param string $dsState The datastream state
	 * @return array $dsIDListArray The list of datastreams in an array.
	 */
	public static function callGetDatastreams($pid, $createdDT = NULL, $dsState = 'A')
	{
		$dob = new DSResource();
		$dsIDListArray = $dob->listStreams($pid);

		if (empty($dsIDListArray) || (is_array($dsIDListArray) && isset($dsIDListArray['faultcode']))) {
			return false;
		}
		if (!is_array($dsIDListArray[0])) {
			// when only one datastream, it returns as a datastream instead of
			// array of datastreams so rewrite as array of datastreams to match
			// multiple datastreams format
			$ds = array();
			$ds['controlGroup'] = $dsIDListArray['controlGroup'];
			$ds['ID']           = $dsIDListArray['ID'];
			$ds['versionID']    = $dsIDListArray['versionID'];
			$ds['altIDs']       = $dsIDListArray['altIDs'];
			$ds['label']        = $dsIDListArray['label'];
			$ds['versionable']  = $dsIDListArray['versionable'];
			$ds['MIMEType']     = $dsIDListArray['MIMEType'];
			$ds['formatURI']    = $dsIDListArray['formatURI'];
			$ds['createDate']   = $dsIDListArray['createDate'];
			$ds['size']         = $dsIDListArray['size'];
			$ds['state']        = $dsIDListArray['state'];
			$ds['location']     = $dsIDListArray['location'];
			$ds['checksumType'] = $dsIDListArray['checksumType'];
			$ds['checksum']     = $dsIDListArray['checksum'];

			$dsIDListArray = array();
			$dsIDListArray[0] = $ds;
		}
		sort($dsIDListArray);
		reset($dsIDListArray);

		return $dsIDListArray;
	}

	/**
	 * This function creates an array of all the datastreams for a specific object using the API-A-LITE rather than soap
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param bool $refresh Avoid a cached copy
	 * @param int $current_tries A counter of how many times this function has retried the addition of a datastream
	 * @return array $dsIDListArray The list of datastreams in an array.
	 */
	public static function callListDatastreamsLite($pid, $refresh = FALSE, $current_tries = 0)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($pid)) {

			$rows = array();

			$sql = "SELECT fat_filename, fat_mimetype, fat_version FROM "
				. APP_TABLE_PREFIX . "file_attachments WHERE fat_pid = :pid GROUP BY fat_filename";

			try
			{
				$stmt = $db->query($sql, array(':pid' => $pid));
				$rows = $stmt->fetchAll();
			}
			catch(Exception $e)
			{
				$log->err($e->getMessage());
			}

			$resultlist = array();
			foreach($rows as $row)
			{
				$resultlist[] = array('dsid' => $row['fat_filename'],
					'label' => $row['fat_filename'],
					'mimeType' => $row['fat_mimetype']);
			}
			return $resultlist;
		} else {
			return array();
		}
	}

	/**
	 * @param string $pid The persistent identifier of the object
	 * @param bool $refresh
	 * @return bool
	 */
	public static function objectExists($pid, $refresh = FALSE)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		$stmt = "SELECT rek_pid
                FROM ". APP_TABLE_PREFIX . "record_search_key
                WHERE rek_pid = ".$db->quote($pid);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		if ($res == $pid) {
			return true;
		}

		$stmt = "SELECT rek_pid
                FROM ". APP_TABLE_PREFIX . "record_search_key__shadow
                WHERE rek_pid = ".$db->quote($pid);
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return ($res == $pid);
	}

	/**
	 * This function creates an array of a specific datastream of a specific object
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $dsID The ID of the datastream
	 * @param string $createdDT Date time stamp as a string
	 * @return array The requested of datastream in an array.
	 */
	public static function callGetDatastream($pid, $dsID, $createdDT = NULL)
	{
		$dsr = new DSResource(APP_DSTREE_PATH);
		$dsr->load($dsID, $pid);
		$dsArray = $dsr->getDSRev($dsID, $pid);
		$dsArray['ID'] = $dsID;
		$vers = $dsr->getDSRevs($dsID, $pid);
		$vers = $vers[$dsArray['version']];
		$dsArray['versionID'] = $vers;
		$dsArray['label'] = $dsID;
		$dsArray['controlGroup'] = $dsArray['controlgroup'];
		$dsArray['MIMEType'] = $dsArray['mimetype'];
		$dsArray['createDate'] = $dsArray['version'];
		$dsArray['location'] = NULL; //TODO Check if this is needed and if so fill with a real value.
		$dsArray['formatURI'] = NULL; //TODO Check if this is needed and if so fill with a real value.
		$dsArray['checksumType'] = 'DISABLED'; //TODO Check if this is needed and if so fill with a real value.
		$dsArray['checksum'] = 'none'; //TODO Check if this is needed and if so fill with a real value.
		$dsArray['versionable'] = FALSE; //TODO Check if this is needed and if so fill with a real value.

		return $dsArray;
	}

  /**
   * Gets the history of a datastream.
   *
   * @param string $pid The persistent identifier of the object
   * @param string $dsID The ID of the datastream
   * @return array of the history
   */
  public static function callGetDatastreamHistory($pid, $dsID)
  {
  }

	/**
	 * Does a datastream with a given ID already exist in an object
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $dsID The ID of the datastream to be checked
	 * @param bool $refresh Avoid a cached copy
	 * @param bool $pattern a regex pattern to search against if given instead of ==/equivalence
	 * @return boolean
	 */
	public static function datastreamExists($pid, $dsID, $refresh = FALSE, $pattern = FALSE)
	{
		if (Misc::isPid($pid) != true) {
			return false;
		}

		$dsExists = false;

		$rs = Fedora_API::callListDatastreamsLite($pid, $refresh);
		if (is_array($rs)) {
			foreach ($rs as $row) {
				if ($pattern != false) {
					if (isset($row['dsid']) && preg_match($pattern, $row['dsid'], $ds_matches)) {
						return $ds_matches[0];
						$dsExists = true;
					}
				} else {
					if (isset($row['dsid']) && ($row['dsid'] == $dsID)) {
						$dsExists = true;
					}
				}
			}
		}
		return $dsExists;
	}

	/**
	 * Does a datastream with a given ID already exist in existing list array of datastreams
	 *
	 * @param string $existing_list The existing list of datastreams
	 * @param string $dsID The ID of the datastream to be checked
	 * @return boolean
	 */
	public static function datastreamExistsInArray($existing_list, $dsID)
	{
		$dsExists = false;
		$rs = $existing_list;
		foreach ($rs as $row) {
			if (isset($row['ID']) && $row['ID'] == $dsID) {
				$dsExists = true;
			}
		}
		return $dsExists;
	}

	/**
	 * This function creates an array of a specific datastream of a specific object
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $dsID The ID of the datastream to be checked
	 * @param string $asofDateTime Gets a specified version at a datetime stamp
	 * @return array The datastream returned in an array
	 */
	public static function callGetDatastreamDissemination($pid, $dsID, $asofDateTime = "")
	{
		// Redirect all calls to the REST Version for now - CK added 17/7/2009
		$return = array();
		$return['stream']= Fedora_API::callGetDatastreamContents($pid, $dsID, true);
		return $return;
	}

	/**
	 * This function creates an array of a specific datastream of a specific object
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $dsID The ID of the datastream
	 * @param boolean $getraw Get as xml
	 * @param resource $filehandle
	 * @param int $current_tries A counter of how many times this function has retried
	 * @return array $resultlist The requested of datastream in an array.
	 */
	public static function callGetDatastreamContents($pid, $dsID, $getraw = FALSE, $filehandle = NULL, $current_tries = 0)
	{
		// $filehandle is a legacy arg left here to keep the API intact.
		$dsr = new DSResource(APP_DSTREE_PATH);
		$dsMeta = $dsr->getDSRev($dsID, $pid);

		$dsExists = Fedora_API::datastreamExists($pid, $dsID);
		if($dsExists)
		{
			if($dsMeta['mimetype'] != 'text/xml' || $getraw)
			{
				$return =  $dsr->getDSData($dsMeta['hash']);
				if (!is_null($filehandle)) {
					fwrite($filehandle, $return);
				}
			}
			else
			{
				$return = array(
					'date' => array($dsMeta['version']),
					'repInfo' => array($dsr->getDSData($dsMeta['hash'])),
					'uri' => array($dsr->createPath($dsMeta['hash']) . $dsMeta['hash'])
				);
			}
			return $return;
		}
	}

	/**
	 * This function creates an array of specific fields from a specific datastream of a specific object
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $dsID The ID of the datastream
	 * @param array $returnfields
	 * @param string $asOfDateTime Gets a specified version at a datetime stamp
	 * @return array The requested of datastream in an array.
	 */
	public static function callGetDatastreamContentsField($pid, $dsID, $returnfields, $asOfDateTime = "")
	{
		static $counter;
		if (!isset($counter)) {
			$counter = 0;

		}
		$counter++;
		$resultlist = array();
		$dsExists = Fedora_API::datastreamExists($pid, $dsID);
		if ($dsExists == true) {
			$xml = Fedora_API::callGetDatastreamDissemination($pid, $dsID, $asOfDateTime);
			$xml = $xml['stream'];
			if (!empty($xml) && $xml != false) {
				$doc = DOMDocument::loadXML($xml);
				$xpath = new DOMXPath($doc);
				$fieldNodeList = $xpath->query("/$dsID/*");
				foreach ($fieldNodeList as $fieldNode) {
					if (in_array($fieldNode->nodeName, $returnfields)) {
						$resultlist[$fieldNode->nodeName][] = trim($fieldNode->nodeValue);
					}
				}
			}
		}
		return $resultlist;
	}

	/**
	 * This function modifies inline xml datastreams (ByValue)
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $dsID The name of the datastream
	 * @param string $state The datastream state
	 * @param string $label The datastream label
	 * @param string $dsContent The datastream content
	 * @param string $mimetype The mimetype of the datastream
	 * @param bool|string $versionable Whether to version control this datastream or not
	 * @return void
	 */
	public static function callModifyDatastreamByValue($pid, $dsID, $state, $label, $dsContent, $mimetype = 'text/xml', $versionable = 'inherit')
	{
		self::_callModifyDatastream($pid, $dsID, "", $label, "A", $mimetype, $versionable, $dsContent);
	}

	/**
	 * This function modifies non-in-line datastreams, either a chunk o'text, a url, or a file.
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $dsID The name of the datastream
	 * @param string $dsLabel The datastream label
	 * @param string $dsLocation The location of the datastream
	 * @param string $mimetype The mimetype of the datastream
	 * @param bool|string $versionable Whether to version control this datastream or not
	 * @return void
	 */
	public static function callModifyDatastreamByReference($pid, $dsID, $dsLabel, $dsLocation = NULL, $mimetype, $versionable = 'inherit')
	{
		$dsr = new DSResource();
		$dsr->load($dsID, $pid);
		$meta = $dsr->getMeta();
		$meta['filename'] = $dsID;
		$meta['label'] = $dsLabel;
		$dsr->setMeta($meta);
		$dsr->save();
	}

	/**
	 * Changes the state and/or label of the object.
	 *
	 * @param string $pid The pid of the object
	 * @param string $state The new state, A, I or D. Null means leave unchanged
	 * @param string $label The new label. Null means leave unchanged
	 * @param string $logMessage A log message
	 */
	public static function callModifyObject($pid, $state, $label, $logMessage = 'Deleted by Fez')
	{

	}

	/**
	 * This function marks a datastream as deleted by setting the state.
	 *
	 * @param string $pid The persistent identifier of the object
	 * @param string $dsID The ID of the datastream
	 * @return bool
	 */
	public static function deleteDatastream($pid, $dsID)
	{
		$dresource = new DSResource();
		$dresource->load($dsID, $pid);
		$dresource->dereference();
		return true;
	}

	/**
	 * This function deletes a datastream
	 *
	 * @param string $pid The persistent identifier of the object to be purged
	 * @param string $dsID The name of the datastream
	 * @param string $startDT The start datetime of the purge
	 * @param string $endDT The end datetime of the purge
	 * @param string $logMessage
	 * @param bool $force
	 * @return bool
	 */
	public static function callPurgeDatastream($pid, $dsID, $startDT = NULL, $endDT = NULL, $logMessage = "Purged Datastream from Fez", $force = FALSE)
	{
		$dresource = new DSResource();
		$dresource->load($dsID, $pid);
		$dresource->dereference();
		return true;
	}

	/**
	 * This function modifies a datastream
	 *
	 * @param string $pid The persistent identifier of the object to be purged
	 * @param string $dsID The ID of the datastream
	 * @param string $dsLocation The location of the file to add
	 * @param string $dsLabel The datastream label
	 * @param string $dsState The datastream state
	 * @param string $mimetype The mimetype of the datastream
	 * @param bool|string $versionable Whether to version control this datastream or not
	 * @param string $xmlContent If it an X based xml content file then it uses a var rather than a file location
	 * @return void
	 */
	private static function _callModifyDatastream ($pid, $dsID, $dsLocation, $dsLabel, $dsState, $mimetype, $versionable='false', $xmlContent="")
	{
		if ($mimetype == "") {
			$mimetype = "text/xml";
		}
		$dsIDOld = $dsID;
		if (is_numeric(strpos($dsID, chr(92)))) {
			$dsID = substr($dsID, strrpos($dsID, chr(92))+1);
			if ($dsLabel == $dsIDOld) {
				$dsLabel = $dsID;
			}
		}

		if(!Zend_Registry::isRegistered('version')) {
			Zend_Registry::set('version', Date_API::getCurrentDateGMT());
		}

		$now = Zend_Registry::get('version');

		$resourceDataLocation = $dsLocation;
		$filesDataSize = filesize($dsLocation);
		$meta = array('mimetype' => $mimetype,
			'filename' => $dsID,
			'label' => $dsLabel,
			'controlgroup' => 'M',
			'state' => 'A',
			'size' => $filesDataSize,
			'updateTS' => $now,
			'pid' => $pid);
		$dsr = new DSResource(APP_DSTREE_PATH, $resourceDataLocation, $meta);
		$dsr->save();
	}

	/**
	 * This function creates an array of all the datastreams for a specific object.
	 *
	 * @param string $pid The persistent identifier of the object
	 * @return array The list of datastreams in an array.
	 */
	private static function _callListDatastreams($pid)
	{
		if (!is_numeric($pid)) {
			$dob = new DSResource();
			$dsIDListArray = $dob->listStreams($pid);
			sort($dsIDListArray);
			reset($dsIDListArray);
			return $dsIDListArray;
		} else {
			return array();
		}
	}

	/**
	 * Format the version of the a file to conform to Fedora_API
	 *
	 * @param string $filename
	 * @param string $pid
	 *
	 * @return array
	 */
	private static function _formatVersion($filename, $pid)
	{
		$versions = array();
		$ver = 0;
		$dsr = new DSResource(APP_DSTREE_PATH);
		$revs = $dsr->getDSRevs($filename, $pid);
		foreach($revs as $rev)
		{
			$versions[$rev['version']] = $filename . "." . $ver;
			$ver++;
		}

		return $versions;
	}
}
