<?php
include_once(APP_INC_PATH.'class.batchadd.php');
include_once(APP_INC_PATH.'class.background_process.php');

class BackgroundProcess_BatchAdd_Record extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_batchadd_record.php';
        $this->name = 'Batch Add';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));
        $batch = new BatchAdd;
        $batch->setBackgroundObject($this);
        $batch->insert($files, $xdis_id, $pid, $wftpl);
        $this->setState(2);
    }
}

?>
