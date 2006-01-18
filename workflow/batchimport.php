<?php
include_once(APP_INC_PATH. 'class.batchimport.php');
include_once(APP_INC_PATH. 'class.auth.php');
include_once(APP_INC_PATH . "class.bgp_batchimport_insert.php");

$pid = $this->pid;
$xdis_id = $this->getXDIS_ID();
$wftpl = $this->getvar('template');
$directory = $this->getVar('batch_directory');

$bgp_batch = new BackgroundProcess_BatchImport_Insert;
$inputs = compact('directory', 'xdis_id', 'pid', 'wftpl');
$inputs_str = serialize($inputs);
$bgp_batch->register($inputs_str, Auth::getUserID());


?>
