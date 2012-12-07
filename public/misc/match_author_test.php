<?php
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.record_general.php");
echo " here";
$rec = new RecordObject('UQ:240823');
echo " here";
$rec->matchAuthor(80900, TRUE, TRUE);

