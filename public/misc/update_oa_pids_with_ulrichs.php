<?php
// Script to update pids with Ulrichs information
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.fulltext_queue.php");

echo "Script started: " . date('Y-m-d H:i:s') . "\n";

$query = "SELECT rek_issn_pid AS pid FROM " . APP_TABLE_PREFIX . "record_search_key_issn
INNER JOIN " . APP_TABLE_PREFIX . "ulrichs
ON ulr_issn = rek_issn
LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_oa_status
ON rek_oa_status_pid = rek_issn_pid
GROUP BY rek_issn_pid, ulr_open_access
HAVING  ulr_open_access = 'true' AND 'rek_oa_status' != 1";

$db = DB_API::get();
$log = FezLog::get();

try {
    $result = $db->fetchAll($query);
} catch (Exception $ex) {
    $log = FezLog::get();
    $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
    return;
}

echo "Checking " . count($result) . " pids\n";
flush();
ob_flush();

$history = 'Ulrichs info added - OA Status = yes, OA Embargo Days = 0';

// for each pid,
foreach ($result as $pidDetails) {
    $pid = $pidDetails['pid'];
    $record = new RecordObject($pid);
    $record->addSearchKeyValueList(array("OA Status"), array('on'), true, $history);
    $record->addSearchKeyValueList(array("OA Embargo Days"), array('0'), true, $history);
    echo $pid.' ';
    flush();
    ob_flush();
}

echo "Script finished: " . date('Y-m-d H:i:s') . "\n";

