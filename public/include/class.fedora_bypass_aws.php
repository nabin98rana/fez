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
include_once(APP_INC_PATH . "class.exiftool.php");
require_once(APP_INC_PATH . "nusoap.php");
include_once(APP_PEAR_PATH . "/HTTP/Request.php");
include_once(APP_INC_PATH . "class.aws.php");
include_once(APP_INC_PATH . "class.links.php");
include_once(APP_INC_PATH . "class.fedora_api_interface.php");
include_once(APP_INC_PATH . "class.datastream.php");

class Fedora_API implements FedoraApiInterface {

  /**
   * Gets the next available persistent identifier.
   *
   * @return string $pid The next available PID in from the PID handler
   */
  public static function getNextPID() {
    $log = FezLog::get();
    $db = DB_API::get();

    $pidInt = [];
    $pidNs = APP_PID_NAMESPACE;

    $db->beginTransaction();

    try {
      $sql = "SELECT MAX(pid_number)+1 AS next_id FROM " . APP_TABLE_PREFIX . "pid_index";
      $stmt = $db->query($sql);
      $pidInt = $stmt->fetch();

    } catch (Exception $e) {
      $log->err($e->getMessage());
    }
    // Check to see if this the first pid for this namespace.
    $pidInt = ($pidInt['next_id'] == NULL) ? 1 : $pidInt['next_id'];

    try {
      $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "pid_index";
      $db->query($stmt);

      $stmt = "INSERT INTO " . APP_TABLE_PREFIX .
        "pid_index (pid_number) VALUES (:pid_number)";
      $db->query($stmt, [':pid_number' => $pidInt]);

      $db->commit();

      return $pidNs . ":" . $pidInt;

    } catch (Exception $e) {
      $db->rollBack();
      $log->err($e->getMessage());
      return [];
    }
  }

  /**
   * Gets the XML of a given object by PID.
   *
   * @param string $pid The persistent identifier
   * @return string $result The XML of the object
   */
  public static function getObjectXML($pid) {

  }

  /**
   * Gets the audit trail for an object.
   *
   * @param string $pid The persistent identifier
   * @return array of audit trail
   */
  public static function getAuditTrail($pid) {

  }

  /**
   * This function ingests a FOXML object and base64 encodes it
   *
   * @access  public
   * @param string $foxml The XML object itself in FOXML format
   * @param string $pid The persistent identifier
   * @return bool
   */
  public static function callIngestObject($foxml, $pid = "") {

  }

  /**
   * Exports an associative array
   *
   * @param string $pid
   * @param string $format
   * @param string $context
   * @return array
   */
  public static function export($pid, $format = "info:fedora/fedora-system:FOXML-1.0", $context = "migrate") {

  }

  /**
   * Returns an associative array
   *
   * @param array $resultFields
   * @param int $maxResults
   * @param string|array $queryTerms
   * @return array
   */
  public static function callFindObjects($resultFields = array(
    'pid',
    'title',
    'identifier',
    'description',
    'state'
  ), $maxResults = 10, $queryTerms = "") {
    $log = FezLog::get();
    $db = DB_API::get();

    $list = [
      'resultList' => [
        'objectFields' => []
      ],
      'listSession' => [
        'token' => NULL
      ]
    ];

    $stmt = "SELECT rek_pid as pid, rek_title as title, rek_description as description" .
      " FROM " . APP_TABLE_PREFIX . "record_search_key";

    if (is_array($queryTerms)) {
      if ($queryTerms['state'] === 'D') {
        $stmt .= "__shadow WHERE rek_pid NOT IN (SELECT DISTINCT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key)";
      }

      if (array_key_exists('terms', $queryTerms)) {
        if ($queryTerms['state'] === 'D') {
          $stmt .= " AND ";
        }
        else {
          $stmt .= " WHERE ";
        }
        $stmt .= "rek_pid LIKE " . $db->quote("%" . str_replace('*', '', $queryTerms['terms']) . "%");
      }
      $stmt .= ' GROUP BY rek_pid';
    }
    else {
      if (!empty($queryTerms) && $queryTerms != '*') {
        $stmt .= " WHERE rek_pid LIKE " . $db->quote("%" . str_replace('*', '', $queryTerms) . "%");
      }
    }

    if ($maxResults > 0) {
      $stmt .= " LIMIT 0," . $db->quote($maxResults, 'INTEGER');
    }
    try {
      $list['resultList']['objectFields'] = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
      return $list;
    } catch (Exception $ex) {
      $log->err($ex);
      return [];
    }
  }

