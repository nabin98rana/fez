<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 15/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.duplicates_report.php');

class BackgroundProcess_DuplicatesReportMergeIsiLoc extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_duplicates_report_merge_isi_loc.php';
        $this->name = 'Duplicates Report Merge on ISI LOC';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));

        if (!empty($report_pid)) { 
            $dr = new DuplicatesReport($report_pid);
            $dr->setBGP($this);
            $dr->autoMergeOnISI_LOC();
        }
        $this->setState(2);
    }
}

 
?>
