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
 * This script is to allow smooth migration on Fez system
 * from Fedora based record storaged to non-Fedora (database based).
 * It script ONLY supports removing Fedora for good, it is NOT intended for migrating the other way around.
 *
 * It executes individual migration scripts, such as:
 * - build/alter database schema,
 * - migrates existing record from Fedora XML,
 * - migrates attached files,
 * - runs sanity checking or run related test cases
 *
 * @version 1.0, 2012-03-08
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
include_once(APP_INC_PATH . "class.upgrade.php");
include_once(APP_INC_PATH . 'class.bgp_index_object.php');
include_once(APP_INC_PATH . 'class.reindex.php');

class MigrateFromFedoraToDatabase
{
  protected $_log = null;
  protected $_db = null;
  protected $_env = null;
  protected $_shadowTableSuffix = "__shadow";
  protected $_upgradeHelper = null;
  protected $_config = null;

  public function __construct($config = array())
  {
    $this->_config = new stdClass();
    $this->_log = FezLog::get();
    $this->_db = DB_API::get();
    $this->_upgradeHelper = new upgrade();

    $this->_parseConfig($config);
  }

  public function runMigration()
  {
    $this->_env = strtolower($_SERVER['APPLICATION_ENV']);

    // Message/warning about the checklist required before running the migration script.
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
   * Parse class configuration param.
   * @param array $config
   * @return boolean
   */
  protected function _parseConfig($config = array())
  {
    if (!is_array($config)) {
      return false;
    }

    foreach ($config as $cfg) {
      $cfg = explode("=", $cfg);
      if (!array_key_exists(0, $cfg) || !array_key_exists(1, $cfg)) {
        continue;
      }

      $key = $cfg[0];
      $value = $cfg[1];

      switch ($key) {
        case 'autoMapXSDFields':
          $this->_config->$key = (bool)$value;
          break;
      }
    }
  }

  /**
   * This step is to inform/warn/scare web administrator on what they are about to do.
   */
  public function preMigration()
  {
    /* echo chr(10) . "\n<br /> Before running this migration script,
        please make sure you have gone through the following checklist.
        There is no way to revert the system once this script executed,
        so make sure you have backup system to rollback to in the case of migration failure.
        <ul>
            <li> Merged fedora_bypass branch to the trunk. </li>
            <li> BACKUP your DATABASE, and extra BACKUP for rollback.</li>
            <li> Did we mention BACKUP? </li>
            <li> Mapped all the XSD fields to the search keys accordingly.
                 Refer to mapXSDFieldToSearchKey method for sample query </li>
            <li> ... </li>
        </ul>
     ";*/

    // Executes mapXSDFields methods, when specified.
    // This is considering that the sk on mapping methods match your system.
    // This method is created to support UQ eSpace Fez.
    if (property_exists($this->_config, 'autoMapXSDFields') && $this->_config->autoMapXSDFields === true) {
      $this->mapXSDFieldToSearchKey();
      $this->addSearchKeys();
    }
  }

  /*
   * At this stage, the Fez system has completed all the migration process,
   * and ready to switch off Fedora completely.
   *
   */
  public function postMigration()
  {
    $db = DB_API::get();

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
    $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
      " SET config_value = '' " .
      " WHERE config_name='app_fedora_username'");
    $db->query("UPDATE " . APP_TABLE_PREFIX . "config " .
      " SET config_value = '' " .
      " WHERE config_name='app_fedora_pwd'");

    echo "Congratulations! Your Fez system is now ready to function without Fedora.\n";
  }

  /**
   * First round of migration, updating database schema.
   */
  public function stepOneMigration()
  {
    // Create shadow tables for search keys
    $this->createSearchKeyShadowTables();

    // Create file attachment and auth quick rules tables
    $this->createAdditionalTables();

    // Upgrade table schema for all datastream permissions and pid non inherited permissions
    $this->updateForDatastreamPermission();

    // Sets the maximum PID on PID index table.
    $this->setMaximumPID();
  }


