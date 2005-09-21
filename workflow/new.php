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
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.collection.php");

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("trigger", 'Create');
$tpl->assign("type", 'new');

Auth::checkAuthentication(APP_SESSION);
//$user_id = Auth::getUserID();

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

$xdis_id = @$HTTP_POST_VARS["xdis_id"] ? $HTTP_POST_VARS["xdis_id"] : @$HTTP_GET_VARS["xdis_id"];	
$collection_pid = @$HTTP_POST_VARS["collection_pid"] ? $HTTP_POST_VARS["collection_pid"] : @$HTTP_GET_VARS["collection_pid"];	
$community_pid = @$HTTP_POST_VARS["community_pid"] ? $HTTP_POST_VARS["community_pid"] : @$HTTP_GET_VARS["community_pid"];	

if (@$HTTP_POST_VARS["cat"] == 'select_workflow') {
    $wft_id = $HTTP_POST_VARS["wft_id"];
    $pid = $HTTP_POST_VARS["pid"];
    Workflow::start($wft_id, $pid);
}


$pid = $collection_pid ? $collection_pid : $community_pid;
$wfl_list = Misc::keyPairs(Workflow::getList(), 'wfl_id', 'wfl_title');
if (!empty($pid) && (!Misc::isInt($pid) || $pid != -1)) {
    $tpl->assign("pid", $pid);

    $record = new RecordObject($pid);
    if ($record->canCreate()) {
        $tpl->assign("isCreator", 1);
        if ($record->isCommunity()) {
            $workflows = WorkflowTrigger::getListByTriggerAndXDis_Id(-1, 'Create', Collection::getCollectionXDIS_ID());
        } elseif ($record->isCollection()) {
            $workflows = $record->getWorkflowsByTrigger('Create');
            $xdis_list = array(-1 => 'Any') + XSD_Display::getAssocListDocTypes(); 
            foreach ($workflows as $wft) {
                $xdis_name = $xdis_list[$wft['wft_xdis_id']];
                $wfl_title = $wfl_list[$wft['wft_wfl_id']];
                $workflows_op[$wft['wft_id']] = "$wfl_title ($xdis_name)";
            }
            $tpl->assign('workflows_op', $workflows_op);
        } else {
            $tpl->assign('message', "Error: can't create objects into ordinary records<br/>");
        }
        $tpl->assign('workflows', $workflows);
    }
} else {
    $tpl->assign("pid", '-1');
    // community level create 
    // get defaults triggers
    $workflows = WorkflowTrigger::getListByTriggerAndXDis_Id(-1, 'Create', Community::getCommunityXDIS_ID());
    $tpl->assign('workflows', $workflows);
    foreach ($workflows as $wft) {
        $wfl_title = $wfl_list[$wft['wft_wfl_id']];
        $workflows_op[$wft['wft_id']] = $wfl_title;
    }
    $tpl->assign('workflows_op', $workflows_op);
}
if (empty($workflows_op)) {
    $tpl->assign('message', 'Error: No workflows defined for Create');
}

$tpl->displayTemplate();
?>
