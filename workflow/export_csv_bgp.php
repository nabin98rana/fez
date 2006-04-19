<?php
include_once(APP_INC_PATH. 'class.exportcsv.php');
include_once(APP_INC_PATH. 'class.auth.php');
include_once(APP_INC_PATH . "class.bgp_export_csv.php");

$pid = $this->pid;

$bgp_csv = new BackgroundProcess_Export_CSV;
$inputs = compact('pid');
$inputs_str = serialize($inputs);
$bgp_csv->register($inputs_str, Auth::getUserID());


?>