  /**
   * Second round of migration, migrates existing content.
   * We could separate this into two steps:
   * 1. on all PIDS & managedContent, without outage.
   * 2. on updated PIDs / managedContents, after the #1 step.
   */
  public function stepTwoMigration()
  {
    // Migrate all records from Fedora
    $this->migratePIDs();

    // Datastream (attached files) migration
    $this->migrateManagedContent();
  }

  /**
   * Fourth round of migration, de-dupe the auth group rules
   */
  public function stepThreeMigration()
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
   * This is the last step of migration
   * moving the following files:
   * // mp3 -> flv
   * // book -> jpg, used by bookreader
   * These files are auto generated when a PID is created, we need a way to be able to read/convert these files without Fedora.
   * Ref: eserv.php
   */
  public function stepLASTMigration()
  {
    // On libtools run the util/s3_sync_pidimages.sh script
    echo "\n On libtools run the util/s3_sync_pidimages.sh script";
  }

  /**
   * This method automatically map XSD fields that we can manually find.
   * Some manual mapping will be required depending on the XSD field on your Fez application.
   */
  public function mapXSDFieldToSearchKey()
  {

    $this->_mapSubjectField();

    // @todo: need to find out what search key should we map the Q Index code field.
    // $this->_mapQIndexCode();
  }

  protected function _mapQIndexCode()
  {
    // Q-index code, Q-index status, Institutional status, collection year and year available dropdowns are not being saved.
    /*
    SELECT xdis_title, fez_xsd_display_matchfields.*
    FROM fez_xsd_display_matchfields
    LEFT JOIN fez_xsd_display ON xsdmf_xdis_id = xdis_id
    WHERE xsdmf_html_input = 'contvocab_selector' AND xsdmf_sek_id IS NULL;
    */

    // Q-index code = HERDC Code ?
    // Institutional Status

    /*
    -- Query to find Subject Controlled Vocab  XSD fields that do not have search keys --
    SELECT xdis_title, fez_xsd_display_matchfields.*
    FROM fez_xsd_display_matchfields
    LEFT JOIN fez_xsd_display ON xsdmf_xdis_id = xdis_id
    WHERE xsdmf_title LIKE "%subject%"
    AND xsdmf_html_input = 'contvocab_selector';
    */

    $stmt = " UPDATE " . APP_TABLE_PREFIX . "xsd_display_matchfields
                    SET xsdmf_sek_id = (SELECT sek_id FROM " . APP_TABLE_PREFIX . "search_key WHERE sek_title = 'Subject')
                    WHERE xsdmf_title LIKE '%subject%'
                    AND xsdmf_html_input = 'contvocab_selector';";

    try {
      $this->_db->exec($stmt);
    } catch (Exception $ex) {
      echo "\n<br />Failed to map XSD field. Here is why: " . $stmt . " \n<br />" . $ex . ".\n";
      return false;
    }
    return true;
  }

  protected function _mapSubjectField()
  {
    // Check if Subject XSD fields have been mapped
    $stmt = "SELECT COUNT(xsdmf_id) as howmany
                 FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields
                 LEFT JOIN " . APP_TABLE_PREFIX . "xsd_display ON xsdmf_xdis_id = xdis_id
                 WHERE xsdmf_title LIKE '%subject%'
                    AND xsdmf_html_input = 'contvocab_selector'
                    AND (xsdmf_sek_id IS NULL OR xsdmf_sek_id = '');";

    $unmappedFields = $this->_db->fetchRow($stmt);


    // Run update query for unmapped Subject fields
    if (array_key_exists('howmany', $unmappedFields) && $unmappedFields['howmany'] > 0) {

      $stmt = "UPDATE " . APP_TABLE_PREFIX . "xsd_display_matchfields
                     SET xsdmf_sek_id = (SELECT sek_id FROM " . APP_TABLE_PREFIX . "search_key WHERE sek_title = 'Subject')
                     WHERE xsdmf_title LIKE '%subject%'
                        AND xsdmf_html_input = 'contvocab_selector'
                        AND (xsdmf_sek_id IS NULL OR xsdmf_sek_id = '');";

      try {
        $this->_db->exec($stmt);
        // echo chr(10) . "\n<br /> Successfully mapped subject " . print_r($unmappedFields, 1);
      } catch (Exception $ex) {
        echo "\n<br />Failed to map XSD field. Here is why: " . $stmt . " <br />" . $ex . ".\n";
        return false;
      }
    }
    return true;
  }

