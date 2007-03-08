<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.bulk_assign_record_group.php');

class BackgroundProcess_Bulk_Assign_Record_Group extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_bulk_assign_record_group.php';
        $this->name = 'Bulk Assign Records to Group';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));

        $barg = new Bulk_Assign_Record_Group;
        $barg->setBGP($this);
		if (!empty($pids) && is_array($pids)) { 
			foreach ($pids as $pid) {
                $barg->assignGroupBGP($pid, $assign_grp_id, $regen, true);
			}
		}
        $this->setState(2);
    }
}



?>
