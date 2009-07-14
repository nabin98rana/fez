<?php

include_once('../config.inc.php');
include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.image_preview.php");
include_once(APP_INC_PATH . "najax_objects/class.background_process_list.php");

NAJAX_Server::allowClasses(array('NajaxImagePreview','NajaxBackgroundProcessList'));
if (NAJAX_Server::runServer()) {
	exit;
}
