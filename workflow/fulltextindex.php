<?php
include_once(APP_INC_PATH. 'class.record.php');
include_once(APP_INC_PATH. 'class.fulltext_index.php');

$pid = $this->pid;
if (!empty($pid) && !is_numeric($pid)) { 
    FulltextIndex::indexPid($pid);
} else {
    $list = Community::getList(0,1000000);
    foreach ($list['list'] as $item) {
        FulltextIndex::indexPid($item['pid']);
    }
}

?>
