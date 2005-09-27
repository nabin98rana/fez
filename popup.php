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
// | Authors: Jo�o Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.popup.php 1.25 04/01/23 03:42:02-00:00 jpradomaia $
//
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");

$tpl = new Template_API();
$tpl->setTemplate("popup.tpl.html");

Auth::checkAuthentication(APP_SESSION, 'index.php?err=5', true);
$usr_id = Auth::getUserID();
$cat = @$HTTP_GET_VARS["cat"] ? @$HTTP_GET_VARS["cat"] : @$HTTP_POST_VARS["cat"];

switch ($cat) 
{
    case 'purge_datastream':
        {
            if (!in_array($HTTP_GET_VARS["ds_id"], Misc::const_array(APP_FEDORA_PROTECTED_DATASTREAMS))) {
                $ds_id = $HTTP_GET_VARS["ds_id"];
                $pid = $HTTP_GET_VARS["pid"];		
                $res = Fedora_API::callPurgeDatastream($pid, $ds_id);
                Record::removeIndexRecordByValue($pid, $ds_id);
                $thumbnail = "thumbnail_".str_replace(" ", "_", substr($ds_id, 0, strrpos($ds_id, "."))).".jpg";
                if (Fedora_API::datastreamExists($pid, $thumbnail)) {
                    Fedora_API::callPurgeDatastream($pid, $thumbnail);
                    Record::removeIndexRecordByValue($pid, $thumbnail);
                }
                if (count($res) == 1) { $res = 1; } else { $res = -1; }
            } else {
                $res = -1;
            }
            $tpl->assign("purge_datastream_result", $res);
            break;
        }
    case 'update_form':
        {
            $pid = Misc::GETorPOST('pid');
            $res = Record::update($pid);
            $tpl->assign("update_form_result", $res);
            $wfstatus = WorkflowStatusStatic::getSession($pid); // restores WorkflowStatus object from the session
            $wfstatus->checkStateChange(true);
            break;
        }
    case 'purge_object':
        {
            // first delete all indexes about this pid
            $pid = Misc::GETorPOST('pid');
            Record::removeIndexRecord($pid);
            $res = Fedora_API::callPurgeObject($pid);
            $tpl->assign("purge_object_result", $res);
            $wfstatus = WorkflowStatusStatic::getSession($pid); // restores WorkflowStatus object from the session
            $wfstatus->checkStateChange(true);
            break;
        }
    case 'new_workflow_triggers':
        {
            $tpl->assign("generic_result",WorkflowTrigger::insert());
            $tpl->assign("generic_action",'add');
            $tpl->assign("generic_type",'workflow trigger');
            break;
        }
    case 'edit_workflow_triggers':
        {
            $tpl->assign("generic_result",WorkflowTrigger::update());
            $tpl->assign("generic_action",'update');
            $tpl->assign("generic_type",'workflow trigger');
            break;
        }
    case 'list_action_workflow_triggers':
        {
            if (Misc::GETorPOST('delete')) {
                $tpl->assign("generic_result",WorkflowTrigger::remove());
                $tpl->assign("generic_action",'delete');
            }
            $tpl->assign("generic_type",'workflow trigger');
            break;
        }


}



$tpl->assign("current_user_prefs", Prefs::get($usr_id));

$tpl->displayTemplate();
?>
