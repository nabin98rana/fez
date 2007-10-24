<?php

include_once('config.inc.php');
include_once(APP_INC_PATH.'class.auth.php');
include_once(APP_INC_PATH.'class.user.php');



if (!$no_headers) {
	header('Content-Type: application/xml');
}

if (empty($parent_pid)) {
	$parent_pid = $_REQUEST['parent_pid'];
}
if (empty($pid)) {
	$pid = $_REQUEST['pid'];
}
if (empty($action)) {
	$action = $_REQUEST['action'];
}
if (empty($theme_id)) {
	$theme_id = $_REQUEST['theme_id'];
}
// or default theme
if (empty($theme_id)) {
	$theme_id = 'chameleon_journal';
}

// get the dri
$tpl = new Template_API; 
$tpl->setTemplate('themes/dri_browse_title.tpl.xml');

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);

// setup the dri contents
switch($action)
{
	case 'browse-title':
		$tpl->assign('include_manifest',true);
		$head = 'Browse by Title';
	break;
	default: // view item
		$stmt = "SELECT rek_pid, rek_title FROM ".APP_TABLE_PREFIX."record_search_key where rek_pid='$pid' ";
		$list = $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		foreach ($list as $key => $item) {
			$stmt = "SELECT rek_file_attachment_name 
				FROM ".APP_TABLE_PREFIX."record_search_key_file_attachment_name 
				WHERE rek_file_attachment_name_pid='$pid' ";
			$list[$key]['rek_file_attachment_name']
				= $GLOBALS['db_api']->dbh->getCol($stmt);
		}
		$tpl->assign('list',$list);
	break;
}
$tpl->assign(compact('pid','theme_id','parent_pid','action','head'));
$dri_xml = $tpl->displayTemplate();

?>