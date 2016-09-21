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

class MigrateFromFedoraToDatabase
{
  protected $_log = null;
  protected $_db = null;
  protected $_fedoraDb = null;
  protected $_env = null;
  protected $_shadowTableSuffix = "__shadow";

  public function __construct()
  {
    $this->_log = FezLog::get();
    $this->_db = DB_API::get();
    $this->_fedoraDb = DB_API::get('fedora_db');
  }

  public function runMigration()
  {
    $this->_env = strtolower($_SERVER['APP_ENVIRONMENT']);

    // Message/warning about backing up before running the migration script.
    $this->preMigration();

    // Updating the structure of Fedora-less
    $this->stepOneMigration();

    // Content migration
    $this->stepTwoMigration();

    // De-dupe auth rules
    $this->stepThreeMigration();

    // Post Migration message
    $this->postMigration();
  }

  /**
   * Any pre-migration tasks here
   */
  private function preMigration()
  {
    $this->toggleAwsStatus(false);
  }

  /**
   * At this stage, the Fez system has completed all the migration process,
   * and ready to switch off Fedora completely.
   */
  private function postMigration()
  {
    $this->toggleAwsStatus(true);
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
    // Migrate all records from Fedora
    $this->migratePIDs();

    // Datastream (attached files) migration
    $this->migrateManagedContent();
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
      echo "\n<br /> Failed to de-dupe auth group tables. Error: " . $ex->getMessage();
      return false;
    }
  }

  /**
   * Run Fedora managed content migration script & security for the attached files.
   */
  private function migrateManagedContent()
  {
    // @todo: Compare the new md5 with "existing" md5 if any.
    ob_flush();
    if ($this->_env != 'production') {
      return;
    }

    $stmt = "select op.token as pid, dr.systemVersion as version, 
        dr.objectState as state, ds.path as path from datastreamPaths ds
      left join objectPaths op on op.tokenDbID = ds.tokenDbID
      left join doRegistry dr on dr.doPID = op.token
      where ds.path like '/espace/data/fedora_datastreams/2016/08%'
      order by op.token ASC, dr.systemVersion ASC";

    $ds = [];
    try {
      $ds = $this->_fedoraDb->fetchAll($stmt);
    } catch (Exception $ex) {
      echo "Failed to retrieve exif data. Error: " . $ex;
    }

    $totalDs = count($ds);
    $counter = 0;

    foreach ($ds as $dataStream) {
      $counter++;
      $pid = $dataStream['pid'];

      echo "\nDoing PID $counter/$totalDs ($pid)\n";
      Zend_Registry::set('version', Date_API::getCurrentDateGMT());

      $path = $dataStream['path'];
      $state = $dataStream['state'];
      echo "Getting ACML from Fedora..\n";
      $dsName = $this->getDsNameFromPath($pid, $path);
      $acml = Record::getACML($pid, $dsName);
      echo "Got ACML from Fedora..\n";

      $cloneExif = true;
      if(
        strpos($dsName, 'presmd_') === 0
      ) {
        $exif = ['exif_mime_type' => 'application/xml'];
        $cloneExif = false;
      } else {
        $exif = Exiftool::getDetails($pid, $dsName);
        if (! $exif) {
          $cloneExif = false;
          $exif['exif_mime_type'] = 'binary/octet-stream';
        }
      }

      $this->toggleAwsStatus(true);
      $location = 'migration/' . str_replace('/espace/data/fedora_datastreams/', '', $path);

      if ($cloneExif) {
        Exiftool::cloneExif($pid, $dsName, $pid, $dsName, $exif);
      }

      echo "Adding datastream for {$dsName}..\n";
      Fedora_API::callAddDatastream(
        $pid, $dsName, $location, '', $state,
        $exif['exif_mime_type'], 'M', false, "", false, 'uql-fez-production-san'
      );

      $did = AuthNoFedoraDatastreams::getDid($pid, $dsName);
      if ($this->inheritsPermissions($acml)) {
        AuthNoFedoraDatastreams::setInherited($did);
      }
      if ($acml) {
        $this->addDatastreamSecurity($acml, $did);
      }
      AuthNoFedoraDatastreams::recalculatePermissions($did);
      $this->toggleAwsStatus(false);
    }
  }

  /**
   * Call bulk 'reindex' workflow on all PIDS.
   * We could runs this function before the outage.
   * eSpace has around 165K records, so here we are, running it in phases.
   */
  private function migratePIDs()
  {
    $stmt = "SELECT COUNT(rek_pid) FROM " . APP_TABLE_PREFIX . "record_search_key
                 ORDER BY rek_pid DESC ";

    try {
      $totalPids = $this->_db->fetchCol($stmt);
    } catch (Exception $e) {
      echo chr(10) . "\n<br /> Failed to retrieve total pids. Query: " . $stmt;
      return false;
    }

    $limit = 1;  // how many pids per process
    $loop = 2;  // how many times we want to loop
    $start = 0;
    for ($i = 0; $start < $totalPids && $i < $loop; $i++) {
      if ($i == 0) {
        $start = $i;
      }
      $this->reindexPids($start, $limit);
      $start += $limit;
    }

    // echo chr(10) . "\n<br /> Ok, we have done the reindex for ". ($loop * $limit) . "PIDs";
    ob_flush();
    return true;

    // Attempt to bring in all the versions of a PID
    /*$stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key
                 ORDER BY rek_pid DESC ";
    try {
      $pids = $this->_db->fetchCol($stmt);
    } catch (Exception $e) {
      echo chr(10) . "\n<br /> Failed to retrieve pids. Query: " . $stmt;
      return false;
    }
    foreach ($pids as $pid) {
      $datastreams = Fedora_API::callGetDatastreams($pid, null, 'A');
      $createdDates = $this->generateDSTimestamps($pid, $datastreams);
      array_pop($createdDates);
      $createdDates[] = null;

      foreach ($createdDates as $createDT) {
        $this->toggleAwsStatus(true);
        $command = APP_PHP_EXEC . " \"" . APP_PATH . "upgrade/fedoraBypassMigration/migrate_pid_versions.php\" \"" .
          $pid . "\" \"" . $createDT . "\"";
        exec($command, $output);
        print_r($output);
        $this->toggleAwsStatus(false);
      }
    }*/
  }

  /**
   * Run Reindex workflow on an array of pids
   * @return boolean
   */
  private function reindexPids($start, $limit)
  {
    $wft_id = 277;  // hack: Reindex workflow trigger ID
    $pid = "";
    $xdis_id = "";
    $href = "";
    $dsID = "";

    // Test PID for reindex
    /*
    $pids    = array("UQ:87692");
    Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);
    */

    $stmt = "SELECT rek_pid
                 FROM " . APP_TABLE_PREFIX . "record_search_key
                 ORDER BY rek_pid DESC
                 LIMIT " . $start . ", " . $limit . ";";

    try {
      $pids = $this->_db->fetchCol($stmt);
    } catch (Exception $e) {
      echo chr(10) . "<br /> Failed to retrieve pids. Query: " . $stmt;
      return false;
    }


    if (sizeof($pids) > 0) {
      //Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);

      // echo chr(10) . "\n<br /> BGP of Reindexing the PIDS has been triggered.
      //     See the progress at http://" . APP_HOSTNAME . "/my_processes.php";
    }
    ob_flush();
    return true;
  }

  /**
   * Recalculate security.
   */
  private function recalculateSecurity()
  {
    // Get all PIDs without parents. Recalculate permissions. This will filter down to child pids and child datastreams
    $stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key
      LEFT JOIN fez_record_search_key_ismemberof
      ON rek_ismemberof_pid = rek_pid
      WHERE rek_ismemberof IS NULL";
    $res = [];
    try {
      $res = $this->_db->fetchAll($stmt);
    } catch (Exception $ex) {
      $this->_log->err($ex);
      echo "Failed to retrieve pid data. Error: " . $ex;
    }
    foreach ($res as $pid) {
      AuthNoFedora::recalculatePermissions($pid);
      echo 'Done: '.$pid.'<br />';
    }
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
    $nextPID = '';
    while (empty($nextPID)) {
      $nextPID = Fedora_API::getNextPID(false);
      // Fedora may still be initialising
      if (empty($nextPID)) {
        sleep(10);
      }
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

  private function toggleAwsStatus($useAws)
  {
    $db = DB_API::get();

    if ($useAws) {
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = 'true' " .
        " WHERE config_name='aws_enabled'");
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = 'true' " .
        " WHERE config_name='aws_s3_enabled'");
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = 'ON' " .
        " WHERE config_name='app_fedora_bypass'");
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = 'ON' " .
        " WHERE config_name='app_xsdmf_index_switch'");
      /*$db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = '' " .
        " WHERE config_name='app_fedora_username'");
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = '' " .
        " WHERE config_name='app_fedora_pwd'");*/

    } else {
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = 'false' " .
        " WHERE config_name='aws_enabled'");
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = 'false' " .
        " WHERE config_name='aws_s3_enabled'");
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = 'OFF' " .
        " WHERE config_name='app_fedora_bypass'");
      $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
        " SET config_value = 'OFF' " .
        " WHERE config_name='app_xsdmf_index_switch'");
    }
  }

  private function getDsNameFromPath($pid, $path)
  {
    $pidMatch = str_replace(':', '_', $pid);
    preg_match("/\\/$pidMatch\\+([^\\+]*)\\+/", $path, $matches);

    if (count($matches) !== 2) {
      return false;
    }
    return $matches[1];
  }

  private function inheritsPermissions($acml)
  {
    if ($acml == false) {
      //if no acml then default is inherit
      $inherit = true;
    } else {
      $xpath = new DOMXPath($acml);
      $inheritSearch = $xpath->query('/FezACML[inherit_security="on"]');
      $inherit = false;
      if ($inheritSearch->length > 0) {
        $inherit = true;
      }
    }
    return $inherit;
  }

  private function addDatastreamSecurity($acml, $did)
  {
    // loop through the ACML docs found for the current pid or in the ancestry
    $xpath = new DOMXPath($acml);
    $roleNodes = $xpath->query('/FezACML/rule/role');

    foreach ($roleNodes as $roleNode) {
      $role = $roleNode->getAttribute('name');
      // Use XPath to get the sub groups that have values
      $groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0]', $roleNode);

      /* todo
       * Empty rules override non-empty rules. Example:
       * If a pid belongs to 2 collections, 1 collection has lister restricted to fez users
       * and 1 collection has no restriction for lister, we want no restrictions for lister
       * for this pid.
       */

      foreach ($groupNodes as $groupNode) {
        $group_type = $groupNode->nodeName;
        $group_values = explode(',', $groupNode->nodeValue);
        foreach ($group_values as $group_value) {

          //off is the same as lack of, so should be the same
          if ($group_value != "off") {
            $group_value = trim($group_value, ' ');

            $arId = AuthRules::getOrCreateRule("!rule!role!" . $group_type, $group_value);
            AuthNoFedoraDatastreams::addSecurityPermissions($did, $role, $arId);
          }
        }
      }
    }
  }
}
