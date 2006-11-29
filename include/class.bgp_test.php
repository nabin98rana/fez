<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 28/11/2006
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.community.php');
include_once(APP_INC_PATH.'class.record.php');

class BackgroundProcess_Test extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_test.php';
        $this->name = 'Background Process Test';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));
        $this->setStatus('I got '.$test);
        $this->setState(2);
    }
}
?>