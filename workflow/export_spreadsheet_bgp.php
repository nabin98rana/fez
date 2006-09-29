<?php
include_once(APP_INC_PATH. 'class.auth.php');
include_once(APP_INC_PATH . "class.bgp_export_spreadsheet.php");

$pid = $this->pid;

$bgp_csv = new BackgroundProcess_Export_Spreadsheet;
$inputs = compact('pid');
$inputs_str = serialize($inputs);
$bgp_csv->register($inputs_str, Auth::getUserID());


?>
