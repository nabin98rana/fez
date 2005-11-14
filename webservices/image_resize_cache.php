<?php

include_once('../config.inc.php');
include_once(APP_INC_PATH.'najax_objects/class.image_preview.php');

$width=400;
$height=400;
$pid = '';
$dsID = '';
$regen = false;

extract($_GET);

$prev_obj = new NajaxImagePreview();
$fname = $prev_obj->getPreview($pid, $dsID, $width, $height, $regen);

header('Content-type: image/jpeg');
readfile($fname);


?>

