<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.workflow_status.php");

$id = Misc::GETorPOST('id');
$tpl->assign("id", $id);
$wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$wfstatus->setTemplateVars($tpl);
$tpl->assign("submit_to_popup", true);
$wfstatus->checkStateChange();


?>
