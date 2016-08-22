<?php

if ((php_sapi_name()!=="cli")) {
  return;
}

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.batchimport.php");
include_once(APP_INC_PATH . "class.auth_no_fedora_datastreams.php");

array_shift($argv);
$ARGS = $argv;

$pid = $ARGS[0];
$temp_store = $ARGS[1];
$datastream_id = $ARGS[2];

BatchImport::handleStandardFileImport($pid, $temp_store, $datastream_id, 0, true);
$acml = file_get_contents($temp_store . ".acml.xml");
$did = AuthNoFedoraDatastreams::getDid($pid, $datastream_id);
if (inheritsPermissions($acml)) {
  AuthNoFedoraDatastreams::setInherited($did);
}
// Setup security on Datastream
if ($acml) {
  addDatastreamSecurity($acml, $did);
}
AuthNoFedoraDatastreams::recalculatePermissions($did);


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

