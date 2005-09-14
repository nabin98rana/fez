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
$tpl->assign("type_name", "workflow trigger");

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);

$record_id = Misc::GETorPOST('pid');
$cat = Misc::GETorPOST('cat');
$pid = $record_id;
if (!empty($pid)) {
    $tpl->assign("pid", $pid);
    $record = new RecordObject($pid);
    $xdis_id = $record->getXmlDisplayId();

    //echo "XDIS_ID -> ".$xdis_id;
    if (!is_numeric($xdis_id)) {
        $xdis_id = Misc::GETorPOST('xdis_id');
        if (is_numeric($xdis_id)) { // must have come from select xdis so save xdis in the eSpace MD
            $record->updateAdminDatastream($xdis_id);
        }
    }
    if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
        //	echo "XDIS_ID -> ".$xdis_id;
        //	echo "redirecting";
        Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=view_form&pid=".$pid.$extra_redirect, false);
    }

    $tpl->assign("isEditor", $record->canEdit());
}
if ($record && $record->canEdit()) {

    $internal_user_list = User::getAssocList();
    $internal_group_list = Group::getAssocListAll();
    $extra_redirect = "";

    $wfl_list = Workflow::getList();
    $tpl->assign('wfl_list', Misc::keyPairs($wfl_list, 'wfl_id', 'wfl_title'));
    $triggers_list = WorkflowTrigger::getTriggerTypes();
    $tpl->assign('triggers_list', $triggers_list);
    $xdis_list = XSD_Display::getAssocListDocTypes(); 
    $tpl->assign('xdis_list', $xdis_list);
    $details = $record->getDetails();
    $tpl->assign("details", $details);
    $tpl->assign('title', $record->getTitle());
    $list = WorkflowTrigger::getList($pid);
    $tpl->assign('list', $list);

    if ($cat == 'edit') {
        $wft_id = Misc::GETorPOST('wft_id');
        $info = WorkflowTrigger::getDetails($wft_id);
        $tpl->assign('info', $info);
    }

    
    // show number of triggers
    $tpl->assign('triggers', count($list));
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
