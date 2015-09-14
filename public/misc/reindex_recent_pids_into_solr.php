<?php
/**
 * used in staging to reindex in solr updated pids
 * 
 */

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "class.db_api.php");

echo "Script started: " . date('Y-m-d H:i:s') . "\n";
$isUser = Auth::getUsername();
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {
    $log = FezLog::get();
    $db = DB_API::get();

    $date = date('Y-m', strtotime('-5 months'));
    $dbtp =  APP_TABLE_PREFIX;

    $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "fulltext_queue (ftq_pid, ftq_op) SELECT rek_pid, 'I' FROM fez_record_search_key
             LEFT JOIN " . APP_TABLE_PREFIX . "fulltext_queue ON rek_pid = ftq_pid
             WHERE ftq_pid IS NULL AND rek_updated_date > " . $db->quote($date) . " ORDER BY rek_updated_date ASC ";  #fulltext_queue is last in, first out
    try {
        $db->exec($stmt);
    }
    catch(Exception $ex) {
        $log->err($ex);
    }

    FulltextQueue::triggerUpdate();
    echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "Admin only";
}