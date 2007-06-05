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

class BackgroundProcess_GenerateDuplicatesReport extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_generate_duplicates_report.php';
        $this->name = 'Generate Duplicates Report';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));

        if (!empty($pids) && is_array($pids)
            && !empty($report_pid)) { 
            $dr = new DuplicatesReport($report_pid);
            $dr->setBGP($this);
            $dr->generate($pids);
        }
        $this->setState(2);
    }
}

 
?>
