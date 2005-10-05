<?php

include_once('../config.inc.php');
include_once(APP_INC_PATH.'class.workflow_status.php');

$id = Misc::GETorPOST('id');
$wfstatus = WorkflowStatusStatic::getFromDB($id);
$wfstatus->run();

?>
