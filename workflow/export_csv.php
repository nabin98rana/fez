<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.workflow_status.php");
include_once(APP_INC_PATH . "class.exportcsv.php");


$pid = $this->pid;
$exp = new ExportCSV;
$exp->export($pid);



?>
