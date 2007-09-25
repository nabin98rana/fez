<?php
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.statistics.php');

class BackgroundProcess_Run_Webstats extends BackgroundProcess
{
    var $summary_only = false;

    function __construct($summary_only = false) {
        parent::__construct();
        $this->include = 'class.bgp_run_webstats.php';
        $this->name = 'Run Webstats';
		$this->summary_only = $summary_only;
    }

    function run() {
        $this->setState(1);
        extract(unserialize($this->inputs));
        $stats = new Statistics;
        $stats->setBGP($this);
		if ($this->summary_only == true) {
        	$stats->updateSummaryStats();
		} else {
        	$stats->gatherStats(); //updateSummaryStats is called in the end of gatherStats
		}
        $this->setState(2);
    }
}

?>
