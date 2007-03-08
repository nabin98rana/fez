<?php
include_once("../config.inc.php");
include_once(APP_INC_PATH. 'class.bulk_assign_record_group.php');
include_once(APP_INC_PATH. 'class.error_handler.php');

$grp_id = $this->assign_grp_id;

$pids = $this->pids;

if (!empty($pids) && is_array($pids)) { 
    Bulk_Assign_Record_Group::assignGroupPids($pids, $grp_id);
}

?>
