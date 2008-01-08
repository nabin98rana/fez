<?php

include_once('../config.inc.php');
include_once(APP_INC_PATH.'najax_objects/class.image_preview.php');

$width = APP_IMAGE_PREVIEW_MAX_WIDTH;
$height = APP_IMAGE_PREVIEW_MAX_HEIGHT;
$pid = '';
$dsID = '';
$regen = false;

extract($_GET);

$prev_obj = new NajaxImagePreview();
$fname = $prev_obj->getPreview($pid, $dsID, $width, $height, $regen);

header('Content-type: image/jpeg');
readfile($fname);


?>

