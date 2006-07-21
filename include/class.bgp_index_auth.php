<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.auth_index.php');

class BackgroundProcess_Index_Auth extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_index_auth.php';
        $this->name = 'Index Auth';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));

        $auth_index = new AuthIndex;
        $auth_index->setBGP($this);
        $auth_index->setIndexAuthBGP($pid, true);
        $this->setState(2);
    }
}

?>
