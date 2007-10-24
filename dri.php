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

$username = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($username);

// setup the dri contents
switch($action)
{
	case 'browse-title':
		$tpl->setTemplate('themes/dri_browse_title.tpl.xml');
		$tpl->assign('include_manifest',true);
		$head = 'Browse by Title';
	break;
	case 'collection-home':
		$tpl->setTemplate('themes/dri_browse_title.tpl.xml');
		$tpl->assign('include_manifest',true);
		$head = 'Issue';
		$stmt = "SELECT rek_pid, rek_title, rek_description FROM ".APP_TABLE_PREFIX."record_search_key where rek_pid='$parent_pid' ";
		$list = $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$tpl->assign('list',$list);
	break;
	case 'search':
	$searchKey = $_REQUEST['searchKey1'];
	$query = $_REQUEST['query1'];
	$list = array();
	if (!empty($query)) {
		$options['searchKey'.Search_Key::getID($searchKey)] = $query;
		$list_res = Record::getListing($options, array("Lister", "Viewer"));
		$list = $list_res['list'];
		$tpl->assign('rend','search-results');
	}
	$tpl->setTemplate('themes/search.tpl.xml');
	$tpl->assign('list',$list);
	break;
	default: // view item
		$tpl->setTemplate('themes/dri_browse_title.tpl.xml');
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
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isUser", $username);
$tpl->assign(compact('pid','theme_id','parent_pid','action','head'));
$dri_xml = $tpl->displayTemplate();

?>