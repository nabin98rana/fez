<?php

include_once('config.inc.php');

header('Content-Type: application/xml');

$pid = $_REQUEST['pid'];
$theme_id = $_REQUEST['theme_id'];
if (empty($theme_id)) {
	$theme_id = 'chameleon_journal';
}

// get the dri
$tpl = new Template_API; 
$tpl->setTemplate('themes/dri_browse_title.tpl.xml');
// setup the dri contents
$tpl->assign(compact('pid','theme_id'));
switch($action)
{
	case 'browse-title':
	default:
		// browse title stuff
	break;
}
$dri_xml = $tpl->displayTemplate();

?>