<?php

include_once('config.inc.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $filename = APP_FEDORA_OAI_URL.'?'.$_SERVER['QUERY_STRING'];
    readfile($filename);
}



?>
