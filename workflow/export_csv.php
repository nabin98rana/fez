<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.workflow_status.php");


$pid = $this->pid;

$record = new RecordGeneral($pid);
echo $record->getObjectXML();

?>
