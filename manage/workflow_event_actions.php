<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

$wfl_id = @$HTTP_POST_VARS['wfl_id'] ? $HTTP_POST_VARS['wfl_id'] : $HTTP_GET_VARS['wfl_id'];
$wfa_id = @$HTTP_POST_VARS['wfa_id'] ? $HTTP_POST_VARS['wfa_id'] : $HTTP_GET_VARS['wfa_id'];
$wfe_id = @$HTTP_POST_VARS['wfe_id'] ? $HTTP_POST_VARS['wfe_id'] : $HTTP_GET_VARS['wfe_id'];

if ($isSuperAdministrator) {
  
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
	$tpl->assign("wfe_title", Workflow_Event::getTitle($wfe_id));
	$tpl->assign("wfl_title", Workflow::getTitle($wfl_id));
	$tpl->assign("wfl_id", $wfl_id);
	$tpl->assign("wfe_id", $wfe_id);
	$tpl->assign("wfa_id", $wfa_id);
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>