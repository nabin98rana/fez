<?php

include_once('config.inc.php');

$parent_pid = $_REQUEST['parent_pid'];
$pid = $_REQUEST['pid'];
$theme_id = $_REQUEST['theme_id'];
if (empty($theme_id)) {
	$theme_id = 'chameleon_journal';
}
$action = $_REQUEST['action'];

// get the theme xslt
$theme_dom = DOMDOcument::load(APP_PATH.'themes/'.$theme_id.'/theme.xsl');

// get the dri
if (empty($action)) {
	$action='community-home';
}
$no_headers = true;
ob_start();
include('dri.php');
$dri_xml = ob_get_contents();
ob_end_clean();

//Error_Handler::logError($dri_xml);
$dri_dom = DOMDOcument::loadXML($dri_xml);

// transform the DRI XML with the theme XSLT
$proc = new XSLTProcessor();
$proc->importStyleSheet($theme_dom);
$transformResult = $proc->transformToXML($dri_dom);

//header('Content-Type: application/xml');
print($transformResult); // html document

?>