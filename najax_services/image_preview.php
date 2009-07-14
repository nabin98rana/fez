<?php

include_once('../config.inc.php');
include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.image_preview.php");

NAJAX_Server::allowClasses('NajaxImagePreview');
if (NAJAX_Server::runServer()) {
	exit;
}
