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

    // Recalculate security
    echo "Step 4: recalculating security..";
    $this->stepFourMigration();
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
        echo "Unable to connect to the Fedora DB.\n";
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
    // Convert the XML quick templates
    echo " - Converting quick templates..";
    $this->convertQuickTemplates();
    echo "..done!\n";

    // Update shadow key stamp with rek_updated_date in core SK table
    // Update rek_security_inherited from FezACML for the pids
    echo " - Updating shadow tables and pid security..\n";
//    $this->updateShadowTableStampsAndAddPidSecurity();
    echo "..done!\n";

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
      echo "\n<br /> Failed to de-dupe auth group tables. Error: " . $ex->getMessage();
      return false;
    }
  }

  /**
   * Fourth round of migration, recalculate PID security.
   */
  private function stepFourMigration()
  {
    $this->recalculatePidSecurity();
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
      where path like '/espace/data/fedora_datastreams/2016/08%'
        and token like 'UQ:399648+%'
      order by path DESC";

    $ds = [];
    try {
      $ds = $this->_fedoraDb->fetchAll($stmt, [], Zend_Db::FETCH_ASSOC);
    } catch (Exception $ex) {
      echo "Failed to retrieve exif data. Error: " . $ex;
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

      echo "\nDoing PID $counter/$totalDs ($pid)\n";
      Zend_Registry::set('version', Date_API::getCurrentDateGMT());

      $acml = $this->getFezACML($pid, 'FezACML_' . $dsName . '.xml');
      if ($acml) {
        echo $acml->saveXML() . "\n";
      } else {
        echo "No FezACML found for record\n";
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

      $did = AuthNoFedoraDatastreams::getDid($pid, $dsName);
      if ($this->inheritsPermissions($acml)) {
        AuthNoFedoraDatastreams::setInherited($did);
      }
      if ($acml) {
        $this->addDatastreamSecurity($acml, $did);
      }
      // Disabled - all security will be recalculated in the fourth step
      // AuthNoFedoraDatastreams::recalculatePermissions($did);
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
      echo chr(10) . "<br /> Failed to retrieve pids. Query: " . $stmt;
      return false;
    }

    if (sizeof($pids) > 0) {
      Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);
    }
    ob_flush();
    return true;
  }

  /**
   * Migrate the previous versions into the shadow tables
   * @return bool
   */
  private function migratePidVersions()
  {
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
   * Update shadow key stamp with rek_updated_date in core SK table
   * Update rek_security_inherited from FezACML for the pids
   */
  private function updateShadowTableStampsAndAddPidSecurity()
  {

    $stmt = "SELECT rek_pid, rek_updated_date, rek_security_inherited FROM " .
      APP_TABLE_PREFIX . "record_search_key WHERE rek_security_inherited IS NULL";
    try {
      $records = $this->_db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    } catch (Exception $ex) {
      $this->_log->err($ex);
      echo "Failed to retrieve pids. Error: " . $ex;
      return false;
    }

    $count = count($records);
    $i = 0;
    foreach ($records as $rek) {
      $i++;
      echo " - Updating rek_security_inherited $i/$count\n";
      $acml = $this->getFezACML($rek['rek_pid'], 'FezACML');
      if ($this->inheritsPermissions($acml)) {
        AuthNoFedora::setInherited($rek['rek_pid'], 1, false);
      }
      else {
        AuthNoFedora::setInherited($rek['rek_pid'], 0, false);
      }
    }

    $searchKeys = Search_Key::getList();
    $stmt = "SELECT rek_pid, rek_updated_date FROM " .
      APP_TABLE_PREFIX . "record_search_key";
    try {
      $records = $this->_db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    } catch (Exception $ex) {
      echo "Failed to retrieve pids. Error: " . $ex;
      return false;
    }

    $recordDates = [];
    foreach ($records as $rek) {
      $recordDates[$rek['rek_pid']] = $rek['rek_updated_date'];
    }
    unset($records);

    $count = count($searchKeys);
    $i = 0;
    $table = APP_TABLE_PREFIX . "record_search_key";
    foreach ($searchKeys as $searchKey) {
      $i++;
      $shadowTable = "${table}_${searchKey['sek_title_db']}__shadow";
      echo " - Updating $shadowTable $i/$count\n";

      if ($searchKey['sek_relationship'] == 1) {
        $stmt = "SELECT DISTINCT rek_${searchKey['sek_title_db']}_pid FROM $shadowTable";
        $pids = [];
        try {
          $pids = $this->_db->fetchCol($stmt);
        } catch (Exception $ex) {}

        $stmt = 'UPDATE ' . $shadowTable .
          ' SET rek_' . $searchKey['sek_title_db'] . '_stamp = :stamp' .
          ' WHERE rek_' . $searchKey['sek_title_db'] . '_pid = :pid';
        foreach ($pids as $pid) {
          if (array_key_exists($pid, $recordDates)) {
            $data = [
              ':pid' => $pid,
              ':stamp' => $recordDates[$pid],
            ];
            try {
              $this->_db->query($stmt, $data);
            } catch (Exception $ex) {}
          }
        }
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

  /**
   * @param DOMDocument $acml
   * @return bool
   */
  private function inheritsPermissions($acml)
  {
    echo "Checking if inherits permissions..";
    $inherit = true;
    if ($acml instanceof DOMDocument) {
      $xpath = new DOMXPath($acml);
      $inheritSearch = $xpath->query('/FezACML[inherit_security="off"]');
      if ($inheritSearch->length > 0) {
        $inherit = false;
      }
    }
    if ($inherit) {
      echo "is inherited\n";
    } else {
      echo "not inherited\n";
    }
    return $inherit;
  }

  private function recalculatePidSecurity() {
    echo "Recalculating PID security\n";
    // Get all PIDs without parents and recalculate permissions.
    // This will filter down to child pids and child datastreams
    $stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key
      LEFT JOIN fez_record_search_key_ismemberof
      ON rek_ismemberof_pid = rek_pid
      WHERE rek_ismemberof IS NULL and rek_object_type != 3";

    $res = [];
    try {
      $res = $this->_db->fetchAll($stmt);
    } catch (Exception $ex) {
      echo "Failed to retrieve pids\n";
    }

    $i = 0;
    $count = count($res);
    foreach ($res as $pid) {
      $i++;
      AuthNoFedora::recalculatePermissions($pid, false, false);
      echo "Done $i/$count\n";
    }
  }
  private function addDatastreamSecurity($acml, $did)
  {
    // loop through the ACML docs found for the current pid or in the ancestry
    $xpath = new DOMXPath($acml);
    $roleNodes = $xpath->query('/FezACML/rule/role');

    foreach ($roleNodes as $roleNode) {
      $role = $roleNode->getAttribute('name');
      $roleId = Auth::getRoleIDByTitle($role);
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
            AuthNoFedoraDatastreams::addSecurityPermissions($did, $roleId, $arId, false);
          }
        }
      }
    }
  }

  /**
   * Converts the XML quick templates
   */
  private function convertQuickTemplates()
  {
    $roles = Auth::getAssocRoleIDs();
    $authRoles = array_flip($roles);
    $templates = FezACML::getQuickTemplateAssocList();

    AuthRules::getOrCreateRuleGroup([[
      'rule' => 'public_list',
      'value' => 1,
    ]]);

    foreach ($templates as $qatId => $qatTitle) {
      $acmlXml = FezACML::getQuickTemplateValue($qatId);
      $acmlDoc = new DomDocument();
      $acmlDoc->loadXML($acmlXml);
      $xpath = new DOMXPath($acmlDoc);
      $roleNodes = $xpath->query('/FezACML/rule/role');

      foreach ($roleNodes as $roleNode) {
        $roleName = $roleNode->getAttribute('name');
        $groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0]', $roleNode);

        foreach ($groupNodes as $groupNode) {
          $groupRule = $groupNode->nodeName;
          $groupValues = explode(',', $groupNode->nodeValue);
          $groups = [];
          foreach ($groupValues as $groupValue) {
            if ($groupValue != "off") {
              $groupValue = trim($groupValue, ' ');
              $groups[] = [
                'rule' => '!rule!role!' . $groupRule,
                'value' => $groupValue,
              ];
            }
          }
          if (count($groups) === 0) {
            continue;
          }
          $argId = AuthRules::getOrCreateRuleGroup($groups);
          $aroId = $authRoles[$roleName];
          $authQuickRule = [
            ':qac_aro_id' => $aroId,
            ':qac_arg_id' => $argId
          ];

          $qacId = '';
          $stmt = "SELECT qac_id FROM " . APP_TABLE_PREFIX . "auth_quick_rules WHERE "
            . "qac_aro_id = :qac_aro_id AND qac_arg_id = :qac_arg_id";
          try {
            $qacId = $this->_db->fetchOne($stmt, $authQuickRule);
          } catch (Exception $ex) {
          }

          if (!$qacId && $qacId != -1) {
            $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "auth_quick_rules "
              . "(qac_aro_id, qac_arg_id) VALUES "
              . "(:qac_aro_id, :qac_arg_id)";
            try {
              $this->_db->query($stmt, $authQuickRule);
            } catch (Exception $ex) {
              echo "Error creating quick rule: " . $ex->getMessage() . "\n\n";
            }
          }
        }
      }
    }
  }

  private function getFezACML($pid, $dsID)
  {
    echo "Getting FezACML for $pid/$dsID\n";
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
}
