<?php
include_once("../config.inc.php");
include_once(APP_INC_PATH. 'class.bgp_bulk_remove_record_collection.php');
include_once(APP_INC_PATH. 'class.error_handler.php');

$collection_pid = $this->pid;
$pids           = $this->pids;

if (!empty($pids) && is_array($pids)) { 
    $bgp = new BackgroundProcess_Bulk_Remove_Record_Collection; 
    $bgp->register(serialize(compact('pids', 'collection_pid', 'regen')), Auth::getUserID());
}

?>
