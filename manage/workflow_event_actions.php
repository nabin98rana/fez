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
// @(#) $Id: s.doc_type_xsds.php 1.2 03/07/14 04:55:26-00:00 jpm $
//
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.workflow_event.php");
include_once(APP_INC_PATH . "class.workflow_event_action.php");
include_once(APP_INC_PATH . "class.wfbehaviours.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "workflow_event_actions");

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$wfl_id = @$HTTP_POST_VARS['wfl_id'] ? $HTTP_POST_VARS['wfl_id'] : $HTTP_GET_VARS['wfl_id'];
$wfa_id = @$HTTP_POST_VARS['wfa_id'] ? $HTTP_POST_VARS['wfa_id'] : $HTTP_GET_VARS['wfa_id'];
$wfe_id = @$HTTP_POST_VARS['wfe_id'] ? $HTTP_POST_VARS['wfe_id'] : $HTTP_GET_VARS['wfe_id'];

if ($isAdministrator) {
  
    if (@$HTTP_POST_VARS["cat"] == "new") {
        $tpl->assign("result", Workflow_Event_Action::insert());
    } elseif (@$HTTP_POST_VARS["cat"] == "update") {
        $tpl->assign("result", Workflow_Event_Action::update());
    } elseif (@$HTTP_POST_VARS["cat"] == "delete") {
        Workflow::remove();
    }

    if (@$HTTP_GET_VARS["cat"] == "edit") {
        $tpl->assign("info", Workflow_Event_Action::getDetails($wfa_id));
    }

    $tpl->assign("list", Workflow_Event_Action::getList($wfe_id));
	$tpl->assign("event_actions", WF_Behaviour::getTitles());
//	$tpl->assign("event_types", Workflow_Event::getEventTypeList());
	$tpl->assign("wfe_title", Workflow_Event::getTitle($wfe_id));
	$tpl->assign("wfl_title", Workflow::getTitle($wfl_id));
	$tpl->assign("wfl_id", $wfl_id);
	$tpl->assign("wfe_id", $wfe_id);
	$tpl->assign("wfa_id", $wfa_id);

//    $tpl->assign("collection_list", Collection::getAll());
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>