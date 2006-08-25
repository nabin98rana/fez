<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.fulltext_index.php');

class BackgroundProcess_Fulltext_Index extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_fulltext_index.php';
        $this->name = 'Fulltext Index';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));

        $ft_index = new FulltextIndex;
        $ft_index->setBGP($this);
        $ft_index->indexBGP($pid, $regen, true);
        $this->setState(2);
    }
}



?>
