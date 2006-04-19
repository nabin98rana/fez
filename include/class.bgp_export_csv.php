<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.exportcsv.php');

class BackgroundProcess_Export_CSV extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_export_csv.php';
        $this->name = 'Export CSV';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));
        $exp = new ExportCSV;
        $exp ->setBackgroundObject($this);
        $exp->export2File($pid);
        $this->setState(2);
    }
}

?>
