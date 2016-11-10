<?php

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

/**
 * This script is to allow smooth migration on Fez system from Fedora based record storaged to non-Fedora (database based).
 * The script ONLY supports removing Fedora for good, it is NOT intended for migrating the other way around.
 *
 * It executes individual migration scripts, such as:
 * - migrates existing record from Fedora,
 * - migrates attached datastreams,
 * - runs sanity checking
 *
 * @version 1.0, 2012-03-08
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */

include_once(APP_INC_PATH . 'class.bgp_index_object.php');
include_once(APP_INC_PATH . 'class.reindex.php');
include_once(APP_INC_PATH . 'class.record_object.php');

class MigrateFromFedoraToDatabase
{
  protected $_log = null;
  protected $_db = null;
  protected $_env = null;
  protected $_aws = null;
  protected $_shadowTableSuffix = "__shadow";
  protected $_tidy;

  public function __construct()
  {
    $this->_log = FezLog::get();
    $this->_db = DB_API::get();
    $this->_aws = AWS::get();
    $this->_tidy = new tidy;
  }

  public function runMigration()
  {
    define('APP_MIGRATION_RUN', true);
    $this->_env = strtolower($_SERVER['APP_ENVIRONMENT']);

    // Message/warning about backing up before running the migration script.
    $this->preMigration();

    // Updating the structure of Fedora-less
    echo "Step 1: Updating the structure of Fedora-less..";
    $this->stepOneMigration();
    echo "..done!\n";

    // Content migration
    echo "Step 2: Content migration:\n";
    $this->stepTwoMigration();
    echo "..done!\n";

    // De-dupe auth rules
    echo "Step 3: De-dupe auth rules..";
    $this->stepThreeMigration();
    echo "..done!\n";

    // Post Migration message
    $this->postMigration();
  }

  /**
   * Any pre-migration tasks here
   */
  private function preMigration()
  {
  }

  /**
   * At this stage, the Fez system has completed all the migration process,
   * and ready to switch off Fedora completely.
   */
  private function postMigration()
  {
    echo "Congratulations! Your Fez system is now ready to function without Fedora.\n";
  }

  /**
   * First round of migration, updating database schema.
   */
  private function stepOneMigration()
  {
    // Sets the maximum PID on PID index table.
    $this->setMaximumPID();
  }

  /**
   * Second round of migration, migrates existing content.
   * We could separate this into two steps:
   * 1. on all PIDS & managedContent, without outage.
   * 2. on updated PIDs / managedContents, after the #1 step.
   */
  private function stepTwoMigration()
  {
    // PID security
    $this->addPidsSecurity();

    // Datastream (attached files) migration
    echo " - Migrating managed content..";
    $this->migrateManagedContent();
    echo "..done!\n";
  }

  /**
   * Third round of migration, de-dupe the auth group rules
   */
  private function stepThreeMigration()
  {
    $stmt = "SELECT argr_arg_id, argr_ar_id FROM " . APP_TABLE_PREFIX . "auth_rule_group_rules";

    $rules = [];
    try {
      $res = $this->_db->fetchAssoc($stmt);
      if (empty($res)) {
        return true;
      }
      foreach ($res as $rule) {
        $rules[$rule['argr_arg_id'] . ':' . $rule['argr_ar_id']] = 1;
      }
      $stmt = "TRUNCATE " . APP_TABLE_PREFIX . "auth_rule_group_rules";
      $this->_db->exec($stmt);

    } catch (Exception $ex) {
      echo "\n<br /> Failed to fetch auth group table data. Error: " . $ex->getMessage();
      return false;
    }

    try {
      $this->_db->beginTransaction();
      $stmt = "TRUNCATE " . APP_TABLE_PREFIX . "auth_rule_group_rules";
      $this->_db->exec($stmt);

      $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "auth_rule_group_rules
                (`argr_arg_id`, `argr_ar_id`)
                VALUES ";
      foreach ($rules as $k => $v) {
        list($arg_id, $argr_ar_id) = explode(':', $k);
        $stmt .= "($arg_id, $argr_ar_id),";
      }
      $stmt = rtrim($stmt, ',');
      $this->_db->exec($stmt);
      $this->_db->commit();
      return true;

    } catch (Exception $ex) {
      $this->_db->rollBack();
      echo " - Failed to de-dupe auth group tables. Error: " . $ex->getMessage();
      return false;
    }
  }

