<?php
include_once("../config.inc.php");
include_once(APP_INC_PATH. 'class.bulk_assign_record_user.php');
include_once(APP_INC_PATH. 'class.error_handler.php');

$assign_usr_ids = $this->getvar('assign_usr_ids');

$pids = $this->pids;

if (!empty($pids) && is_array($pids)) { 
    Bulk_Assign_Record_User::assignUserPids($pids, $assign_usr_ids);
}

?>
