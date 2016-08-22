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
// | Author: Elvi Shu <e.shu@library.uq.edu.au>                           |
// +----------------------------------------------------------------------+

if ((php_sapi_name()!=="cli")) {
  return;
}

/**
 * The purpose of this script is to
 *   migrate Fedora managed contents (ie: PDFs, images, etc) to Fez CAS system.
 * Fez CAS system is storing file content in MD5 hash format and recording the file meta data in %TABLE_PREFIX%_file_attachments table.
 *
 * This is a one-off migration script as part of Fedora-less project.
 */
include_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'config.inc.php';
error_reporting(1);
set_time_limit(0);
$log = FezLog::get();
$db = DB_API::get();

$pids = '';
$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "exif where exif_pid = 'UQ:316342' ORDER BY exif_pid ";  //where exif_pid = 'UQ:21033'
try {
  $pids = $db->fetchAll($stmt);
} catch (Exception $ex) {
  echo "Failed to retrieve exif data. Error: " . $ex;
}
$totalPids = count($pids);
$counter = 0;
foreach ($pids as $pid) {
  $pid = $pid['pid'];
  $counter++;
  $datastreams = Fedora_API::callGetDatastreams($pid);
  $datastreams = Misc::cleanDatastreamListLite($datastreams, $pid);
  if (count($datastreams) > 0) {
    echo "\nDoing PID $counter/$totalPids ($pid)\n";
  }

  foreach ($datastreams as $datastream) {

    if ($datastream['controlGroup'] == 'M') {

      Zend_Registry::set('version', Date_API::getCurrentDateGMT());

      $fedoraFilePath = APP_FEDORA_GET_URL . "/" . $pid . "/" . $datastream['ID'];
      $temp_store = APP_TEMP_DIR . $datastream['ID'];
      file_put_contents($temp_store, fopen($fedoraFilePath, 'r'));

      $acml = Record::getACML($pid, $datastream['ID']);
      file_put_contents($temp_store . ".acml.xml", $acml);

      toggleAwsStatus(true);
      $command = APP_PHP_EXEC . " \"" . APP_PATH . "upgrade/fedoraBypassMigration/migrate_import_datastream.php\" \"" .
        $pid . "\" \"" . $temp_store . "\" \"" . $datastream['ID'] . "\"";
      exec($command);
      @unlink($temp_store);
      @unlink($temp_store . ".acml.xml");
      toggleAwsStatus(false);
    }
  }
}

function toggleAwsStatus($useAws)
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