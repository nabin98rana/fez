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
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.workflow_state.php");
include_once(APP_INC_PATH . "class.wfbehaviours.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.workflow_state_link.php");
include_once(APP_INC_PATH . "class.graphviz.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "workflow_states");

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$wfl_id = @$HTTP_POST_VARS['wfl_id'] ? $HTTP_POST_VARS['wfl_id'] : @$HTTP_GET_VARS['wfl_id'];
$wfs_id = @$HTTP_POST_VARS['wfs_id'] ? $HTTP_POST_VARS['wfs_id'] : @$HTTP_GET_VARS['wfs_id'];

if ($isAdministrator) {
  
    if (@$HTTP_POST_VARS["cat"] == "new") {
        $tpl->assign("result", Workflow_State::insert());
    } elseif (@$HTTP_POST_VARS["cat"] == "update") {
        $tpl->assign("result", Workflow_State::update());
    } elseif (@$HTTP_POST_VARS["cat"] == "delete") {
        Workflow_State::remove();
    }

    if (@$HTTP_GET_VARS["cat"] == "edit") {
        $info = Workflow_State::getDetails($wfs_id);
        if (empty($info['next_ids'])) {
            $info['next_ids'] = array(-1);
        }
        if (empty($info['prev_ids'])) {
            $info['prev_ids'] = array(-1);
        }
    } else {
        $info['next_ids'] = array(-1);
        $info['prev_ids'] = array(-1);
    }
    $tpl->assign("info", $info);

    $states = Workflow_State::getList($wfl_id);
    $states_list = Misc::keyPairs($states, 'wfs_id', 'wfs_title');
    $tpl->assign("list", $states);
    $tpl->assign("states_list", array('-1' => 'None') + $states_list);
    $dot = WorkflowStateLink::getDot($wfl_id, APP_BASE_URL."manage/workflow_states.php?cat=edit&wfl_id=$wfl_id&wfs_id=@id@");
    $dot_id = md5($dot);
    $_SESSION['dot'][$dot_id] = $dot; 
    $tpl->assign("encoded_dot", $dot_id); 
    $map = Graphviz::getCMAPX($dot);
    $tpl->assign('cmapx', $map); 
    $map_name = Graphviz::getGraphName($dot);
    $tpl->assign('map_name', $map_name); 
    $tpl->assign("auth_role_options", Auth::getAssocRoleIDs());
    
    $link_check = WorkflowStateLink::checkLinks($wfl_id);
    $tpl->assign("link_check", $link_check);
	$tpl->assign("wfl_title", Workflow::getTitle($wfl_id));
	$tpl->assign("wfl_id", $wfl_id);
    $behaviours = Misc::keyPairs(WF_Behaviour::getListManual(), 'wfb_id', 'wfb_title');
    $tpl->assign("behaviours_list", $behaviours);
    $behaviours_auto = Misc::keyPairs(WF_Behaviour::getListAuto(), 'wfb_id', 'wfb_title');
    $tpl->assign("behaviours_list_auto", $behaviours_auto);
    $tpl->assign("roles_list", Auth::getDefaultRoles());
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>