  /**
   * Adds search key columns / tables, which data have not been recorded in database.
   * This method is specific for eSpace.
   * Manual process may required to fit your Fez application.
   */
  public function addSearchKeys()
  {
    $this->_addSearchKeysCopyright();
  }

  /**
   * Add search key for 'copyright' field on core search key table.
   * We don't need to add it on shadow table,
   *    as _createOneShadow() method takes care of sk table duplication.
   *
   * @return boolean
   */
  protected function _addSearchKeysCopyright()
  {
    // Add Search Key table
    $stmtAddSearchKey = "INSERT INTO " . APP_TABLE_PREFIX . "search_key
                (`sek_id`, `sek_namespace`, `sek_incr_id`, `sek_title`, `sek_alt_title`, `sek_desc`, `sek_adv_visible`,
                 `sek_simple_used`, `sek_myfez_visible`, `sek_order`, `sek_html_input`, `sek_fez_variable`,
                 `sek_smarty_variable`, `sek_cvo_id`, `sek_lookup_function`, `sek_data_type`, `sek_relationship`,
                 `sek_meta_header`, `sek_cardinality`, `sek_suggest_function`, `sek_faceting`, `sek_derived_function`,
                 `sek_lookup_id_function`, `sek_bulkchange`)
                VALUES
                ('core_111', 'core', 111, 'Copyright', '', '', 0, 0, 0, 999, 'checkbox', 'none', '', NULL, '', 'int', 0,
                '', 0, '', 0, '', '', 0);";

    $stmtAddRecordSearchKeyColumn = "ALTER TABLE " . APP_TABLE_PREFIX . "record_search_key
                    ADD rek_copyright INT(11) NULL,
                    ADD rek_copyright_xsdmf_id INT(11) NULL;";

    $this->_db->beginTransaction();
    try {
      $this->_db->exec($stmtAddSearchKey);
      $this->_db->exec($stmtAddRecordSearchKeyColumn);
      $this->_db->commit();

      // echo "<br /> Search key 'copyright' added to search_key table & the main record_search_key table.";
      return true;
    } catch (Exception $ex) {
      $this->_db->rollBack();
      echo "\n<br /> Failed to add search key 'copyright'. Error: " . $ex;
    }
    return false;
  }

  /**
   * Returns an array of unmapped XSD fields.
   * @return array | boolean
   */
  protected function _getUnmappedXSDFields()
  {
    $excludeInput = array('static', 'xsd_ref');
    $excludeDisplay = array('FEZACML for Datastreams', 'FezACML for Communities',
      'FezACML for Collections', 'FezACML for Records',
      'DesignRQF2006MD Display', 'MARCXML test record');

    $stmt = "SELECT xdis_title, fez_xsd_display_matchfields.*
                    FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields
                    LEFT JOIN fez_xsd_display ON xsdmf_xdis_id = xdis_id

                    WHERE xsdmf_enabled = 1 AND xsdmf_invisible = 0
                    AND xsdmf_html_input NOT IN (" . explode(", ", $excludeInput) . ")
                    AND xsdmf_sek_id IS NULL
                    AND xdis_title NOT IN (" . explode(", ", $excludeDisplay) . ");
                    ";
    try {
      $unmappedFields = $this->_db->fetchAll($stmt);
      return $unmappedFields;
    } catch (Exception $ex) {
      echo "\n<br />Failed to grab unmapped fields because of: " . $stmt . " <br />" . $ex . ".\n";
    }
    return false;
  }

