<?php

include_once('../config.inc.php');
include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.image_preview.php");
include_once(APP_INC_PATH . "najax_objects/class.background_process_list.php");

file_put_contents('/tmp/mss', "hello\n");
NAJAX_Server::allowClasses(array('NajaxImagePreview','NajaxBackgroundProcessList'));
if (NAJAX_Server::runServer()) {
	exit;
}


file_put_contents('/tmp/mss', "hello2\n");

?>