  /**
   * Resumes a find
   *
   * @param string $token
   * @return array
   */
  public static function callResumeFindObjects($token) {

  }

  /**
   * This function uses Fedora's simple search service which only really works against Dublin Core records.
   * @param string $query The query by which the search will be carried out.
   *    See http://www.fedora.info/wiki/index.php/API-A-Lite_findObjects#Parameters: for
   *    documentation of the syntax of the query.
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
  public static function callPurgeObject($pid) {
    $datastreams = Fedora_API::callGetDatastreams($pid);
    foreach ($datastreams as $ds) {
      if ($ds['controlGroup'] != 'R') {
        Fedora_API::callPurgeDatastream($pid, $ds['ID']);
      }
    }
    Links::purgeLinks($pid);

    return TRUE;
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
   * @return integer
   */
  public static function getUploadLocation($pid, $dsIDName, $file, $dsLabel, $mimetype = 'text/xml', $controlGroup = 'M', $dsID = NULL, $versionable = FALSE) {
    $loc_dir = '';

    if (!is_numeric(strpos($dsIDName, "/"))) {
      $loc_dir = Misc::getFileTmpPath();
    }

    $file_full = '';
    if (!empty($file) && (trim($file) != "")) {
//			$file_full = $loc_dir . str_replace(":", "_", $pid) . "_" . $dsIDName . ".xml";
      $file_full = $loc_dir . $dsIDName;
      $fp = fopen($file_full, "w"); //@@@ CK - 28/7/2005 - Trying to make the file name in /tmp the uploaded file name
      fwrite($fp, $file);
      fclose($fp);
    }
    $dsID = '';

    $versionable = $versionable === TRUE ? 'true' : $versionable === FALSE ? 'false' : $versionable;
    $dsExists = Fedora_API::datastreamExists($pid, $dsIDName, TRUE);

    if ($dsExists !== TRUE) {
      $dsID = Fedora_API::callAddDatastream($pid, $dsIDName, $file_full, $dsLabel, "A", $mimetype, $controlGroup, $versionable, '');
    }
    else {
      $dsID = Datastream::getDid($pid, $dsIDName);
    }

    if (is_file($file_full)) {
      unlink($file_full);
    }
    return $dsID;
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
  public static function getUploadLocationByLocalRef($pid, $dsIDName, $dsLocation, $dsLabel, $mimetype, $controlGroup = 'M', $dsID = NULL, $versionable = FALSE) {
    return Fedora_API::callAddDatastream($pid, $dsIDName, $dsLocation, $dsLabel, 'A', $mimetype, $controlGroup, $versionable, '', FALSE);
  }

  /**
   * This function returns what the path should be for a PID in S3
   *
   * @param string @pid
   * @return string
   */
  public static function getDataPath($pid) {
    return "data/" . str_replace(":", "_", $pid);
  }

  public static function getCloudFrontUrl($pid, $dsid) {
    $aws = AWS::get();
    $path = Fedora_API::getDataPath($pid);
    $cfURL = $aws->getById($path, $dsid);
    return $cfURL;
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
   * @param bool $unlinkLocalFile
   * @param bool|string $srcBucket
   * @return integer
   */
  public static function callAddDatastream($pid, $dsID, $dsLocation, $dsLabel, $dsState, $mimetype, $controlGroup = 'M', $versionable = FALSE, $xmlContent = "", $unlinkLocalFile = true, $srcBucket = FALSE) {
    if (is_numeric(strpos($dsID, chr(92)))) {
      $dsID = substr($dsID, strrpos($dsID, chr(92)) + 1);
    }

    $aws = AWS::get();
    $dataPath = Fedora_API::getDataPath($pid);

    $fezACMLXML = '';
    $isFezACML = FALSE;
    if (stripos($dsID, 'FezACML_') === 0) {
      $isFezACML = TRUE;
    }
    $tmpPath = Misc::getFileTmpPath();
    if (stripos($dsLocation, $tmpPath) === 0) {
      $obj = $aws->postFile($dataPath, [$dsLocation], FALSE, $mimetype);
      if ($obj) {
        $obj = $obj[0];
      }
      if ($isFezACML) {
        $fezACMLXML = file_get_contents($dsLocation);
      }
      if ($unlinkLocalFile) {
        unlink($dsLocation);
      }
    }
    else {
      $obj = TRUE;
      // @todo(post-migration): Remove migration check
      $copy = TRUE;
      /*if (
        defined('APP_MIGRATION_RUN')
        && APP_MIGRATION_RUN === TRUE
        && $aws->checkExistsById($dataPath, $dsID) === TRUE
      ) {
        $copy = FALSE;
      }*/
      if ($copy) {
        $obj = $aws->copyFile($dsLocation, $dataPath . "/" . $dsID, $srcBucket, $mimetype);
      }
      if ($isFezACML) {
        $fezACMLXML = $aws->getFileContent($dataPath, $dsID, [], true);
      }
    }
    if (!$obj) {
      return FALSE;
    }

    $dsArray = $aws->headObject($dataPath . "/" . $dsID);
    if (!$dsArray) {
      return FALSE;
    }
    $object = [
      'url' => $dsArray['@metadata']['effectiveUri'],
      'size' => $dsArray['ContentLength'],
      'version' => $dsArray['VersionId'],
      'checksum' => str_replace('"', '', $dsArray['ETag'])
    ];

    return Datastream::addDatastreamInfo($pid, $dsID, $mimetype, $object, $dsState, $dsLabel, $fezACMLXML);
  }

  /**
   *This function creates an array of all the datastreams for a specific object.
   *
   * @param string $pid The persistent identifier of the object
   * @param string $createdDT Fedora timestamp of version to retrieve
   * @param string $dsState The datastream state
   * @return array $dsIDListArray The list of datastreams in an array.
   */
  public static function callGetDatastreams($pid, $createdDT = NULL, $dsState = 'A') {
    $dsArray = Datastream::getFullDatastreamInfo($pid);
    $dataStreams = [];

    foreach ($dsArray as $object) {
      $dataStreams[] = Fedora_API::callGetDatastream($pid, $object['dsi_dsid'], $createdDT);
    }

    //Add on the links 'R' based datastreams
    $links = Links::getLinks($pid);

    foreach ($links as &$link) {
      $linkDS = array();
      $linkDS['ID'] = trim($link['rek_link']);
      $linkDS['location'] = $linkDS['ID'];
      $linkDS['label'] = trim($link['rek_link_description']);
      $linkDS['controlGroup'] = 'R';
      $dataStreams[] = $linkDS;
    }

    return $dataStreams;
  }

  /**
   * This function creates an array of all the datastreams for a specific object using the API-A-LITE rather than soap
   *
   * @param string $pid The persistent identifier of the object
   * @param bool $refresh Avoid a cached copy
   * @param int $current_tries A counter of how many times this function has retried the addition of a datastream
   * @return array $dsIDListArray The list of datastreams in an array.
   */
  public static function callListDatastreamsLite($pid, $refresh = FALSE, $current_tries = 0) {
    return Datastream::getDatastreamInfo($pid);
  }

  /**
   * @param string $pid The persistent identifier of the object
   * @param bool $refresh
   * @return bool
   */
  public static function objectExists($pid, $refresh = FALSE) {
    $log = FezLog::get();
    $db = DB_API::get();
    $stmt = "SELECT rek_pid
                FROM " . APP_TABLE_PREFIX . "record_search_key
                WHERE rek_pid = " . $db->quote($pid);
    try {
      $res = $db->fetchOne($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return array();
    }
    if ($res == $pid) {
      return TRUE;
    }

    $stmt = "SELECT rek_pid
                FROM " . APP_TABLE_PREFIX . "record_search_key__shadow
                WHERE rek_pid = " . $db->quote($pid);
    try {
      $res = $db->fetchOne($stmt);
    } catch (Exception $ex) {
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
  public static function callGetDatastream($pid, $dsID, $createdDT = NULL) {
    $createdDT = NULL; // Force NULL until S3 versions are supported
    $dataPath = Fedora_API::getDataPath($pid);
    $dsArray = Datastream::getFullDatastreamInfo($pid, $dsID);

    $dsData = array();
    $dsData['ID'] = $dsID;
    $dsData['versionID'] = $dsArray['dsi_version'];
    $dsData['label'] = $dsArray['dsi_label'];
    $dsData['controlGroup'] = "M";
    $dsData['MIMEType'] = $dsArray['dsi_mimetype'];
    $dsData['createDate'] = NULL; //(string)$dsArray['LastModified']; //TODO: convert to saved meta
    $dsData['location'] = $dataPath . "/" . $dsID;
    $dsData['formatURI'] = NULL; //TODO Check if this is needed and if so fill with a real value.
    $dsData['checksumType'] = 'MD5';
    $dsData['checksum'] = $dsArray['dsi_checksum'];
    $dsData['versionable'] = FALSE; //TODO Check if this is needed and if so fill with a real value.
    $dsData['size'] = $dsArray['size'];
    $dsData['state'] = 'A';

    return $dsData;
  }

  /**
   * Gets the history of a datastream.
   *
   * @param string $pid The persistent identifier of the object
   * @param string $dsID The ID of the datastream
   * @return array of the history
   */
  public static function callGetDatastreamHistory($pid, $dsID) {
    $rec = new Fez_Record_SearchkeyShadow($pid);
    return $rec->returnVersionDates();
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
  public static function datastreamExists($pid, $dsID, $refresh = FALSE, $pattern = FALSE) {
    if (Misc::isPid($pid) != TRUE) {
      return FALSE;
    }

    $aws = AWS::get();
    $dataPath = Fedora_API::getDataPath($pid);
    $dsExists = $aws->checkExistsById($dataPath, $dsID); //TODO: implement $pattern if necessary

    return $dsExists;
  }

  /**
   * Does a datastream with a given ID already exist in existing list array of datastreams
   *
   * @param array $existing_list The existing list of datastreams
   * @param string $dsID The ID of the datastream to be checked
   * @return boolean
   */
  public static function datastreamExistsInArray($existing_list, $dsID) {
    $dsExists = FALSE;
    $rs = $existing_list;
    foreach ($rs as $row) {
      if (isset($row['ID']) && $row['ID'] == $dsID) {
        $dsExists = TRUE;
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
   * @param bool $getRaw Return the raw file content else return cast to string
   * @return array The datastream returned in an array
   */
  public static function callGetDatastreamDissemination($pid, $dsID, $asofDateTime = "", $getRaw = false) {
    $return = array();
    $args = array();

    $aws = AWS::get();
    $dataPath = Fedora_API::getDataPath($pid);

    if ($asofDateTime != "") {
      $args = array("VersionId" => $asofDateTime);
    }

    // If the datastream request is FezACML, use the DB cache
    if (stripos($dsID, 'FezACML_') === 0) {
      $return['stream'] = Datastream::getDatastreamCachedFezACML($pid, $dsID);
    }
    else {
      $return['stream'] = $aws->getFileContent($dataPath, $dsID, $args, true, $getRaw);
    }

    return $return;
  }

  /**
   * This function creates an array of a specific datastream of a specific object
   *
   * @param string $pid The persistent identifier of the object
   * @param string $dsID The ID of the datastream
   * @param boolean $getRaw Get as xml
   * @param resource $filehandle
   * @param int $current_tries A counter of how many times this function has retried
   * @return mixed $resultlist The requested of datastream in an array.
   */
  public static function callGetDatastreamContents($pid, $dsID, $getRaw = FALSE, $filehandle = NULL, $current_tries = 0) {
    // $filehandle is a legacy arg left here to keep the API intact.

    $dsExists = Fedora_API::datastreamExists($pid, $dsID);
    $return = '';
    if ($dsExists) {

      if ($filehandle != NULL) {
        $return = Fedora_API::callGetDatastreamDissemination($pid, $dsID);
        $return = fwrite($filehandle, $return['stream']);
        return $return;
      }
      $dsMeta = Fedora_API::callGetDatastream($pid, $dsID);

      if ($dsMeta['MIMEType'] != 'text/xml' || $getRaw) {
        $return = Fedora_API::callGetDatastreamDissemination($pid, $dsID, "", $getRaw);
        $return = $return['stream'];
      }
      else {
        // do not implement until needed - this is probably dead - only happens twice in system, and not relevant for bypass
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
  public static function callGetDatastreamContentsField($pid, $dsID, $returnfields, $asOfDateTime = "") {
    static $counter;
    if (!isset($counter)) {
      $counter = 0;

    }
    $counter++;
    $resultlist = array();
    $dsExists = Fedora_API::datastreamExists($pid, $dsID);
    if ($dsExists == TRUE) {
      $xml = Fedora_API::callGetDatastreamDissemination($pid, $dsID, $asOfDateTime);
      $xml = $xml['stream'];
      if (!empty($xml) && $xml != FALSE) {
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
  public static function callModifyDatastreamByValue($pid, $dsID, $state, $label, $dsContent, $mimetype = 'text/xml', $versionable = 'inherit') {
    $log = FezLog::get();
    $tempFile = Misc::getFileTmpPath($dsID);
    $fp = fopen($tempFile, "w");
    if (fwrite($fp, $dsContent) === FALSE) {
      $err = "Cannot write to file ($tempFile)";
      $log->err(array($err, __FILE__, __LINE__));
      exit;
    }
    fclose($fp);

    self::callAddDatastream($pid, $dsID, $tempFile, $label, "A", $mimetype, "M", $versionable, '');
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
  public static function callModifyDatastreamByReference($pid, $dsID, $dsLabel, $dsLocation = NULL, $mimetype, $versionable = 'inherit') {
    $aws = AWS::get();
    $dataPath = Fedora_API::getDataPath($pid);
    $aws->rename($dataPath, $dsID, $dataPath, $dsID, $dsLabel);
  }

  /**
   * Changes the state and/or label of the object.
   *
   * @param string $pid The pid of the object
   * @param string $state The new state, A, I or D. Null means leave unchanged
   * @param string $label The new label. Null means leave unchanged
   * @param string $logMessage A log message
   */
  public static function callModifyObject($pid, $state, $label, $logMessage = 'Deleted by Fez') {
    // This does not need to be implemented because we don't store object state in s3, just datastreams
  }

  /**
   * This function marks a datastream as deleted by setting the state.
   *
   * @param string $pid The persistent identifier of the object
   * @param string $dsID The ID of the datastream
   * @return bool
   */
  public static function deleteDatastream($pid, $dsID) {
    $aws = AWS::get();

    Datastream::deleteDatastreamInfo($pid, $dsID);
    $dataPath = Fedora_API::getDataPath($pid);

    return $aws->deleteById($dataPath, $dsID);
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
  public static function callPurgeDatastream($pid, $dsID, $startDT = NULL, $endDT = NULL, $logMessage = "Purged Datastream from Fez", $force = FALSE) {
    $aws = AWS::get();

    if (Fedora_API::datastreamExists($pid, $dsID)) {
      Datastream::purgeDatastreamInfo($pid, $dsID);
      $dataPath = Fedora_API::getDataPath($pid);

      return $aws->purgeById($dataPath, $dsID);
    }
  }
}
