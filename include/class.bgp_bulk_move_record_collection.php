<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.bulk_move_record_collection.php');

class BackgroundProcess_Bulk_Move_Record_Collection extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_bulk_move_record_collection.php';
        $this->name = 'Bulk Move Records to Collection';
    }

    function run()
    {
        $this->setState(BGP_RUNNING);
        extract(unserialize($this->inputs));

        $bmrc = new Bulk_Move_Record_Collection;
        $bmrc->setBGP($this);

		if (!empty($options)) {
			$this->setStatus("Running search");
			$pids = $bmrc->getPidsFromSearchBGP($options);
			$this->setStatus("Found ".count($pids). " records");
		}
		if (!empty($pids) && is_array($pids)) { 
            $bmrc->moveBGP($pids, $parent_pid, $regen, true);
		}
        $this->setState(BGP_FINISHED);
    }
}



?>
