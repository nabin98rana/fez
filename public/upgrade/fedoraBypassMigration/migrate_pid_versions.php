<?php

if ((php_sapi_name()!=="cli")) {
  return;
}

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';

array_shift($argv);
$ARGS = $argv;

$pid = $ARGS[0];
$createDT = $ARGS[1];

echo "Updating $pid, $createDT\n";

$record = new RecordObject($pid, $createDT);
Workflow::start(289, $pid, $record->xdis_id);
$record->forceInsertUpdate([]);

echo "Updated $pid, $createDT\n";

if (APP_FILECACHE == "ON") {
  $cache = new fileCache($pid, 'pid=' . $pid);
  $cache->poisonCache();
}
