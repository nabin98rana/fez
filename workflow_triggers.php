<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
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
// @(#) $Id: s.new.php 1.14 03/07/11 05:04:05-00:00 jpm $
//

include_once("config.inc.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");


$tpl = new Template_API();
$tpl->setTemplate("update.tpl.html");
$tpl->assign("type", "workflow_triggers");

Auth::checkAuthentication(APP_SESSION);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);

$record_id = @$HTTP_POST_VARS["pid"] ? $HTTP_POST_VARS["pid"] : $HTTP_GET_VARS["pid"];
$pid = $record_id;

$community_list = Community::getAssocList();
$collection_list = Collection::getAssocList();

$internal_user_list = User::getAssocList();
$internal_group_list = Group::getAssocListAll();
$extra_redirect = "";

$pid = $record_id;
//if ($role_id == User::getRoleID('standard user') || ($role_id == User::getRoleID('administrator')) || ($role_id == User::getRoleID('manager'))) {
$tpl->assign("pid", $pid);
$xdis_list = XSD_Display::getAssocListDocTypes(); 
$tpl->assign("xdis_list", $xdis_list);

$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Community_Admin");
$xdis_array = Fedora_API::callGetDatastreamContentsField ($pid, 'eSpaceMD', array('xdis_id'));
$xdis_id = $xdis_array['xdis_id'][0];
if (Auth::checkAuthorisation($pid, $xdis_id, $acceptable_roles, $HTTP_SERVER_VARS['PHP_SELF']."?".$HTTP_SERVER_VARS['QUERY_STRING']) == true) {

$wfl_list = Workflow::getList();
$tpl->assign('wfl_list', Misc::keyPairs($wfl_list, 'wfl_id', 'wfl_title'));
$triggers_list = WorkflowTrigger::getTriggerTypes();
$tpl->assign('triggers_list', $triggers_list);



    
$details = Record::getDetails($pid, $xdis_id);

//print_r($datastreams);
$parents = Record::getParents($pid);
$tpl->assign("parents", $parents);

$tpl->assign("espace_root_dir", APP_PATH);

$tpl->assign("ds_get_path", APP_FEDORA_GET_URL."/".$pid."/");
$tpl->assign("isEditor", 1);
//print_r($details);
$tpl->assign("details", $details);
$setup = Setup::load();

// if user is an espace user then get prefs
if (Auth::userExists($username)) {
	$prefs = Prefs::get(Auth::getUserID());
}
$tpl->assign("user_prefs", $prefs);
//$user_details = User::getDetails(Auth::getUserID());

} else {
//	Auth::redirect(APP_RELATIVE_URL . "list.php", false);
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();


?>
