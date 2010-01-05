<?php

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.workflow_status.php");

// determine if there are workflows currently working on this pid and let the user know if there are
$pid = $_GET['pid'];
$workflowsCount = WorkflowStatusStatic::getCountForPid($pid);
echo "{$workflowsCount}";
