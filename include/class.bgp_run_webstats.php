<?php
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.statistics.php');

class BackgroundProcess_Run_Webstats extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_run_webstats.php';
        $this->name = 'Run Webstats';
    }

    function run()
    {
        $this->setState(1);
        $stats = new Statistics;
        $reindex->bgp = $this;
        $stats->gatherStats();
        $this->setState(2);
    }
}

?>