  /**
   * Run Fedora managed content migration script & security for the attached files.
   * @todo: update misc/migrate_fedora_managedcontent_to_fezCAS.php to return, instead of exit at the end of the script.
   *
   * @todo:
   * To speed up this process, specially for system that already running CAS system,
   * we going to do a checksum, compare the new md5 with "existing" md5 if any.
   *
   */
  public function migrateManagedContent()
  {
    ob_flush();
    if ($this->_env != 'production') {
      return;
    }

    $stmt = 'select op.token as pid, dr.systemVersion as version, 
        dr.objectState as state, ds.path as path from datastreamPaths ds
      left join objectPaths op on op.tokenDbID = ds.tokenDbID
      left join doRegistry dr on dr.doPID = op.token
      where ds.path like \'/espace/data/fedora_datastreams/2016/08%\'
      order by op.token ASC, dr.systemVersion ASC';

    $ds = [];
    try {
      $ds = $this->_db->fetchAll($stmt);
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
      $dsName = $this->getDsNameFromPath($pid, $path);
      $acml = Record::getACML($pid, $dsName);

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
      $location = 'https://s3-ap-southeast-2.amazonaws.com/uql-fez-production-san/migration/' .
        str_replace('/espace/data/fedora_datastreams/', '', $path);

      if ($cloneExif) {
        Exiftool::cloneExif($pid, $dsName, $pid, $dsName, $exif);
      }

      Fedora_API::callAddDatastream(
        $pid, $dsName, $location, '', $state,
        $exif['exif_mime_type'], 'M', false, "", false
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
  public function migratePIDs()
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
      $this->_reindexPids($start, $limit);
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
  protected function _reindexPids($start, $limit)
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
  public function recalculateSecurity()
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
  public function setMaximumPID()
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
    if (!$this->_isTableExists($tableName)) {

      // echo "Creating pid_index table ... ";

      $stmt = "CREATE TABLE " . $tableName . "
                       (pid_number int(10) unsigned NOT NULL,
                        PRIMARY KEY (pid_number)
                       );";
      try {
        $this->_db->exec($stmt);
      } catch (Exception $ex) {
        // echo "\n<br />Table ". $tableName ." creation failed. Here is why: ". $stmt . " <br />" . $ex .".\n";
        return false;
      }
    }

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
  }

  /**
   * Upgrade table schema for all datastream permissions and pid non inherited permissions
   */
  public function updateForDatastreamPermission()
  {
    $file = APP_PATH . "/upgrade/sql_scripts/upgrade2012021700.sql";
    try {
      $this->_upgradeHelper->parse_mysql_dump($file);
      // echo chr(10) . "<br />Successfully created permissions table";
    } catch (Exception $e) {
      echo "\n<br> Failed updating datastream tables. file = " . $file . " Ex: " . $e;
      return false;
    }
    return true;
  }

  /**
   * Create tables and update db schema to store Digital Object & attached files.
   */
  public function createAdditionalTables()
  {
    // Add auth quick rules table.
    $file = APP_PATH . "/upgrade/sql_scripts/upgrade2012081000.sql";
    try {
      $this->_upgradeHelper->parse_mysql_dump($file);
      // echo chr(10) . "\n<br />Successfully added auth quick rules table";
    } catch (Exception $e) {
      echo "\n<br /> Failed updating auth quick rules tables. file = " . $file . " Ex: " . $e->getMessage();
      return false;
    }
    ob_flush();
    return true;
  }

  /**
   * This script is based on rm_fedora.php. 1.1 - 1.4, 1.6 - 1.7
   * Creates shadow tables for core search key tables and each non-core search key tables.
   * Remove any unique constraints from the shadow table.
   *
   * @return boolean
   */
  public function createSearchKeyShadowTables()
  {
    // 1.1 Create core search key shadow table
    // echo "\n<br />1.1 Creating core search key shadow table ... ";

    $originalTable = APP_TABLE_PREFIX . "record_search_key";

    // Create the table
    if (!$this->_createOneShadowTable($originalTable)) {
      return false;
    }
    // Remove any unique keys copied from the search key table from the shadow table
    $this->_removeUniqueConstraintsCore();

    // Add a joint primary key
    $this->_addJointPrimaryKeyCore();

    // echo "<br /> End of 1.1. Now we have shadow for core search key shadow table ".$originalTable;


    // 1.2 Create non-core search key shadow tables
    // echo "<br />1.2 Creating non-core search key shadow tables ... \n";
    ob_flush();
    $searchKeys = Search_Key::getList();
    foreach ($searchKeys as $sk) {

      // We only create search key table with multiple relationship
      if ($sk['sek_relationship'] != '1') {
        continue;
      }

      // echo "\n<br /> Shadowing " . $sk['sek_title_db'] . " table ... ";
      ob_flush();
      $originalTable = APP_TABLE_PREFIX . "record_search_key_" . $sk['sek_title_db'];
      $shadowTable = APP_TABLE_PREFIX . "record_search_key_" . $sk['sek_title_db'] . $this->_shadowTableSuffix;

      // Create the table
      if (!$this->_createOneShadowTable($originalTable, $sk['sek_title_db'])) {
        return false;
      }

      // Remove any unique keys copied from the search key table from the shadow table
      $this->_removeUniqueConstraintsNonCore($shadowTable, $sk['sek_title_db']);

      // Add joint primary key
      if ($sk['sek_cardinality'] == 1) {
        $this->_addJointPrimaryKeyMultipleNonCore($shadowTable, $sk['sek_title_db']);
      } else {
        $this->_addJointPrimaryKeyNonCore($shadowTable, $sk['sek_title_db']);
      }

      // echo "\n<br /> End of Shadowing " . $sk['sek_title_db'] . " table.. with a SuCCeSS!";
    }

    // echo "\n<br /> End of 1.2. Now we have shadow tables for non-core search keys.";
    ob_flush();
  }

  /**
   * Creates search key shadow table with matching schema with the original sk table.
   * Adds timestamp column on the shadow table record versioning.
   * @todo: Update to use CREATE TABLE IF NOT EXISTS instead.
   *
   * @param string $originalTable Name of the original search key table.
   * @return boolean True when shadow table successfully created
   */
  protected function _createOneShadowTable($originalTable, $sekTitleDb = "")
  {
    if (empty($originalTable) || is_null($originalTable)) {
      return false;
    }

    $shadowTable = $originalTable . $this->_shadowTableSuffix;

    // Creates table duplicate from original sk table
    if ($this->_isTableExists($shadowTable)) {

      $stmt = "DROP TABLE IF EXISTS " . $shadowTable;

      try {
        $this->_db->exec($stmt);
      } catch (Exception $ex) {
        echo "<br />Table " . $shadowTable . " dropping failed. Here is why: " . $stmt . " <br />" . $ex . ".\n";
        return false;
      }

      // echo "<br />Table ". $shadowTable ." has been Dropped.\n";
    }

    $stmt = "CREATE TABLE " . $shadowTable . " LIKE " . $originalTable;

    try {
      $this->_db->exec($stmt);
    } catch (Exception $ex) {
      echo "<br />Table " . $shadowTable . " creation failed. Here is why: " . $stmt . " <br />" . $ex . ".\n";
      return false;
    }

    // echo "<br />Table ". $shadowTable ." has been created.\n";


    // Add stamp column to new shadow table
    // echo "<br />Adding stamp column to the new shadow table ... ";

    $tableDescribe = $this->_db->describeTable($shadowTable);
    $columnName = "rek_" . (!empty($sekTitleDb) ? $sekTitleDb . "_" : "") . "stamp";

    if (!array_key_exists("rek_stamp", $tableDescribe)) {

      $stmt = "ALTER TABLE " . $shadowTable . " ADD COLUMN " . $columnName . " DATETIME;";

      try {
        $this->_db->exec($stmt);
      } catch (Exception $ex) {
        echo "<br />Alter table failed. Because of: " . $stmt . " <br />" . $ex;
        return false;
      }

      // echo "<br />Table ". $shadowTable ." has been altered.\n";

    } else {
      // echo "<br />We have the stamp! Move on...";
    }

      // } else {
      //   echo "<br />Table ". $shadowTable ." already exists somewhere in the universe, let's move on...\n";
      // }
    ob_flush();
    return true;
  }

  protected function _removeUniqueConstraintsCore($retries = 0)
  {
    // Core search key shadow table
    // echo chr(10) . "<br />" . "Removing unique constraint from fez_record_search_key__shadow ... ";
    $retries++;
    $tableName = APP_TABLE_PREFIX . "record_search_key" . $this->_shadowTableSuffix;

    // We are removing primary key on shadow because PID is serving as primary key on the core search key table.
    // echo "* Removing primary key constraint from fez_record_search_key__shadow ... ";
    $stmt = "ALTER TABLE " . $tableName . " DROP PRIMARY KEY;";
    try {
      $this->_db->exec($stmt);
    } catch (Exception $ex) {
      //echo "<br />No constraint to remove " .$stmt . " - Exception=" . $ex;
      //return false;
      if ($retries > 5) {
        return;
      } else {
        sleep(1);
        $this->_removeUniqueConstraintsCore($retries);
      }
    }
    // echo "ok!\n\n";
  }

  // 1.6 Remove unique constraints from non-core shadow tables
  protected function _removeUniqueConstraintsNonCore($tableName, $sekTitleDb)
  {
    // echo " Removing autoincrement to $tableName";
    $stmt = "ALTER TABLE " . $tableName . " MODIFY rek_" . $sekTitleDb . "_id INT NOT NULL;";
    try {
      $this->_db->exec($stmt);
    } catch (Exception $ex) {
      // May fail if PRIMARY key does not exist (MySQL version > 5.1)
      echo "<br />NOTICE: No primary key to drop on ". $tableName;
    }

    $stmt = "ALTER TABLE " . $tableName . " DROP PRIMARY KEY;";
    try {
      $this->_db->exec($stmt);
    } catch (Exception $ex) {
      // echo "<br />No constraint to remove " .$stmt . " - Exception=" . $ex;
      // return false;
    }

    $stmt = "SHOW INDEX FROM " . $tableName . " WHERE Non_unique = 0 AND Key_name != 'PRIMARY'";
    $indexes = $this->_db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    foreach ($indexes as $idx) {
      $stmt = "DROP INDEX " . $idx['Key_name'] . " ON " . $tableName . ";";
      try {
        $this->_db->exec($stmt);
      } catch (Exception $ex) {
        //echo "<br /> No unique constraint to remove. " . $stmt ;
      }
      // echo "ok!\n";
    }

    return true;
  }

  // 1.8 Add joint primary keys to shadow tables
  protected function _addJointPrimaryKeyCore($retries = 0)
  {
    $retries++;
    $tableName = APP_TABLE_PREFIX . "record_search_key" . $this->_shadowTableSuffix;

    $stmt = "ALTER TABLE " . $tableName . " ADD PRIMARY KEY (rek_pid, rek_stamp);";
    try {
      $this->_db->exec($stmt);
      return true;
    } catch (Exception $ex) {
      if ($retries > 5) {
        echo "<br />Could not add joint primary key to " . $tableName . " because: " . $ex;
        return false;
      } else {
        sleep(1);
        return $this->_addJointPrimaryKeyCore($retries);
      }
    }
    // echo "\n";
  }

  protected function _addJointPrimaryKeyNonCore($tableName, $sekTitleDb, $retries = 0)
  {
    $retries++;
    $stmt = "ALTER TABLE " . $tableName . " ADD PRIMARY KEY (rek_" . $sekTitleDb . "_pid, rek_" . $sekTitleDb . "_stamp);";
    try {
      $this->_db->exec($stmt);
      return true;
    } catch (Exception $ex) {
      if ($retries > 5) {
        echo "<br />Could not add joint primary key to " . $tableName . " because: " . $ex;
        return false;
      } else {
        sleep(1);
        return $this->_addJointPrimaryKeyNonCore($tableName, $sekTitleDb, $retries);
      }
    }
    // echo "\n";
  }

  protected function _addJointPrimaryKeyMultipleNonCore($tableName, $sekTitleDB, $retries = 0)
  {
    $retries++;
    // echo " Adding joint primary key to ". $tableName;
    $stmt = "ALTER TABLE " . $tableName . " ADD PRIMARY KEY (rek_" . $sekTitleDB . "_pid, rek_" . $sekTitleDB . "_order, rek_" . $sekTitleDB . "_stamp);";
    try {
      $this->_db->exec($stmt);
      return true;
    } catch (Exception $ex) {
      if ($retries > 5) {
        echo "<br />Could not add joint primary key to " . $tableName . " because: " . $ex;
        return false;
      } else {
        sleep(1);
        return $this->_addJointPrimaryKeyMultipleNonCore($tableName, $sekTitleDB, $retries);
      }
    }
    // echo "\n";
  }

  /**
   * Check if table already exists on currently connected database.
   *
   * @param string $tableName Name of the table, dooh...
   * @return boolean By default return FALSE. Return TRUE only when table exists.
   */
  protected function _isTableExists($tableName)
  {
    try {
      $exists = $this->_db->describeTable($tableName);
      if (!empty($exists)) {
        return true;
      }
    } catch (Exception $e) {
      // nothing to see here, return false on next line
    }
    return false;
  }

  protected function generateDSTimestamps($pid, $datastreams)
  {
    $createdDates = array();

    // Retrieve all versions of all datastreams
    foreach ($datastreams as $datastream) {
      //probably only need to check the dates of the FezMD datastream. This should reduce calls to Fedora and improve performance - CK added 17/7/2009.
      //Re-added mods since they also need checking
      if ($datastream['ID'] == 'FezMD') {
        $parms = array('pid' => $pid, 'dsID' => $datastream['ID']);

        $datastreamVersions = Fedora_API::openSoapCall('getDatastreamHistory', $parms);

        // Extract created dates from datastream versions
        foreach ($datastreamVersions as $key => $var) {

          // If a datastream contains multiple versions, Fedora bundles them in an array, however doesn't
          // do if a datastream only has a single version.

          // If the datastream is an array, retrieve value keyed under createDate
          if (is_array($var) && array_key_exists('createDate', $var)) {
            $createdDates[] = $var['createDate'];
          } // If the datastream isn't an array, retrieve the createDate value
          else if ($key === 'createDate') {
            $createdDates[] = $var;
          }
        }
      }
    }

    // Remove duplicate datestamps from array
    $createdDates = array_unique($createdDates);

    // Sort datestamps using the custom fedoraDateSorter function
    usort($createdDates, "fedoraDateSorter");

    // Iterate through amalgamated list of datestamps, removing those that are deemed to have been created
    // too closely-together to have been a result of a user edit.
    //
    // Once a 'phantom' version has been found, iterate through the list again until all datestamps are
    // suitably far apart.
    /*do {
      $phantomVersionFound = false;
      for ($i = 1; $i < sizeof($createdDates); $i++) {

        // If the time between the current datestamp and the previous datestamp is too low, remove the previous
        // entry and scan the list from the start
        if (strtotime($createdDates[$i]) - strtotime($createdDates[$i - 1]) < APP_VERSION_TIME_INTERVAL) {
          array_splice($createdDates, $i - 1, 1);
          $phantomVersionFound = true;
          break;
        }

        if ($phantomVersionFound) break;
      }
    } while ($phantomVersionFound);*/

    return $createdDates;
  }

  /**
   * Custom date sorter for Fedora dates, used by PHP's usort()
   *
   * <p>
   * Note: This function uses strtotime() directly on the dates, which appears to work but which may be flawed - I'm not
   * familiar with Fedora's date format or whether it's custom or a standard format.
   * </p>
   */
  protected function fedoraDateSorter($a, $b)
  {
    $unixTimestamp1 = strtotime($a);
    $unixTimestamp2 = strtotime($b);

    if ($unixTimestamp1 == $unixTimestamp2) return 0;
    return ($unixTimestamp1 < $unixTimestamp2) ? -1 : 1;
  }

  protected function toggleAwsStatus($useAws)
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
    }
  }

  protected function getDsNameFromPath($pid, $path)
  {
    $pidMatch = str_replace(':', '_', $pid);
    preg_match("/\\/$pidMatch\\+([^\\+]*)\\+/", $path, $matches);

    if (count($matches) !== 2) {
      return false;
    }
    return $matches[1];
  }

  protected function inheritsPermissions($acml)
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

  protected function addDatastreamSecurity($acml, $did)
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
