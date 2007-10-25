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
	case 'community-home':
		$tpl->setTemplate('themes/dri_community.tpl.xml');
		$stmt = "select rek_pid, rek_title, rek_description, rek_issue_number, rek_volume_number, rek_date
		from fez_record_search_key_ismemberof AS im1
		LEFT JOIN fez_record_search_key AS sk ON rek_ismemberof_pid=rek_pid
		LEFT JOIN ".APP_TABLE_PREFIX."record_search_key_volume_number 
			ON  rek_volume_number_pid=rek_pid
		LEFT JOIN ".APP_TABLE_PREFIX."record_search_key_issue_number 
			ON  rek_issue_number_pid=rek_pid
		WHERE im1.rek_ismemberof='$pid'
			ORDER BY rek_date DESC
		";
		$list = $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		foreach ($list as $key => $item) {
			$stmt = "SELECT rek_file_attachment_name 
				FROM ".APP_TABLE_PREFIX."record_search_key_file_attachment_name 
				WHERE rek_file_attachment_name_pid='".$item['rek_pid']."' ";
			$list[$key]['files']
				= $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			foreach ($list[$key]['files'] as $fkey => $filename) {
				$list[$key]['files'][$fkey]['use'] = substr($filename['rek_file_attachment_name'], 0, strpos($filename['rek_file_attachment_name'], '_'));
			}
		}
		//Error_Handler::logError($list);
		$tpl->assign('list',$list);
	break;
	case 'collection-home':
		$tpl->setTemplate('themes/dri_browse_title.tpl.xml');
		$tpl->assign('include_manifest',true);
		$head = 'Issue';
		$stmt = "SELECT rek_pid, rek_title, rek_description, rek_issue_number, rek_volume_number, rek_date
					FROM ".APP_TABLE_PREFIX."record_search_key 
					LEFT JOIN ".APP_TABLE_PREFIX."record_search_key_volume_number 
					ON  rek_volume_number_pid=rek_pid
					LEFT JOIN ".APP_TABLE_PREFIX."record_search_key_issue_number 
					ON  rek_issue_number_pid=rek_pid
					where rek_pid='$parent_pid' ";
		$list = $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$tpl->assign('list',$list);
	break;
	case 'browse-title':
		$tpl->setTemplate('themes/dri_browse_title.tpl.xml');
		$tpl->assign('include_manifest',true);
		$head = 'Browse by Title';
	break;
	case 'search':
	$searchKey = $_REQUEST['searchKey1'];
	$query = $_REQUEST['query1'];
	$list = array();
	if (!empty($query)) {
		// get sections 
		$stmt = "select im1.rek_ismemberof_pid as section_pid
		from fez_record_search_key_ismemberof AS im1
		WHERE im1.rek_ismemberof='$parent_pid'";
		$res = $GLOBALS['db_api']->dbh->getCol($stmt);
		if ($searchKey != 'All Fields') {
			$options['searchKey'.Search_Key::getID($searchKey)] = $query;
		} else {
			$options['searchKey0'] = $query;
		}
		$options['searchKey'.Search_Key::getID('isMemberOf')] = array($parent_pid) + $res;
		$list_res = Record::getListing($options, array("Lister", "Viewer"));
		$list = $list_res['list'];
		$tpl->assign('n','search-results');
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
				WHERE rek_file_attachment_name_pid='".$item['rek_pid']."' ";
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