<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.exportspreadsheet.php');
include_once(APP_INC_PATH.'class.exportspreadsheet_rqf.php');

class BackgroundProcess_Export_Spreadsheet extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_export_spreadsheet.php';
        $this->name = 'Export Spreadsheet';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));
        if (!is_null($rqf) && $rqf) {
            $exp = new ExportSpreadsheetRQF;
        } else {
            $exp = new ExportSpreadsheet;
        }
        $exp ->setBackgroundObject($this);
        $exp->export2File($pid);
        $this->setState(2);
    }
}

?>
