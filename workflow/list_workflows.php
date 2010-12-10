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
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.ad_hoc_sql.php");
include_once(APP_INC_PATH . "class.object_type.php");

if (empty($trigger_type)) {
    $trigger_type = 'New';
}
$log = FezLog::get();

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("trigger", $trigger_type);
$tpl->assign("type", 'update');

Auth::checkAuthentication(APP_SESSION);
$user_id = Auth::getUserID();

$xdis_id = Misc::GETorPOST('xdis_id');
$pid = Misc::GETorPOST('pid');
$pids = Misc::GETorPOST('pids');
$ahs_id = Misc::GETorPOST('ahs_id');
$dsID = Misc::GETorPOST("dsID");
$href= Misc::GETorPOST('href');
$tpl->assign("href", $href);
$cat = Misc::GETorPOST('cat');
if ($cat == 'select_workflow') {
    
    $wft_id = Misc::GETorPOST("wft_id");
    
	if (is_numeric($wft_id)) {
		
	    $wfl_id = WorkflowTrigger::getWorkflowID($wft_id);
		if (is_numeric($wfl_id)) {
			if (is_numeric($ahs_id)) {
				$pids = Ad_Hoc_SQL::getPIDs($ahs_id);
			}
		    
			if (!empty($pids) || $trigger_type == 'Bulk Change Search') {
				$log->debug('Bulk change search');
		        if (Workflow::userCanTrigger($wfl_id,$user_id)) {
		        	$log->debug('User can trigger');
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);				
				} else {
					$message = "You do not have the rights to run this workflow";					
				}
			} elseif ((empty($pid) && empty($pids)) || $pid == -2) { //workflow where the user selects the pid etc
				$log->debug('PID selected');
		        if (Workflow::userCanTrigger($wfl_id,$user_id)) {
		        	$log->debug('User can trigger');
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);				
				} else {
					$message = "You do not have the rights to run this workflow";					
				}
			} else {
				$log->debug('Other workflow');
	            if (Workflow::canTrigger($wfl_id, $pid)) {
	            	$log->debug('User can trigger');
	    			Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);	
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
