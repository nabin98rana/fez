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
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.background_process_list.php");

$tpl = new Template_API();
$tpl->setTemplate("popup.tpl.html");

Auth::checkAuthentication(APP_SESSION, 'index.php?err=5', true);
$isAdministrator = Auth::isAdministrator(); 
$usr_id = Auth::getUserID();
$cat = @$HTTP_GET_VARS["cat"] ? @$HTTP_GET_VARS["cat"] : @$HTTP_POST_VARS["cat"];

switch ($cat) 
{	


	
    case 'purge_datastream':
        {
            $dsID = $HTTP_GET_VARS["dsID"];
            $pid = $HTTP_GET_VARS["pid"];		
            $res = Fedora_API::callPurgeDatastream($pid, $dsID);
            Record::removeIndexRecordByValue($pid, $dsID);
            $thumbnail = "thumbnail_".str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))).".jpg";
            $web = "web_".str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))).".jpg";
            $preview = "preview_".str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))).".jpg";
            $FezACML_DS = "FezACML_".str_replace(" ", "_", $dsID).".xml";
            $PresMD_DS = "presmd_".str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))).".xml";
			Record::removeIndexRecord($pid, $dsID, 'keep');
            if (Fedora_API::datastreamExists($pid, $thumbnail)) {
                Fedora_API::callPurgeDatastream($pid, $thumbnail);
                Record::removeIndexRecordByValue($pid, $thumbnail);
            }
            if (Fedora_API::datastreamExists($pid, $preview)) {
				Record::removeIndexRecordByValue($pid, $preview);
                Fedora_API::callPurgeDatastream($pid, $preview);
			}
            if (Fedora_API::datastreamExists($pid, $web)) {
				Record::removeIndexRecordByValue($pid, $web);
                Fedora_API::callPurgeDatastream($pid, $web);
			}
            if (Fedora_API::datastreamExists($pid, $FezACML_DS)) {
				Record::removeIndexRecordByValue($pid, $FezACML_DS);			
                Fedora_API::callPurgeDatastream($pid, $FezACML_DS);
			}
            if (Fedora_API::datastreamExists($pid, $PresMD_DS)) {
				Record::removeIndexRecordByValue($pid, $PresMD_DS);			
                Fedora_API::callPurgeDatastream($pid, $PresMD_DS);
			}

            if (count($res) == 1) { $res = 1; } else { $res = -1; }
            $tpl->assign("purge_datastream_result", $res);
            break;
        }
    case 'update_form':
        {
            $id = Misc::GETorPOST('id');
            $wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
            $pid = $wfstatus->pid;
            $res = Record::update($pid, array("FezACML"), array(""));
            $tpl->assign("update_form_result", $res);
            $wfstatus->checkStateChange(true);
            break;
        }
    case 'update_security':
        {
            $id = Misc::GETorPOST('id');
            $wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
            $pid = $wfstatus->pid;
            $dsID = $wfstatus->dsID;
			if ($dsID != "") {
	            $res = Record::editDatastreamSecurity($pid, $dsID);			
			} else {
	            $res = Record::update($pid, array(""), array("FezACML"));
			}
            $tpl->assign("update_form_result", $res);
            $wfstatus->checkStateChange(true);
            break;
        }		
    case 'purge_object':
        {
            if ($isAdministrator) {
                // first delete all indexes about this pid
                $id = Misc::GETorPOST('id');
                $wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
                $pid = $wfstatus->pid;
                Record::removeIndexRecord($pid);
                $res = Fedora_API::callPurgeObject($pid);
                $tpl->assign("purge_object_result", $res);
                $wfstatus->checkStateChange(true);
            }
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
    case 'delete_background_processes':
        {
            $items = Misc::GETorPOST('items');
            $bgpl = new BackgroundProcessList();
            $res = $bgpl->delete($items);
            $tpl->assign('generic_result', $res);
            $tpl->assign("generic_action",'delete');
            $tpl->assign("generic_type",'background processes');
            break;
        }
	case 'purge_objects':
	        {
	            if ($isAdministrator) {
					// first delete all indexes about this pid
					$items = Misc::GETorPOST('items');
					if (empty($items)) { // is named pids on the list form
					    $items = Misc::GETorPOST('pids');
					}
					foreach ($items as $pid) {
					//                $rec_obj = new Record($pid);
					//                if ($rec_object->canDelete()) {
					        Record::removeIndexRecord($pid);
					        $res = Fedora_API::callPurgeObject($pid);
					//                }
					}
					$tpl->assign("purge_object_result", $res);
					break;
				}
	        }
    case 'publish_objects':
        {
            $items = Misc::GETorPOST('pids');
            foreach ($items as $pid) {
                $rec_obj = new RecordGeneral($pid);
                $res = $rec_obj->setStatusId(2);
            }
            $tpl->assign('generic_result', $res);
            $tpl->assign("generic_action",'publish');
            $tpl->assign("generic_type",'records');
            break;
        }

}



$tpl->assign("current_user_prefs", Prefs::get($usr_id));

$tpl->displayTemplate();
?>
