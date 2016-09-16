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

if ((php_sapi_name() !== "cli")) {
  return;
}

/**
 * The purpose of this script is to migrate Fedora managed contents.
 * This is a one-off migration script as part of Fedora-less project.
 */
include_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'config.inc.php';
error_reporting(1);
set_time_limit(0);
$log = FezLog::get();
$db = DB_API::get();

$stmt = 'select op.token as pid, dr.systemVersion as version, 
  dr.objectState as state, ds.path as path from datastreamPaths ds
left join objectPaths op on op.tokenDbID = ds.tokenDbID
left join doRegistry dr on dr.doPID = op.token
where ds.path like \'/espace/data/fedora_datastreams/2016/08%\'
order by op.token ASC, dr.systemVersion ASC';

$ds = [];
try {
  $ds = $db->fetchAll($stmt);
} catch (Exception $ex) {
  echo "Failed to retrieve exif data. Error: " . $ex;
}

$totalDs = count($ds);
$counter = 0;
$awsSrc = new AWS(APP_SAN_IMPORT_DIR, 'migration');

foreach ($ds as $dataStream) {
  $counter++;
  $pid = $dataStream['pid'];

  echo "\nDoing PID $counter/$totalDs ($pid)\n";
  Zend_Registry::set('version', Date_API::getCurrentDateGMT());

  $path = $dataStream['path'];
  $state = $dataStream['state'];
  $dsName = getDsNameFromPath($pid, $path);
  $acml = Record::getACML($pid, $dsName);

  $exif = [];
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

  toggleAwsStatus(true);
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
  if (inheritsPermissions($acml)) {
    AuthNoFedoraDatastreams::setInherited($did);
  }
  if ($acml) {
    addDatastreamSecurity($acml, $did);
  }
  AuthNoFedoraDatastreams::recalculatePermissions($did);
  toggleAwsStatus(false);
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

function getDsNameFromPath($pid, $path)
{
  $pidMatch = str_replace(':', '_', $pid);
  preg_match("/\\/$pidMatch\\+([^\\+]*)\\+/", $path, $matches);

  if (count($matches) !== 2) {
    return false;
  }
  return $matches[1];
}

function inheritsPermissions($acml)
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

function addDatastreamSecurity($acml, $did)
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