  /**
   * Run Fedora managed content migration script & security for the attached files.
   */
  private function migrateManagedContent()
  {
    $fedoraDb = null;
    try {
      $fedoraDb = Zend_Db::factory(FEDORA_DB_TYPE, [
        'host'     => FEDORA_DB_HOST,
        'username' => FEDORA_DB_USERNAME,
        'password' => FEDORA_DB_PASSWD,
        'dbname'   => FEDORA_DB_DATABASE_NAME,
        'port'     => FEDORA_DB_PORT,
        'profiler' => ['enabled' => false],
      ]);
      $fedoraDb->getConnection();
    } catch (Exception $ex) {
      echo " - Unable to connect to the Fedora DB.\n";
      return;
    }

    ob_flush();
    if ($this->_env != 'production') {
      return;
    }

    $stmt = "select token, path from datastreamPaths order by path ASC";

    $ds = [];
    try {
      $ds = $fedoraDb->fetchAll($stmt, [], Zend_Db::FETCH_ASSOC);
      $fedoraDb->closeConnection();
    } catch (Exception $ex) {
      echo " - Failed to get datastreams from Fedora. Error: " . $ex;
    }

    $totalDs = count($ds);
    $counter = 0;

    foreach ($ds as $dataStream) {
      $counter++;

      $path = $dataStream['path'];
      $tokenParts = $this->getDsNameAndPidFromToken($dataStream['token']);
      $pid = $tokenParts['pid'];
      $dsName = $tokenParts['dsName'];
      $state = 'A';

      echo "\n - Doing PID $counter/$totalDs ($pid)\n";
      Zend_Registry::set('version', Date_API::getCurrentDateGMT());

      $FezACML_dsID = FezACML::getFezACMLDSName($dsName);
      $acml = false;
      if(
        !(Misc::hasPrefix($dsName, 'thumbnail_')
        || Misc::hasPrefix($dsName, 'MODS')
        || Misc::hasPrefix($dsName, 'FezACML_')
        || Misc::hasPrefix($dsName, 'FezComments')
        || Misc::hasPrefix($dsName, 'preview_')
        || Misc::hasPrefix($dsName, 'web_')
        || Misc::hasPrefix($dsName, 'stream_')
        || Misc::hasPrefix($dsName, 'presmd_'))
      ) {
        $acml = $this->getFezACML($pid, 'FezACML_' . $dsName . '.xml');
      }
      $acmlXml = '';
      if ($acml) {
        $acmlXml = $acml->saveXML();
      }
      if (!empty ($acmlXml)) {
        Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A",
          "FezACML security for datastream - " . $dsName,
          $acmlXml, "text/xml", "inherit");
      } else {
        Fedora_API::callPurgeDatastream($pid, $FezACML_dsID);
      }

      $dsInfo = Datastream::getFullDatastreamInfo($pid, $dsName, '_exported');

      if (! Fedora_API::datastreamExists($pid, $dsName)) {
        $mimeType = $this->quickMimeContentType($dsName);
        $location = 'migration/' . str_replace('/espace/data/fedora_datastreams/', '', $path);
        $location = str_replace('+', '%2B', $location);
        Fedora_API::callAddDatastream(
          $pid, $dsName, $location, '', $state,
          $mimeType, 'M', FALSE, "", FALSE, 'uql-fez-production-san'
        );
      }

      if (array_key_exists('dsi_pid', $dsInfo)) {
        Datastream::migrateDatastreamInfo([
          ':dsi_pid' => $pid,
          ':dsi_dsid' => $dsName,
          ':dsi_permissions' => $dsInfo['dsi_permissions'],
          ':dsi_embargo_date' => $dsInfo['dsi_embargo_date'],
          ':dsi_embargo_processed' => $dsInfo['dsi_embargo_processed'],
          ':dsi_open_access' => $dsInfo['dsi_open_access'],
          ':dsi_label' => $dsInfo['dsi_label'],
          ':dsi_copyright' => $dsInfo['dsi_copyright'],
          ':dsi_watermark' => $dsInfo['dsi_watermark'],
          ':dsi_security_inherited' => $dsInfo['dsi_security_inherited'],
        ]);
      }
    }

