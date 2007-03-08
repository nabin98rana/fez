<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.bulk_assign_record_user.php');

class BackgroundProcess_Bulk_Assign_Record_User extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_bulk_assign_record_user.php';
        $this->name = 'Bulk Assign Records to User';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));

        $barg = new Bulk_Assign_Record_User;
        $barg->setBGP($this);
		if (!empty($pids) && is_array($pids)) { 
			foreach ($pids as $pid) {
                $barg->assignUserBGP($pid, $assign_usr_ids[0], $regen, true);
			}
		}
        $this->setState(2);
    }
}



?>
