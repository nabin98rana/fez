<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | eSpace                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.list.php 1.16 03/10/14 15:38:03-00:00 jpradomaia $
//


include_once("config.inc.php");
include_once(APP_INC_PATH . "db_access.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");

include_once(APP_INC_PATH . "class.misc.php");

include_once(APP_INC_PATH . "class.record.php");

include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");

include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.user.php");
//include_once(APP_INC_PATH . "class.news.php");

$tpl = new Template_API();
$tpl->setTemplate("list.tpl.html");

// CK turned authentication off for now for eSpace, now back on for testing
// Auth::checkAuthentication(APP_SESSION);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);



/*$pagerRow = Record::getParam('pagerRow');
if (empty($pagerRow)) {
    $pagerRow = 0;
}
$rows = Record::getParam('rows');
if (empty($rows)) {
    $rows = APP_DEFAULT_PAGER_SIZE;
}
$usr_id = Auth::getUserID();
*/
//$options = Record::saveSearchParams();

//$tpl->assign("options", $options);
//$tpl->assign("sorting", Record::getSortingInfo($options));

//$col_id = Auth::getCurrentCollection();
//print_r($options);
$terms = @$_REQUEST['terms'];

$collection_pid = @$HTTP_POST_VARS["collection_pid"] ? $HTTP_POST_VARS["collection_pid"] : @$HTTP_GET_VARS["collection_pid"];	
$community_pid = @$HTTP_POST_VARS["community_pid"] ? $HTTP_POST_VARS["community_pid"] : @$HTTP_GET_VARS["community_pid"];


if (!empty($collection_pid)) {
    // list a collection
	$tpl->assign("xdis_id", Record::getRecordXDIS_ID());
	$collection_details = Collection::getDetails($collection_pid);
	$parents = Collection::getParents($collection_pid);
	$tpl->assign("parents", $parents);
	$collection_xdis_id = Collection::getCollectionXDIS_ID();
	$userPIDAuthGroups = Auth::getAuthorisationGroups($collection_pid, $collection_xdis_id);
	$isCreator = (in_array("Creator", $userPIDAuthGroups) || in_array("Community Administrator", $userPIDAuthGroups) || in_array("Collection Administrator", $userPIDAuthGroups));
	$tpl->assign("isCreator", $isCreator);
	$isEditor = (in_array("Creator", $userPIDAuthGroups) || in_array("Community Administrator", $userPIDAuthGroups) || in_array("Editor", $userPIDAuthGroups) || in_array("Collection Administrator", $userPIDAuthGroups));
	$tpl->assign("isEditor", $isEditor);
	$list = Collection::getListing($collection_pid);
	$tpl->assign("list_heading", "List of Records in ".$collection_details[0]['title']." Collection");
	$tpl->assign("list_type", "collection_records_list");
	$tpl->assign("collection_pid", $collection_pid);
} elseif (!empty($community_pid)) {
    // list collections in a community

	$tpl->assign("community_pid", $community_pid);
	$xdis_id = Collection::getCollectionXDIS_ID();
	$community_xdis_id = Community::getCommunityXDIS_ID();

	$userPIDAuthGroups = Auth::getAuthorisationGroups($community_pid, $community_xdis_id);

	$isCreator = (in_array("Creator", $userPIDAuthGroups));
	$tpl->assign("isCreator", $isCreator);
	$isEditor = (in_array("Creator", $userPIDAuthGroups) || in_array("Community Administrator", $userPIDAuthGroups) || in_array("Editor", $userPIDAuthGroups));
	$tpl->assign("isEditor", $isEditor);
	$tpl->assign("xdis_id", $xdis_id);	
	$community_details = Community::getDetails($community_pid);

	$list = Collection::getList($community_pid);
	$tpl->assign("list_heading", "List of Collections in ".$community_details[0]['title']." Community");
	$tpl->assign("list_type", "collection_list");
} elseif (!empty($terms)) {
    // search eSpace
	$list = Record::getListing($terms, 1, 100);
	$tpl->assign("list_heading", "Search Results ($terms)");
	$tpl->assign("list_type", "all_records_list");
} else {
    // list all communities
	$xdis_id = Community::getCommunityXDIS_ID();
	$tpl->assign("xdis_id", $xdis_id);	
	$list = Community::getList();
	$tpl->assign("list_type", "community_list");
	$tpl->assign("list_heading", "List of Communities");
}

//$list = Record::getListing($options, 1, 100);
//print_r($list);
//echo $list; // xslt'ed xml string
//$tpl->assign("col_id", $col_id);
//$tpl->assign("projects", Project::getAssocList($usr_id));
//$tpl->assign("collections", Collection::getAllSorted());
$tpl->assign("list", $list);
//$tpl->assign("list", $list["list"]);
//$tpl->assign("list_info", $list["info"]);
//$tpl->assign("csv_data", base64_encode($list["csv"]));

// @@@ 21/7/2004 CK - Added $custom_modified to sort library branches alphabetically, and any other custom fields as needed
//$custom_modified = array();
//$custom_modified = (Custom_Field::getListByProject($col_id, 'report_form'));
//asort($custom_modified[1]['field_options']);
//$tpl->assign("custom_fields", $custom_modified);
//$tpl->assign("priorities", Misc::getPriorities());
//$tpl->assign("status", Status::getAssocStatusList($col_id));
//$tpl->assign("users", Collection::getUserAssocList($col_id, 'active', User::getRoleID('Reporter')));
//$tpl->assign("custom", Filter::getAssocList($col_id));

//$tpl->assign("csts", Filter::getListing($col_id));
//$tpl->assign("categories", Category::getAssocList($col_id));
//@@@ CK - 7/9/2004 - Added time tracking list so could search by time tracking cats
//$tpl->assign("time_categories", Time_Tracking::getAssocCategories());

//$tpl->assign("news", News::getListByProject($col_id));

if (Auth::userExists($username)) {
	$prefs = Prefs::get(Auth::getUserID());
}
//$tpl->assign("refresh_rate", @$prefs['list_refresh_rate'] * 60);
//$tpl->assign("refresh_page", "list.php");

$tpl->displayTemplate();
?>