    // Remove datastream info shadow entries which don't have a url
    try {
      $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "datastream_info__shadow 
                   WHERE dsi_url='' OR dsi_url IS NULL";
      $this->_db->query($stmt);
    } catch (Exception $e) {}
  }

  /**
   * Update shadow key stamp with rek_updated_date in core SK table
   * Update rek_security_inherited from FezACML for the pids
   */
  private function addPidsSecurity()
  {
    $stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key";
    try {
      $pids = $this->_db->fetchCol($stmt);
    } catch (Exception $ex) {
      $this->_log->err($ex);
      echo " - Failed to retrieve pids. Error: " . $ex;
      return false;
    }

    $count = count($pids);
    $i = 0;
    foreach ($pids as $pid) {
      $i++;
      echo " - Updating security for $pid ($i/$count)\n";
      $acml = $this->getFezACML($pid, 'FezACML');
      if ($acml) {
        $dsID = FezACML::getFezACMLPidName($pid);
        $location = APP_TEMP_DIR . $dsID;
        file_put_contents($location, $acml);
        Fedora_API::callAddDatastream($pid, $dsID, $location,
          'FezACML security for PID - ' . $pid, 'A', 'text/xml');
        unlink($location);
      }
    }
    return true;
  }

  /**
   * Sets the maximum PID on PID index table.
   * Based on rm_fedora.php
   *
   * @return boolean
   */
  private function setMaximumPID()
  {
    // Get the maximum PID number from Fedora
    $nextPID = $this->getNextPID();

    if ($nextPID === FALSE) {
      return false;
    }
    $nextPIDParts = explode(":", $nextPID);
    $nextPIDNumber = (int)$nextPIDParts[1];

    // Make sure we have the pid index table
    $tableName = APP_TABLE_PREFIX . "pid_index";

    // truncating table
    // echo "truncating to pid_index table ... ";
    $stmt = "TRUNCATE " . $tableName . " ";
    $this->_db->exec($stmt);
    // echo "ok!\n";

    // Insert the maximum PID
    // echo "Fetching next PID from Fedora, and writing to pid_index table ... ";
    $stmt = "INSERT INTO " . $tableName . " (pid_number) VALUES ('" . ($nextPIDNumber - 1) . "');";
    $this->_db->exec($stmt);
    // echo "ok!\n";
    ob_flush();

    return true;
  }

  private function getDsNameAndPidFromToken($token)
  {
    $parts = explode('+', $token);
    return [
      'pid' => $parts[0],
      'dsName' => $parts[1]
    ];
  }

  private function getFezACML($pid, $dsID)
  {
    $result = Misc::processURL(APP_FEDORA_GET_URL . "/" . $pid . "/" . $dsID, false, null, null, null, 10, true);
    if ($result['success'] === 0) {
      return FALSE;
    }
    $xmlACML = $result['response'];
    if (! $xmlACML) {
      return FALSE;
    }
    $config = array(
      'indent' => TRUE,
      'input-xml' => TRUE,
      'output-xml' => TRUE,
      'wrap' => 0
    );
    $this->_tidy->parseString($xmlACML, $config, 'utf8');
    $this->_tidy->cleanRepair();
    $xmlACML = $this->_tidy;
    $xmlDoc = new DomDocument();
    $xmlDoc->preserveWhiteSpace = FALSE;
    $xmlDoc->loadXML($xmlACML);
    return $xmlDoc;
  }

  private function getNextPID() {
    $pid = FALSE;
    $getString = APP_SIMPLE_FEDORA_APIM_DOMAIN . "/objects/nextPID?format=xml";
    $ch = curl_init($getString);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://') {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }
    curl_setopt($ch, CURLOPT_USERPWD, APP_FEDORA_USERNAME . ":" . APP_FEDORA_PWD);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('format' => "xml"));
    $results = curl_exec($ch);
    if ($results) {
      curl_close($ch);
      $xml = $results;
      $dom = @DomDocument::loadXML($xml);
      if (!$dom) {
        return FALSE;
      }
      $result = $dom->getElementsByTagName("pid");
      foreach ($result as $item) {
        $pid = $item->nodeValue;
        break;
      }
    }
    else {
      curl_close($ch);
    }
    return $pid;
  }

  private function quickMimeContentType($filename) {

    $mime_types = array(

      'txt' => 'text/plain',
      'htm' => 'text/html',
      'html' => 'text/html',
      'php' => 'text/html',
      'css' => 'text/css',
      'js' => 'application/javascript',
      'json' => 'application/json',
      'xml' => 'application/xml',
      'swf' => 'application/x-shockwave-flash',
      'flv' => 'video/x-flv',

      // images
      'png' => 'image/png',
      'jpe' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpg' => 'image/jpeg',
      'gif' => 'image/gif',
      'bmp' => 'image/bmp',
      'ico' => 'image/vnd.microsoft.icon',
      'tiff' => 'image/tiff',
      'tif' => 'image/tiff',
      'svg' => 'image/svg+xml',
      'svgz' => 'image/svg+xml',

      // archives
      'zip' => 'application/zip',
      'rar' => 'application/x-rar-compressed',
      'exe' => 'application/x-msdownload',
      'msi' => 'application/x-msdownload',
      'cab' => 'application/vnd.ms-cab-compressed',

      // audio/video
      'mp3' => 'audio/mpeg',
      'qt' => 'video/quicktime',
      'mov' => 'video/quicktime',

      // adobe
      'pdf' => 'application/pdf',
      'psd' => 'image/vnd.adobe.photoshop',
      'ai' => 'application/postscript',
      'eps' => 'application/postscript',
      'ps' => 'application/postscript',

      // ms office
      'doc' => 'application/msword',
      'rtf' => 'application/rtf',
      'xls' => 'application/vnd.ms-excel',
      'ppt' => 'application/vnd.ms-powerpoint',

      // open office
      'odt' => 'application/vnd.oasis.opendocument.text',
      'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    $ext = strtolower(array_pop(explode('.',$filename)));
    if (array_key_exists($ext, $mime_types)) {
      return $mime_types[$ext];
    }
    else {
      return 'application/octet-stream';
    }
  }

  public function fixRekUpdatedDate() {
    $stmt = "SELECT rek_pid
                 FROM " . APP_TABLE_PREFIX . "record_search_key";
    $pids = [];
    try {
      $pids = $this->_db->fetchCol($stmt);
    } catch (Exception $e) {
    }

    $count = count($pids);
    $i = 0;
    foreach ($pids as $pid) {
      $i++;
      echo " - Updating $i/$count\n";
      try {
        $stmt = "UPDATE " . APP_TABLE_PREFIX . "record_search_key SET
                 rek_updated_date=(
                   SELECT pre_date FROM " . APP_TABLE_PREFIX . "premis_event
                   WHERE pre_pid=" . $this->_db->quote($pid)  . " ORDER BY pre_date DESC LIMIT 0,1
                 )
                 WHERE rek_pid=" . $this->_db->quote($pid)  . "
                   AND rek_pid IN (SELECT pre_pid FROM " . APP_TABLE_PREFIX . "premis_event 
                   WHERE pre_pid=" . $this->_db->quote($pid)  . ")";
        $this->_db->exec($stmt);
      } catch (Exception $e) {
        echo $e->getMessage() . "\n";
        exit;
      }
    }
  }

  public function getDatastreamLabels() {
    $stmt = "SELECT rek_pid
                 FROM " . APP_TABLE_PREFIX . "record_search_key";
    $pids = [];
    try {
      $pids = $this->_db->fetchCol($stmt);
    } catch (Exception $e) {
    }

    $count = count($pids);
    $i = 0;
    foreach ($pids as $pid) {
      $i++;
      echo " - Updating $i/$count\n";

      $datastreams = Fedora_API::callGetDatastreams($pid);
      foreach ($datastreams as $ds) {
        if (
          (is_numeric(strpos($ds['ID'], "thumbnail_")))
          || (is_numeric(strpos($ds['ID'], "MODS")))
          || (is_numeric(strpos($ds['ID'], "web_")))
          || (is_numeric(strpos($ds['ID'], "preview_")))
          || (is_numeric(strpos($ds['ID'], "presmd_")))
          || (is_numeric(strpos($ds['ID'], "stream_")))
          || (is_numeric(strpos($ds['ID'], "FezACML_")))
          || (is_numeric(strpos($ds['ID'], "FezComments")))
        ) {
          continue;
        }

        $object = [
          'url' => '',
          'size' => $ds['size'],
          'version' => '',
          'checksum' => ''
        ];
        Datastream::addDatastreamInfo($pid, $ds['ID'], $ds['MIMEType'], $object, $ds['state'], $ds['label']);
      }
    }
  }
}
