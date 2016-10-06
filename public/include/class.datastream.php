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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . 'class.record.php');
include_once(APP_INC_PATH . 'class.fezacml.php');

/**
 * Class to handle datastreams
 */
class Datastream
{

  //Any changes here must be mirrored in swfuploader.js and changes to closed access (Currently only option 5) must be changed in security changes functions applied off these and javascript in upload_files.tpl.html
  public static $file_options = array(0 => 'Please choose file type', 1 => 'Accepted version (author final draft  post-refereeing)', 2 => 'Submitted version (author version pre-refereeing)',
    3 => 'Publishers Copy (Open Access)', 4 => 'Working/Technical Paper', 5 => 'HERDC evidence (not open access- admin only)', 6 => 'Data Collection (Open Access)',
    8 => 'Non-traditional research output Research Statement (Admin only)', 7 => 'Other (any files not included in any of the above)'
  );

  //Add a datastream to a pid
  //$newFile is the file name in the temporary upload directory
  //$filesFezACMLNum the security template number applied to the datastream, if empty it defaults to default
  public static function addDatastreamToPid($pid, $newFile, $description, $fezACMLTemplateNum = null)
  {
    $log = FezLog::get();
    if (!empty($newFile)) {
      if (Fedora_API::datastreamExists($pid, $newFile) && APP_VERSION_UPLOADS_AND_LINKS != "ON") {
        $newFileName = $newFile . time();
        //Fedora_API::callPurgeDatastream($pid, $newFile);
      } else {
        $newFileName = $newFile;
      }
      $deleteFile = APP_TEMP_DIR . $newFile;
      $newFile = APP_TEMP_DIR . $newFile;
      if (file_exists($newFile)) {
        $mimetype = Misc::mime_content_type($newFile);
        $versionable = APP_VERSION_UPLOADS_AND_LINKS == "ON" ? 'true' : 'false';
        Fedora_API::getUploadLocationByLocalRef($pid, $newFileName, $newFile, $description, $mimetype, 'M', null, $versionable);
        Exiftool::saveExif($pid, $newFileName);
        if (is_integer($fezACMLTemplateNum)) {
          Datastream::setfezACML($pid, $newFileName, $fezACMLTemplateNum);
        }
        Workflow::processIngestTrigger($pid, $newFileName, $mimetype);
        Record::generatePresmd($pid, $newFileName);
        if (APP_FEDORA_BYPASS != 'ON') {
          Record::setIndexMatchingFields($pid);
        }
        if (is_file($newFile)) {
          $deleteCommand = APP_DELETE_CMD . " " . $deleteFile;
          exec($deleteCommand);
        }
      } else {
        $log->err("File not created $newFile<br/>\n", __FILE__, __LINE__);
      }
    }
  }

