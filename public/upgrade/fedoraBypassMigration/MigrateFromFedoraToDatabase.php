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
  protected $_fedoraDb = null;
  protected $_env = null;
  protected $_shadowTableSuffix = "__shadow";
  protected $_tidy;

  public function __construct()
  {
    $this->_log = FezLog::get();
    $this->_db = DB_API::get();
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
    if (! Zend_Registry::isRegistered('fedora_db')) {
      try {
        $fdb = Zend_Db::factory(FEDORA_DB_TYPE, [
          'host'     => FEDORA_DB_HOST,
          'username' => FEDORA_DB_USERNAME,
          'password' => FEDORA_DB_PASSWD,
          'dbname'   => FEDORA_DB_DATABASE_NAME,
          'port'     => FEDORA_DB_PORT,
          'profiler' => ['enabled' => false],
        ]);
        $fdb->getConnection();
        Zend_Registry::set('fedora_db', $fdb);
      } catch (Exception $ex) {
        echo " - Unable to connect to the Fedora DB.\n";
        return;
      }
    }
    $this->_fedoraDb = DB_API::get('fedora_db');
  }

  /**
   * At this stage, the Fez system has completed all the migration process,
   * and ready to switch off Fedora completely.
   */
  private function postMigration()
  {
    //$this->reindexPids();
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
    ob_flush();
    if ($this->_env != 'production') {
      return;
    }

    $stmt = "select token, path from datastreamPaths   
      where path not like '/espace/data/fedora_datastreams/2016/%'
      order by path DESC";

    $ds = [];
    try {
      $ds = $this->_fedoraDb->fetchAll($stmt, [], Zend_Db::FETCH_ASSOC);
    } catch (Exception $ex) {
      echo " - Failed to retrieve exif data. Error: " . $ex;
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

      echo "\n\n\n - Doing PID $counter/$totalDs ($pid)\n";
      Zend_Registry::set('version', Date_API::getCurrentDateGMT());

      $acml = $this->getFezACML($pid, 'FezACML_' . $dsName . '.xml');
      if ($acml) {
        Fedora_API::callModifyDatastreamByValue($pid, 'FezACML_' . $dsName, "A", "FezACML",
          $acml->saveXML(), "text/xml", "inherit");
      }

      if(
        strpos($dsName, 'presmd_') === 0
      ) {
        $exif = ['exif_mime_type' => 'application/xml'];
      } else {
        $exif = Exiftool::getDetails($pid, $dsName);
        if (! $exif) {
          $exif['exif_mime_type'] = 'binary/octet-stream';
        }
      }

      $location = 'migration/' . str_replace('/espace/data/fedora_datastreams/', '', $path);
      $location = str_replace('+', '%2B', $location);

      echo "Adding datastream for {$dsName}..\n";
      Fedora_API::callAddDatastream(
        $pid, $dsName, $location, '', $state,
        $exif['exif_mime_type'], 'M', false, "", false, 'uql-fez-production-san'
      );
    }
  }

  /**
   * Run Reindex workflow on an array of pids
   * @return boolean
   */
  private function reindexPids()
  {
    $wft_id = 277;  // hack: Reindex workflow trigger ID
    $pid = "";
    $xdis_id = "";
    $href = "";
    $dsID = "";

    $stmt = "SELECT rek_pid
                 FROM " . APP_TABLE_PREFIX . "record_search_key
                 ORDER BY rek_pid DESC";

    try {
      $pids = $this->_db->fetchCol($stmt);
    } catch (Exception $e) {
      echo " - Failed to retrieve pids. Query: " . $stmt;
      return false;
    }

    if (sizeof($pids) > 0) {
      Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);
    }
    ob_flush();
    return true;
  }

  /**
   * Update shadow key stamp with rek_updated_date in core SK table
   * Update rek_security_inherited from FezACML for the pids
   */
  public function addPidsSecurity()
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
        $location = APP_TEMP_DIR . 'FezACML-' . $pid . '.xml';
        file_put_contents($location, $acml);
        Fedora_API::callAddDatastream($pid, 'FezACML', $location, '', 'A', 'text/xml');
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
    echo " - Getting FezACML for $pid/$dsID\n";
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
}
