<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH. 'class.bgp_generate_bookreader_images.php');
include_once(APP_INC_PATH. 'class.error_handler.php');

$pid = $this->pid;

$bgp = new BackgroundProcess_Generate_Bookreader_Images();
$bgp->register(serialize(compact('pid')), Auth::getUserID());