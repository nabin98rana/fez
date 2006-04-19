<?php

include_once('config.inc.php');
include_once(APP_INC_PATH.'class.background_process.php');


$bgp_id = Misc::GETorPOST('bgp_id');

$bgp = new BackgroundProcess($bgp_id);
$bgp->getExportFile();

