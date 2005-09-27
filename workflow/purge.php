<?php
// first delete all indexes about this pid
$pid = Misc::GETorPOST('pid');
Record::removeIndexRecord($pid);
$res = Fedora_API::callPurgeObject($pid);
$wfstatus = WorkflowStatusStatic::getSession($pid); // restores WorkflowStatus object from the session
$wfstatus->checkStateChange(true);
?>
