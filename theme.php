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
list($dri_xml, $dri_info) = Misc::processURL(APP_BASE_URL."dri.php?theme_id=$theme_id&pid=$pid");

//print_r($dri_xml);
$dri_dom = DOMDOcument::loadXML($dri_xml);

// transform the DRI XML with the theme XSLT
$proc = new XSLTProcessor();
$proc->importStyleSheet($theme_dom);
$transformResult = $proc->transformToXML($dri_dom);

//header('Content-Type: application/xml');
print($transformResult); // html document

?>