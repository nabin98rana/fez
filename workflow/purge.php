<?php
// first delete all indexes about this pid
$pid = $this->pid;
Record::removeIndexRecord($pid);
$res = Fedora_API::callPurgeObject($pid);
?>
