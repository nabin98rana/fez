<?php

include_once('../config.inc.php');
include_once(APP_INC_PATH.'najax_objects/class.image_preview.php');

$pid = '';
$dsID = '';

extract($_GET);

$prev_obj = new NajaxImagePreview();
$fname = $prev_obj->getPreview($pid, $dsID);

header('Content-type: image/jpeg');
readfile($fname);