  /**
   * Set the security on the datastream directly using fezACML xml.
   *
   * @param string $xml A valid fezACML xml document
   */
  static function setfezACMLXml($pid, $dsID, $xml)
  {
    $FezACML_dsID = FezACML::getFezACMLDSName($dsID);
    if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
      Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A", "FezACML security for datastream - " . $dsID,
        $xml, "text/xml", "true");
    } else {
      Fedora_API::getUploadLocation($pid, $FezACML_dsID, $xml, "FezACML security for datastream - " . $dsID,
        "text/xml", "X", null, "true");
    }
  }

  static function setfezACML($pid, $dsID, $fezACMLTemplateNum)
  {
    if (APP_FEDORA_BYPASS == 'ON') {
      $did = self::getDid($pid, $dsID);
      return FezACML::updateDatastreamQuickRule($pid, $fezACMLTemplateNum, $did);
    }
    $xmlObj = FezACML::getQuickTemplateValue($fezACMLTemplateNum);
    if ($xmlObj != false) {
      return self::setfezACMLXml($pid, $dsID, $xmlObj);
    }

  }

  /**
   * Stores the datastream in the dastream_info table
   * @param string $pid The persistent identifier of the object to be purged
   * @param string $dsName The name of the datastream
   * @param string $mimetype The mimetype of the datastream
   * @param string $state The datastream state
   * @param array $object The object in S3
   * @return integer The datastream ID
   */
  public static function addDatastreamInfo($pid, $dsName, $mimetype, $object, $state)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $data = [
      ':dsi_dsid' => $dsName,
      ':dsi_pid' => $pid,
      ':dsi_mimetype' => $mimetype,
      ':dsi_url' => $object['url'],
      ':dsi_state' => $state,
      ':dsi_size' => $object['size'],
      ':dsi_version' => $object['version'],
      ':dsi_checksum' => $object['checksum'],
    ];

    $did = self::getDid($pid, $dsName);

    if ($did) {
      $stmt = "UPDATE " . APP_TABLE_PREFIX . "datastream_info SET
                  dsi_mimetype = :dsi_mimetype,
                  dsi_url = :dsi_url,
                  dsi_state = :dsi_state,   
                  dsi_size = :dsi_size,  
                  dsi_version = :dsi_version,
                  dsi_checksum = :dsi_checksum
                  WHERE dsi_pid = :dsi_pid AND dsi_dsid = :dsi_dsid";
    } else {
      $data[':dsi_security_inherited'] = 0;
      $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "datastream_info "
        . "(dsi_dsid, dsi_pid, dsi_mimetype, dsi_url, dsi_security_inherited, dsi_state, dsi_size, dsi_version, dsi_checksum) VALUES "
        . "(:dsi_dsid, :dsi_pid, :dsi_mimetype, :dsi_url, :dsi_security_inherited, :dsi_state, :dsi_size, :dsi_version, :dsi_checksum)";
    }
    try {
      $db->query($stmt, $data);
      if (! $did) {
        $did = $db->lastInsertId(APP_TABLE_PREFIX . "datastream_info", "dsi_id");
      }
    } catch (Exception $ex) {
      $log->err($ex);
    }

    if (!Zend_Registry::isRegistered('version')) {
      Zend_Registry::set('version', Date_API::getCurrentDateGMT());
    }
    $date = Zend_Registry::get('version');
    $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "datastream_info__shadow
               SELECT *, " . $db->quote($date) . " FROM " . APP_TABLE_PREFIX . "datastream_info
                        WHERE dsi_id = " . $db->quote($did);
    try {
      $db->query($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
    }

    return $did;
  }

  public static function getDid($pid, $dsName)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT dsi_id FROM " . APP_TABLE_PREFIX . "datastream_info WHERE "
      . "dsi_pid = :dsi_pid AND dsi_dsid = :dsi_dsid";
    try {
      $did = $db->fetchOne($stmt, [
        ':dsi_pid' => $pid,
        ':dsi_dsid' => $dsName,
      ]);
      return $did;

    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
  }

  /**
   * This function creates an array of all the datastreams for a specific object
   *
   * @param string $pid The persistent identifier of the object
   * @return array $dsIDListArray The list of datastreams in an array.
   */
  public static function getDatastreamInfo($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (!is_numeric($pid)) {

      $rows = array();

      $sql = "SELECT dsi_dsid, dsi_mimetype FROM "
        . APP_TABLE_PREFIX . "datastream_info WHERE dsi_pid = :dsi_pid GROUP BY dsi_dsid";

      try
      {
        $stmt = $db->query($sql, array(':dsi_pid' => $pid));
        $rows = $stmt->fetchAll();
      }
      catch(Exception $e)
      {
        $log->err($e->getMessage());
      }

      $resultlist = array();
      foreach($rows as $row)
      {
        $resultlist[] = array('dsid' => $row['dsi_dsid'],
          'label' => $row['dsi_dsid'],
          'mimeType' => $row['dsi_mimetype']);
      }
      return $resultlist;
    } else {
      return array();
    }
  }

  /**
   * This function creates an array of all the datastreams for a specific object
   *
   * @param string $pid The persistent identifier of the object
   * @param string $dsID The ID of the datastream
   * @return array $dsIDListArray The list of datastreams in an array.
   */
  public static function getFullDatastreamInfo($pid, $dsID = '')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $res = [];
    $data = [':dsi_pid' => $pid];
    $sql = "SELECT * FROM "
      . APP_TABLE_PREFIX . "datastream_info WHERE dsi_pid = :dsi_pid";

    if ($dsID !== '') {
      $data['dsi_dsid'] = $dsID;
      $sql .= " AND dsi_dsid = :dsi_dsid";
    }
    try
    {
      $stmt = $db->query($sql, $data);
      $res = $stmt->fetchAll();
    }
    catch(Exception $e) {
      $log->err($e->getMessage());
    }

    return $res;
  }

  /**
   * This function marks a datastream as deleted by setting the state.
   *
   * @param string $pid The persistent identifier of the object
   * @param string $dsID The ID of the datastream
   * @return bool
   */
  public static function deleteDatastreamInfo($pid, $dsID)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    try {
      $sql = "UPDATE " . APP_TABLE_PREFIX . "datastream_info SET dsi_state = :dsi_state WHERE "
        . "dsi_dsid = :dsi_dsid AND dsi_pid = :dsi_pid";
      $db->query($sql, [
        ':dsi_state' => 'D',
        ':dsi_dsid' => $dsID,
        ':dsi_pid' => $pid
      ]);
    } catch (Exception $e) {
      $log->err($e->getMessage());
    }
  }

  /**
   * This function deletes a datastream
   *
   * @param string $pid The persistent identifier of the object to be purged
   * @param string $dsID The name of the datastream
   * @return bool
   */
  public static function purgeDatastreamInfo($pid, $dsID)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    try {
      $sql = "DELETE FROM " . APP_TABLE_PREFIX . "datastream_info WHERE "
        . "dsi_dsid = :dsi_dsid AND dsi_pid = :dsi_pid";
      $db->query($sql, [
        ':dsi_dsid' => $dsID,
        ':dsi_pid' => $pid
      ]);
    } catch (Exception $e) {
      $log->err($e->getMessage());
    }
  }

  /**
   * Set the security on the datastream to inherit from parent.
   */
  static function setfezACMLInherit($pid, $dsID)
  {
    $xml = FezACML::makeQuickTemplateInherit();
    return self::setfezACMLXml($pid, $dsID, $xml);
  }

  //Removes permissions on datastream which makes it open access if the pid is accessible.
  static function removeFezACMLDatastream($pid, $dsID)
  {
    $FezACML_dsID = FezACML::getFezACMLDSName($dsID);
    return Fedora_API::callPurgeDatastream($pid, $FezACML_dsID);
  }

  //Saves $permissions and $embargo date. Does nothing and returns with true if both are unchanged
  public static function saveDatastreamSelectedPermissions($pid, $dsId, $permissions, $embargoDate)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (!empty($embargoDate)) {
      //Make sure the date is in English time -> mysql time
      $embargoDate = str_replace('/', '-', $embargoDate);
      $phpdate = strtotime($embargoDate);
      $embargoDate = date('Y-m-d H:i:s', $phpdate);
    } else {
      $embargoDate = 'NULL';
    }

    if (empty($pid) || empty($dsId)) {
      $log->err("saveDatastreamSelectedPermissions called with blank data. pid: " . $pid . " dsId: " . $dsId);
      return false;
    }

    $stmt = "
			SELECT * FROM " . APP_TABLE_PREFIX . "datastream_info
            WHERE dsi_pid = " . $db->quote($pid) . " AND dsi_dsid = " . $db->quote($dsId);

    try {
      $res = $db->fetchRow($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
    if ($embargoDate != 'NULL') {
      $embargoDate = $db->quote($embargoDate);
    }
    if (is_array($res)) {
      if ($res['dsi_permissions'] == $permissions && $res['dsi_embargo_date'] == $embargoDate) {
        return true;
      } else {
        $stmt = "UPDATE " . APP_TABLE_PREFIX . "datastream_info SET
                    dsi_permissions = " . $db->quote($permissions) . ",
                    dsi_embargo_date = " . $embargoDate . "
                    WHERE dsi_pid = " . $db->quote($pid) . " AND dsi_dsid = " . $db->quote($dsId);
        $historyDetail = 'Update ' . $permissions . ' to ' . $dsId;

      }
    } else {
      $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "datastream_info (dsi_pid, dsi_dsid, dsi_permissions, dsi_embargo_date)
                    VALUES (" . $db->quote($pid) . "," . $db->quote($dsId) . "," . $db->quote($permissions) . "," . $embargoDate . ")";
      $historyDetail = 'Add ' . $permissions . ' to ' . $dsId;
    }

    try {
      $res = $db->exec($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return array();
    }

    History::addHistory($pid, null, "", "", false, $historyDetail);
    return $res;
  }

  function getClassification($pid, $dsId)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
			SELECT dsi_permissions FROM " . APP_TABLE_PREFIX . "datastream_info
            WHERE dsi_pid = " . $db->quote($pid) . " AND dsi_dsid = " . $db->quote($dsId);

    try {
      $res = $db->fetchOne($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  static function getEmbargoDate($pid, $dsId)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
			SELECT dsi_embargo_date FROM " . APP_TABLE_PREFIX . "datastream_info
            WHERE dsi_pid = " . $db->quote($pid) . " AND dsi_dsid = " . $db->quote($dsId);

    try {
      $res = $db->fetchOne($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  static function setEmbargoProcessed($pid, $dsId)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
			UPDATE " . APP_TABLE_PREFIX . "datastream_info SET dsi_embargo_processed = 1
            WHERE dsi_pid = " . $db->quote($pid) . " AND dsi_dsid = " . $db->quote($dsId);

    try {
      $res = $db->exec($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  static function embargoFileRename($pid, $dsIdOld, $dsIdNew)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
			UPDATE " . APP_TABLE_PREFIX . "datastream_info SET dsi_dsid = " . $db->quote($dsIdNew) . "
            WHERE dsi_pid = " . $db->quote($pid) . " AND dsi_dsid = " . $db->quote($dsIdOld);

    try {
      $res = $db->exec($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }
}