<?php

include_once('config.inc.php');

header('Content-Type: application/xml');
$tpl = new Template_API; 
$tpl->setTemplate('themes/manifest.tpl.xml');
$tpl->displayTemplate();

?>