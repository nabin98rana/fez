<?php
include_once("../config.inc.php");
include_once(APP_INC_PATH. 'class.bulk_move_record_collection.php');
include_once(APP_INC_PATH. 'class.error_handler.php');

$collection_pid = $this->pid;

$pids = $this->pids;

if (!empty($pids) && is_array($pids)) { 
    Bulk_Move_Record_Collection::movePids($pids, $collection_pid);
}

?>
