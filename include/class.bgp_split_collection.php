<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.bulk_move_record_collection.php');

class BackgroundProcess_Split_Collection extends BackgroundProcess
{

	const CHUNK_SIZE = 2000;

    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_split_collection.php';
        $this->name = 'Split Collection';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));

        $bmrc = new Bulk_Move_Record_Collection;
        $bmrc->setBGP($this);
        
		$bmrc->splitCollection($collection_pid, BackgroundProcess_Split_Collection::CHUNK_SIZE);
		
        $this->setState(2);
    }
}



?>