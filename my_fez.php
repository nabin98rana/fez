<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
include_once("config.inc.php");
include_once(APP_INC_PATH . "db_access.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");

include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.group.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.background_process_list.php");
include_once(APP_INC_PATH.'najax/najax.php');
include_once(APP_INC_PATH.'najax_objects/class.background_process_list.php');

$tpl = new Template_API();
$tpl->setTemplate("my_fez.tpl.html");
$options = Pager::saveSearchParams();
$search_keys = Search_Key::getMyFezSearchList();
$isMemberOf = $options['isMemberOf'];
//print_r($search_keys);
//print_r($options);
if (!array_key_exists("searchKey9", $options)) { // for the my fez unpublished items page need to set the default to any non-published items 
	$options["searchKey9"] = "-4"; 
	$options["noOrder"] = 0;
}
//if (!in_array($options["searchKey9"], $search_keys[])) {
//	$
	
//}
$assign_grp_id = $options['grp_id'];
$sta_id = $options['sta_id'];
$assign_usr_id = $options['usr_id'];
//echo $assign_usr_id." ". $sta_id." ".$assign_grp_id;

$sort_by = $options['sort_by'];
if (empty($sort_by)) {
	$sort_by = "Title";	
}

$sort_by_dir = $options['sort_by_dir'];
$sort_by_list = array();
foreach (Search_Key::getAssocList() as $key => $value) {
    $sort_by_list["searchKey".$key] = $value;
}
$tpl->assign('isMemberOf_default', $isMemberOf);
$tpl->assign('myFezView', "MAI");
$tpl->assign('extra_title', "Assigned Unpublished Items");
$tpl->assign('sort_by_list', $sort_by_list);
$tpl->assign('sort_by_dir_list', array("Asc", "Desc"));
$tpl->assign('sort_by_default', $sort_by);
$tpl->assign('sort_by_dir_default', $sort_by_dir);



Auth::checkAuthentication(APP_SESSION);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
	$prefs = Prefs::get(Auth::getUserID());
} elseif ($username != ""){
// don't require registration now for logged in users, although they can (to get prefs etc) but don't force them
//	Auth::redirect(APP_RELATIVE_URL . "register.php?err=5&username=" . $username);	
}
$tpl->assign("isAdministrator", $isAdministrator);

$collection_list = Collection::getEditList();
//print_r($collection_list);
$collection_assoc_list = array();
//$collection_assoc_list['ALL'] = '(All Assigned Collections)';
foreach ($collection_list as &$item) {
//   $item['community'] = implode(',',Misc::keyPairs(Collection::getParents2($item['pid']),'pid','title'));
  //$item['count'] = Collection::getEditListingCount($item['pid']);
//   $item['count'] = Collection::getSimpleListingCount($item['pid']);   
   $collection_assoc_list[$item['pid']] = $item['title'][0];
}
foreach ($search_keys as $skey => $svalue) {
	if ($svalue["sek_id"] == Search_Key::getID("isMemberOf")) {
		$search_keys[$skey]["field_options"] = $collection_assoc_list;
	}
	if ($svalue["sek_smarty_variable"] == 'User::getAssocList()') {
		$search_keys[$skey]["field_options"] = array("-1" => "un-assigned", "-2" => "myself", "-3" => "myself and un-assigned") + $search_keys[$skey]["field_options"];
	}			
	if ($svalue["sek_html_input"] != 'multiple' && $svalue["sek_smarty_variable"] != 'Status::getUnpublishedAssocList()') {
		$search_keys[$skey]["field_options"] = array("" => "any") + $search_keys[$skey]["field_options"];		
	}	
	if ($svalue["sek_id"]  == Search_Key::getID("Status")) {
		$search_keys[$skey]["field_options"] = array("-4" => "any Unpublished") + $search_keys[$skey]["field_options"]; //get all status's
	}	
	if ($svalue["sek_id"] == Search_Key::getID("Status")) {
		if (!array_key_exists($options["searchKey9"], $search_keys[$skey]["field_options"])) {
			$options["searchKey9"] = "-4";
		}
	}
}


$tpl->assign('my_collections_list', $collection_list);

$tpl->assign("eserv_url", APP_BASE_URL."eserv.php");

$tpl->assign('search_keys', $search_keys);

$tpl->assign("roles_list", Auth::getDefaultRoles());
$pager_row = Pager::getParam('pager_row_my_assigned');
if (empty($pager_row)) {
    $pager_row = 0;
}
$rows = Pager::getParam('rows');
if (empty($rows)) {
    $rows = APP_DEFAULT_PAGER_SIZE;
}

$status_list = Status::getAssocList();

$grp_list = Group::getAssocListAll();
if (is_numeric($grp_id)) {
	$usr_list = Group::getUserAssocList($usr_id);
} else {
	$usr_list = User::getAssocList();
}
$bulk_workflows = WorkflowTrigger::getAssocListByTrigger("-1", 7); //get the bulk change workflows
$tpl->assign("bulk_workflows", $bulk_workflows);

if (Misc::GETorPost("search_button") == "Search" && trim($options["searchKey0"]) != "") { //if search button was just pressed and all fields search has something in it then sort by relevance enforced
	$options["sort_by"] = "searchKey0";
}

if ($options["searchKey0"] != "" && (Misc::GETorPost("sort_by") == "" || $options["sort_by"] == "searchKey0")) {
	$options["sort_order"] = 1;
}

$tpl->assign("options", $options);
$tpl->assign("grp_list", $grp_list);
$tpl->assign("status_list", $status_list);
$tpl->assign("usr_list", $usr_list);
$tpl->assign("isMemberOf_list", $collection_assoc_list);
//$assigned_items= Record::getAssigned(Auth::getUsername(), $pager_row, $rows, $sort_by, $sort_by_dir, $isMemberOf);

$assigned_items = Record::getListing($options, array("Editor", "Approver"), $pager_row, $rows, $options["sort_by"]);

foreach ($assigned_items['list'] as $aikey => $aidata) {
	$assigned_items['list'][$aikey]['parents'] = Record::getParents($aidata['pid']);
}

$tpl->assign('my_assigned_items_list', $assigned_items['list']);
$tpl->assign('my_assigned_items_info', $assigned_items['info']);

$tpl->assign('najax_header', NAJAX_Utilities::header(APP_RELATIVE_URL.'include/najax'));
$tpl->registerNajax( NAJAX_Client::register('NajaxBackgroundProcessList', APP_RELATIVE_URL.'najax_services/generic.php'));

$tpl->displayTemplate();

//$bench->display(); // to output html formated
?>