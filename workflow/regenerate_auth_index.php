<?php
include_once(APP_INC_PATH. 'class.record.php');
include_once(APP_INC_PATH. 'class.auth_index.php');
include_once(APP_INC_PATH . "class.bgp_index_auth.php");

$pid = $this->pid;
if (!empty($pid) && !is_numeric($pid)) { 
    AuthIndex::setIndexAuth($pid,true);
} else {
    $list = Community::getList(0,1000000);
    foreach ($list['list'] as $item) {
        AuthIndex::setIndexAuth($item['rek_pid'],true);
    }
}

?>
