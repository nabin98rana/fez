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
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.object_type.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.collection.php");

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("trigger", 'Create');
$tpl->assign("type", 'new');

$xdis_id = $_REQUEST['xdis_id'];
$ret_id = $_REQUEST['ret_id'];
$collection_pid = $_REQUEST['collection_pid'];
$community_pid = $_REQUEST['community_pid'];
$pid = $_REQUEST["pid"];
$href = $_REQUEST['href'];
$cat = $_REQUEST['cat'];
$custom_view_pid = $_REQUEST['custom_view_pid'];


Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?"."xdis_id=".$xdis_id."&ret_id=".$ret_id."&collection_pid=".$collection_pid."&href=".$href."&community_pid=".$community_pid."&pid=".$pid."&custom_view_pid=".$custom_view_pid."&cat=".$cat);
$user_id = Auth::getUserID();

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

$tpl->assign("href", $href);

$cat = $_REQUEST['cat'];
if ($cat == 'select_workflow') {
    $wft_id = $_REQUEST["wft_id"];
	if (is_numeric($wft_id)) {
		$wfl_id = WorkflowTrigger::getWorkflowID($wft_id);
		if (is_numeric($wfl_id)) {
			if (!empty($pids) || $trigger_type == 'Bulk Change Search') {
		        if (Workflow::userCanTrigger($wfl_id,$user_id)) {
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids, $custom_view_pid);				
				} else {
					$message = "You do not have the rights to run this workflow";					
				}
			} elseif ((empty($pid) && empty($pids)) || $pid == -2) { //workflow where the user selects the pid etc
		        if (Workflow::userCanTrigger($wfl_id,$user_id)) {
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids, $custom_view_pid);
				} else {
					$message = "You do not have the rights to run this workflow";					
				}
			} else {
	            if (Workflow::canTrigger($wfl_id, $pid)) {
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids, $custom_view_pid);	
				} else {
					$message = "You do not have the rights to run this workflow";					
				}
			}	
		} else {
			$message = "No workflow found for given trigger";
		}
	} else {
		$message = "Workflow trigger must be numeric";
	}

}

$message = '';
if (empty($pid)) {
    $pid = $collection_pid ? $collection_pid : $community_pid;
}
$wfl_list = Misc::keyArray(Workflow::getList(), 'wfl_id');
$xdis_list = array(-1 => 'Any') + XSD_Display::getAssocListDocTypes(); 
$tpl->assign('wfl_list', $wfl_list);
$tpl->assign('xdis_list', $xdis_list);

if ($pid == -1) {
    $tpl->assign("pid", '-1');
    $pid = -1;
    // community level create 
    // get defaults triggers
    $ret_id = Object_Type::getID('Community');
    $workflows = WorkflowTrigger::getFilteredList(-1, array( 
                'trigger' => 'Create', 
                'ret_id' => $ret_id));
    foreach ($workflows as $trigger) {
        if (Workflow::userCanTrigger($trigger['wft_wfl_id'],$user_id, array('Creator'))) {
            $workflows1[] = $trigger;
        }
    }
    $workflows = $workflows1; 
    $tpl->assign('workflows', $workflows);
} elseif (empty($pid) || $pid == -2) {
    $pid = -2;
    // find workflows that select a pid as they go
    $workflows = WorkflowTrigger::getFilteredList(-1, array(
            'trigger' => 'Create', 
            'xdis_id' => -2,
            'strict_xdis' => true,
            'any_ret' => true));
    foreach ($workflows as $trigger) {
        if (Workflow::userCanTrigger($trigger['wft_wfl_id'],$user_id, array('Creator'))) {
            $workflows1[] = $trigger;
        }
    }
    $workflows = $workflows1; 
    $tpl->assign('workflows', $workflows);
    $tpl->assign("pid", $pid);
} else {
    $tpl->assign("pid", $pid);

    $record = new RecordObject($pid);
    if ($record->canCreate()) {
        $tpl->assign("isCreator", 1);
        if ($record->isCommunity()) {
            $ret_id = Object_Type::getID('Collection');
            $workflows = WorkflowTrigger::getFilteredList(-1, array(
                    'trigger' => 'Create', 
                    'ret_id' => $ret_id));
        } elseif ($record->isCollection()) {
            $ret_id = Object_Type::getID('Record');
            $workflows = WorkflowTrigger::getFilteredList(-1, array(
                        'trigger' => 'Create', 
                        'xdis_id' => $xdis_id,
                        'ret_id' => $ret_id));
        } else {
            $message .= "Error: can't create objects into ordinary records<br/>";
        }
        
    } else {
    }
    $tpl->assign('xdis_id', $xdis_id);
    // check which workflows can be triggered
    if (!empty($pid) && !$isAdministrator) {
        foreach ($workflows as $trigger) {
            if (Workflow::canTrigger($trigger['wft_wfl_id'], $pid)) {
                $workflows1[] = $trigger;
            }
        }
        $workflows = $workflows1;
    }
    $tpl->assign('workflows', $workflows);
}


if (empty($workflows)) {
    $message .= "Error: No workflows defined for Create<br/>";
} elseif (count($workflows) == 1) {
    // no need for user to select a workflow - just start the only one available
//    Workflow::start($workflows[0]['wft_id'], $pid, $xdis_id, $href);
	$wft_id = $workflows[0]['wft_id'];
	if (is_numeric($wft_id)) {
		$wfl_id = WorkflowTrigger::getWorkflowID($wft_id);
		if (is_numeric($wfl_id)) {
			if (!empty($pids) || $trigger_type == 'Bulk Change Search') {
		        if (Workflow::userCanTrigger($wfl_id,$user_id)) {
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids, $custom_view_pid);				
				} else {
					$message = "You do not have the rights to run this workflow";
				}
			} elseif ((empty($pid) && empty($pids)) || $pid == -2) { //workflow where the user selects the pid etc
		        if (Workflow::userCanTrigger($wfl_id,$user_id)) {
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids, $custom_view_pid);				
				} else {
					$message = "You do not have the rights to run this workflow";					
				}
			} else {
	            if (Workflow::canTrigger($wfl_id, $pid)) {
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids, $custom_view_pid);	
				} else {
					$message = "You do not have the rights to run this workflow";					
				}
			}	
		} else {
			$message = "No workflow found for given trigger";
		}
	} else {
		$message = "Workflow trigger must be numeric";
	}

}

$tpl->assign('message', $message);
$tpl->displayTemplate();
?>
