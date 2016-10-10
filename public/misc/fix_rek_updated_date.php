<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
if (php_sapi_name()==="cli") {
  fixRekUpdatedDate();
}

function fixRekUpdatedDate() {
  $db = DB_API::get();

  $stmt = "SELECT rek_pid
                 FROM " . APP_TABLE_PREFIX . "record_search_key";
  $pids = [];
  try {
    $pids = $db->fetchCol($stmt);
  } catch (Exception $e) {
  }

  $count = count($pids);
  $i = 0;
  foreach ($pids as $pid) {
    $i++;
    echo "Updating $i/$count\n";
    try {
      $stmt = "UPDATE " . APP_TABLE_PREFIX . "record_search_key SET
                 rek_updated_date=(
                   SELECT pre_date FROM " . APP_TABLE_PREFIX . "premis_event
                   WHERE pre_pid=" . $db->quote($pid)  . " ORDER BY pre_date DESC LIMIT 0,1
                 )
                 WHERE rek_pid=" . $db->quote($pid)  . "
                   AND rek_pid IN (SELECT pre_pid FROM " . APP_TABLE_PREFIX . "premis_event 
                   WHERE pre_pid=" . $db->quote($pid)  . ")";
      $db->exec($stmt);
    } catch (Exception $e) {
      echo $e->getMessage() . "\n";
      exit;
    }
  }
}
