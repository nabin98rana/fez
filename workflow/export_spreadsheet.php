<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.workflow_status.php");
include_once(APP_INC_PATH . "class.exportspreadsheet.php");


$pid = $this->pid;
$exp = new ExportSpreadsheet;
$exp->export($pid);



?>
