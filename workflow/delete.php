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
$tpl->assign("trigger", 'Delete');
$tpl->assign("type", 'delete');

Auth::checkAuthentication(APP_SESSION);
//$user_id = Auth::getUserID();

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

$xdis_id = Misc::GETorPOST('xdis_id');
$pid = Misc::GETorPOST('pid');

$cat = Misc::GETorPOST('cat');
if ($cat == 'select_workflow') {
    $wft_id = Misc::GETorPOST("wft_id");
    $pid = Misc::GETorPOST("pid");
    Workflow::start($wft_id, $pid, $xdis_id);
}

$message = '';
$wfl_list = Misc::keyPairs(Workflow::getList(), 'wfl_id', 'wfl_title');
$xdis_list = array(-1 => 'Any') + XSD_Display::getAssocListDocTypes(); 
$tpl->assign('wfl_list', $wfl_list);
$tpl->assign('xdis_list', $xdis_list);
if (!(empty($pid) || $pid == -1)) {
    $tpl->assign("pid", $pid);

    $record = new RecordObject($pid);
    if ($record->canEdit()) {
        $tpl->assign("isEditor", 1);
        $xdis_id = $record->getXmlDisplayId();
        // don't be flexible on doc types for collection and community
        if ($record->isCommunity() || $record->isCollection()) {
            $strict = true;
        } else {
            $strict = false;
        }
        $workflows = $record->getWorkflowsByTriggerAndXDIS_ID('Delete', $xdis_id, $strict);
        print_r($workflows);
        $tpl->assign('workflows', $workflows);
    } else {
    }
    $tpl->assign('xdis_id', $xdis_id);
}
if (empty($workflows)) {
    $message .= "Error: No workflows defined for Delete<br/>";
} elseif (count($workflows) == 1) {
    // no need for user to select a workflow - just start the only one available
    Workflow::start($workflows[0]['wft_id'], $pid, $xdis_id);
}

$tpl->assign('message', $message);
$tpl->displayTemplate();
?>
