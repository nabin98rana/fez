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
include_once(APP_INC_PATH.'db_access.php');
include_once(APP_INC_PATH.'class.wfbehaviours.php');
include_once(APP_INC_PATH.'class.workflow_state.php');
include_once(APP_INC_PATH.'class.workflow_trigger.php');
include_once(APP_INC_PATH.'class.foxml.php');
include_once(APP_INC_PATH . 'class.bgp_generate_duplicates_report.php');
include_once(APP_INC_PATH . 'class.bgp_duplicates_report_merge_isi_loc.php');
include_once(APP_INC_PATH . 'class.template.php');
include_once(APP_INC_PATH . 'class.mail.php');

/**
 * for tracking status of objects in workflows.  This is like the runtime part of the workflows.
 */
class WorkflowStatus {
    var $wfs_id;
    var $pid;
    var $pids = array();    
    var $dsID;
    var $xdis_id;
    var $wft_id;
    var $wfl_details;
    var $wfs_details;
    var $wft_details;
    var $wfb_details;
    var $dsInfo;
    var $change_on_refresh;
    var $end_on_refresh;
    var $parents_list;
    var $parent_pid;
    var $vars = array(); // associative array for storing workflow variables between states
    var $rec_obj;
    var $href;
    var $states_done = array();
    var $record_title;
    //var $request_params; // copy of $_REQUEST when workflow was started
    
    /**
     * Constructor.   These variables don't really need to be set depending on 
     * how the workflow is triggered.  Sometimes nothing is known at the start of the
     * workflow lik ehwen the user clicks 'create' on the My_Fez page.
     */
    function WorkflowStatus($pid=null, $wft_id, $xdis_id=null, $dsInfo=null, $dsID='', $pids=array())
    {
        $this->pid = $pid;
        $this->pids = $pids;        
        $this->dsID = $dsID;
        $this->wft_id= $wft_id;
        $this->xdis_id= $xdis_id;
        $this->dsInfo = $dsInfo;
        //$this->request_params = $_REQUEST;
        $this->id = $this->newDBSession();
        $this->setSession();
    }

    function newDBSession()
    {
        $usr_id = Auth::getUserID();
        $dbtp =  APP_TABLE_PREFIX;
        $stmt = "INSERT INTO ".$dbtp."workflow_sessions (wfses_usr_id, wfses_listing, wfses_date) " .
                "VALUES ('".$usr_id."','',NOW())";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        return $GLOBALS["db_api"]->get_last_insert_id();
    }

    /**
     * Get an object for the pid that the workflow is working on
     */
    function getRecordObject()
    {
        if (!$this->rec_obj || $this->rec_obj->pid != $this->pid) {
            $this->rec_obj = new RecordObject($this->pid);
        }
        return $this->rec_obj;
    }

    function getRecordTitle()
    {
    	if (empty($this->record_title)) {
	        if ($this->pid && !is_numeric($this->pid)) {
    			$rec = $this->getRecordObject();
    			$this->record_title = $rec->getTitle();
			}
		}
		return $this->record_title;
    }

    /**
     * Saves this object in the workflow_sessions db table
     */
    function setSession()
    {
        $this->getWorkflowDetails();
        $this->getStateDetails();
        $title = $this->wfl_details['wfl_title'].": ".$this->wfs_details['wfs_title'];
        if ($this->pid && !is_numeric($this->pid)) {
            $title .= " on ".$this->pid.": ". $this->getRecordTitle();
        }
        $date = Date_API::getCurrentDateGMT();
        $usr_id = Auth::getUserID();
        $id = $this->id;
        // get rid of some stuff to cut the size of the serialised object down
        $wfs_details = $this->wfs_details; 
        $wfl_details = $this->wfl_details; 
        $wfb_details = $this->wfb_details; 
        $rec_obj = $this->rec_obj; 
        $this->wfs_details = null;
        $this->wfl_details = null;
        $this->wfb_details = null;
        $this->rec_obj = null;
        $title = Misc::escapeString($title);
        $blob = Misc::escapeString(serialize($this));
        if (strlen($blob) > 64000) {
        	Error_Handler::logError("Maximum size of workflow session data exceeded", __FILE__,__LINE__);
        }
        $dbtp =  APP_TABLE_PREFIX;
        $stmt = "UPDATE ".$dbtp."workflow_sessions " .
                "SET wfses_object='".$blob."', wfses_listing='".$title."', wfses_date='".$date."' " .
                "WHERE wfses_id='".$id."' AND wfses_usr_id='".$usr_id."' ";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = -1;
        } else {
            $res = 1;
        }
        $this->wfs_details = $wfs_details; 
        $this->wfl_details = $wfl_details; 
        $this->wfb_details = $wfb_details; 
        $this->rec_obj = $rec_obj; 
        return $res;
    }

    /**
     * Deletes this object from the session variable
     */
    function clearSession()
    {
        $usr_id = Auth::getUserID();
        $id = $this->id;
        $dbtp =  APP_TABLE_PREFIX;
        $stmt = "DELETE FROM ".$dbtp."workflow_sessions " .
                "WHERE wfses_id='".$id."' AND wfses_usr_id='".$usr_id."' ";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        return 1;
    }

    /**
     * Clear member copies of the workflow state details
     */
    function clearStateDetails()
    {
        $this->wfs_details = null;
        $this->wfb_details = null;
    }

    /**
     * Sets the currently executing state
     * @param integer $wfs_id The new state to be in
     */
    function setState($wfs_id)
    {
        if ($this->wfs_id != $wfs_id) {
            $this->wfs_id = $wfs_id;
            $this->clearStateDetails();
        }
    }

    /**
     * Get a member copy of the trigger that started the workflow. 
     */
    function getTriggerDetails()
    {
        if (!$this->wft_details) {
            $this->wft_details = WorkflowTrigger::getDetails($this->wft_id);
            $wft_type = WorkflowTrigger::getTriggerName($this->wft_details['wft_type_id']);
            $this->parent_pid = null;
            $this->parents_list = null;
            if ($wft_type == 'Create') {
                $this->parent_pid = $this->pid;
            } elseif (($wft_type != 'Ingest') && ($this->dsID == "")) {
                $this->getRecordObject();
                $this->parents_list = $this->rec_obj->getParents();
            }
        }
        return $this->wft_details;
    }

    /**
     * get a member of copy fo the current state
     */
    function getStateDetails()
    {
        if (!$this->wfs_details) {
            if (!$this->wfs_id) {
                // need to start the workflow
                $this->getTriggerDetails();
                $this->wfs_details = Workflow_State::getStartState($this->wft_details['wft_wfl_id']);
                $this->setState($this->wfs_details['wfs_id']);
            }
            $this->wfs_details = Workflow_State::getDetails($this->wfs_id);
        }
        return $this->wfs_details;
    }

    /**
     * get a member copy of the current workflow
     */
    function getWorkflowDetails()
    {
        if (!$this->wfl_details) {
            $this->getTriggerDetails();
            $this->wfl_details = Workflow::getDetails($this->wft_details['wft_wfl_id']);
        }
        return $this->wfl_details;
    } 

    /**
     * Get a member copy of the behaviour details for the current state
     */
    function getBehaviourDetails()
    {
        if (!$this->wfb_details) {
            $this->getStateDetails();
            $this->wfb_details = WF_Behaviour::getDetails($this->wfs_details['wfs_wfb_id']);
        }
        return $this->wfb_details;
    } 

    /**
     * Move to the next state from an automatic state.  Automatic states can only go to one
     * proceeding state.  
     */
    function auto_next()
    {
        //Error_Handler::logError($this->wfs_id);
        if (empty($this->wfs_id)) {
            // we must have just chained to a new workflow so run the first state.
            $this->run();
        } else {
            $this->getStateDetails();
            if (!$this->wfs_details['wfs_end']) {
                // goto next state
                $this->setState($this->wfs_details['next_ids'][0]);
                $this->run();
            } else {
                $this->theend();
            }
        }
    }

    function addToStateHistory()
    {
        $history_end = Misc::array_last($this->states_done);
        if (empty($history_end) || $history_end['wfs_id'] != $this->wfs_details['wfs_id']) {
        	$this->states_done[] = array_merge($this->wfs_details, $this->wfb_details);
    	}
    }

    /**
     * Perform the action for the current state.  This will be either displaying a form or running a script.
     */
    function run()
    {
        $this->getBehaviourDetails();
        $this->getTriggerDetails();
        $this->getStateDetails();
        $this->addToStateHistory();
        $this->setSession();

        if ($this->wfb_details['wfb_auto']) {
            include(APP_PATH.'workflow/'.$this->wfb_details['wfb_script_name']);
            $this->auto_next();
        } else {
            if (!$GLOBALS['auth_isBGP']) {
                header("Location: ".APP_RELATIVE_URL.'workflow/'.$this->wfb_details['wfb_script_name']
                    ."?id=".$this->id."&wfs_id=".$this->wfs_id);
            }
            exit;
        }
    }

    /**
     * The end of the workflow has been reached.  Tidy up some variables and display a summary page.
     */
    function theend()
    {
        $this->getWorkflowDetails();
        $wfl_title = $this->wfl_details['wfl_title'];
        $wft_type = WorkflowTrigger::getTriggerName($this->wft_details['wft_type_id']);
        $dsID = $this->dsID;
        $pid = $this->pid;
        $parent_pid = $this->parent_pid;
        $parents_list = serialize($this->parents_list);
        $href= $this->href;
        $args = compact('wfl_title','wft_type','parent_pid','pid', 'dsID', 'parents_list', 'action', 'href');
        $argstrs = array();
        $outcome = $this->getvar('outcome');
        $outcome_details = $this->getvar('outcome_details');
        foreach ($args as $key => $arg) {
            $argstrs[] = "$key=".urlencode($arg);
        }
        $querystr=implode('&', $argstrs);
        if (($wft_type != 'Delete') && !empty($this->pid))  {
            History::addHistory($pid, $this->wfl_details['wfl_id'], $outcome, $outcome_details, true);
        } elseif (!empty($this->parents_list)) {
            History::addHistory($this->parents_list['rek_pid'], $this->wfl_details['wfl_id'], "", "Deleted child ".$pid, true);
        }
        $this->clearSession();
        if ($wft_type != 'Ingest') {
            header("Location: ".APP_RELATIVE_URL."workflow/end.php?".$querystr);
            exit;
        }
    }

    /**
     * Set the pid of the record created as part of this workflow
     */
    function setCreatedPid($pid)
    {
        $this->parent_pid = $this->pid;
        $this->pid = $pid;
    }

    /**
     * When the current page refreshes, the workflow state should go to a new state.
     * This is used when the workflow form has done soemthing in a popup and needs to 
     * make the workflow progress.  When the popup finishes, it refreshes the main window and 
     * the workflow state changes.
     */
    function setStateChangeOnRefresh($end=false)
    {
        $this->change_on_refresh = true;
        if ($end) {
            $this->end_on_refresh = true;
        }
        $this->setSession();
    }

    /**
     * Check if a button to move to the next state has been clicked or in the case of a refresh,
     * whether there is to be a state change on refresh.
     * This method causes the next state to run.
     */
    function checkStateChange($ispopup=false)
    {
        $button = Misc::GETorPOST_prefix('workflow_button_');
        if ($button) {
            $this->getStateDetails();
            if ($button != -1) {
                $this->setState($button);
                if (!$ispopup) {
                    $this->run();
                } else {
                    $this->setStateChangeOnRefresh();
                }
            } else {
                // have reached the end of the workflow
                if (!$ispopup) {
                    $this->theend();
                } else {
                    $this->setStateChangeOnRefresh(true);
                }
            }
        } else {
            if ($this->change_on_refresh) {
                $this->change_on_refresh = false;
                if ($this->end_on_refresh) {
                    $this->end_on_refresh = false;
                    $this->theend();
                } else {
                    $this->run();
                }
            }
        }
    }
    
    /**
     * Gets the list of next states as a list of wfs_id and label pairs.  The list is used to make 
     * buttons that will allow the user to choose the next state in the workflow
     */
    function getButtons()
    {
        $this->getStateDetails();
        $this->getWorkflowDetails();
        $next_states = Workflow_State::getDetailsNext($this->wfs_id);
        $button_list = array();
        foreach ($next_states as $next) {
            if (Workflow_State::canEnter($next['wfs_id'], $this->pid)) {
                // transparent states are hidden from the user so we make the button have the text of
                // the next non-transparent state.  Only auto states can be transparent.
                if ($next['wfs_auto'] && $next['wfs_transparent']) {
                    $next2 = $next;
                    while (!$next2['wfs_end'] && $next2['wfs_auto'] && $next2['wfs_transparent']) {
                        $next2_list = Workflow_State::getDetailsNext($next2['wfs_id']);
                        // this list should only have one item since an auto state can only have one next state
                        $next2 = $next2_list[0];
                    }
                    if ($next2['wfs_end'] && $next2['wfs_auto'] && $next2['wfs_transparent']) {
                        $title = $this->wfl_details['wfl_end_button_label'];
                    } else {
                        $title = $next2['wfs_title'];
                    }
                    // note the wfs_id is that of the transparent state - only the title is treated differently
                    $button_list[] = array(
                            'wfs_id' => $next['wfs_id'],
                            'wfs_title' => $title
                            );
                } else {
                    $button_list[] = array(
                            'wfs_id' => $next['wfs_id'],
                            'wfs_title' => $next['wfs_title']
                            );
                }
            }
        }
        if ($this->wfs_details['wfs_end']) {
            $button_list[] = array(
                    'wfs_id' => -1,
                    'wfs_title' => $this->wfl_details['wfl_end_button_label']
                    );
        }
        return $button_list;
    }

    /**
     * Get the display id for the record currently being worked on
     */
    function getXDIS_ID()
    {
        if (!$this->xdis_id) {
            $this->getRecordObject();
            $this->xdis_id = $this->rec_obj->getXmlDisplayId();
        }
        return $this->xdis_id;
    }

    /**
     * Assign a workflow variable.  This is a mechanism for saving variables in one part of a 
     * workflow to be used in another.  The variables are saved inthe workflow which persists through the session.
     */
    function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * Retrieve a variable set with the assign method.
     */
    function getvar($name)
    {
        return @$this->vars[$name];
    }

    /** 
     * Set a standard set of variables used by the workflow template.  This includes the next state buttons
     * progress lists.
     */
    function setTemplateVars(&$tpl)
    {

        $tpl->assign("id", $this->id);
        $tpl->assign('workflow_buttons', $this->getButtons());
        $this->getWorkflowDetails();
        $tpl->assign('wfl_title', $this->wfl_details['wfl_title']);
        $tpl->assign('states_done', $this->states_done);
        $this->getStateDetails();
        $tpl->assign('wfs_title', $this->wfs_details['wfs_title']);
        $tpl->assign('wfs_description', $this->wfs_details['wfs_description']);
        $tpl->assign('href', $this->href);
    }

    function setNewWorkflow($new_wft_id)
    {
        $this->wft_id= $new_wft_id;
        $this->wfs_id = null;
        $this->wfs_details = null;
        $this->wfl_details = null;
        $this->wfb_details = null;
        $this->wft_details = null;
    }
    
}

/**
 * Manages the instantiation of a worflow status from the session
 */
class WorkflowStatusStatic
{
    /**
     * Get an instance of a workflow runtime from the session.
     * @param integer $id The workflow id to be retrieved.
     * @param integer $usr_id The id of the user
     * @param integer $wfs_id The workflow state to go to (e.g. when linking link to a previous workflow step) 
     * @return object WorkflowStatus object.
     */
    function getSession($id = null, $usr_id = null, $wfs_id = null)
    {
        if (empty($id)) {
            $id = Misc::GETorPOST('id');
        }
        if (empty($usr_id)) {
            $usr_id = Auth::getUserID();
        }
        if (empty($wfs_id)) {
            $wfs_id = Misc::GETorPOST('wfs_id');
        }
        $obj = null;
        $dbtp =  APP_TABLE_PREFIX;
        $stmt = "SELECT wfses_object FROM ".$dbtp."workflow_sessions " .
                "WHERE wfses_usr_id='".$usr_id."'  AND wfses_id='".$id."' ";  
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return null;
        }
        if (empty($res)) {
            return null;
        }
        $obj = unserialize($res);
        if (!is_object($obj) || get_class($obj) != 'WorkflowStatus' ) {
        	Error_Handler::logError("Workflow object is corrupt. get_class: ".get_class($obj)."print_r: ".print_r($obj,true),__FILE__,__LINE__);
        	return null;
        }
        if (!$obj->change_on_refresh && !empty($wfs_id)) {
            $obj->setState($wfs_id);
        }
        return $obj;
    }
    
    function getList($usr_id = null)
    {
        $dbtp =  APP_TABLE_PREFIX;
        if (!empty($usr_id)) {
            $where_user = "wfses_usr_id='".$usr_id."'"; 
        } else {
            $where_user = '1';
        }
        $stmt = "SELECT wfses_id,wfses_date, wfses_listing FROM ".$dbtp."workflow_sessions " .
                "WHERE ".$where_user .
                " ORDER BY wfses_id ASC ";  
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        foreach ($res as $key => $item) {
            $res[$key]['wfses_date'] = Date_API::getFormattedDate($item['wfses_date']);
        }
        return $res;
    }
    
    /**
     * Removes the workflow session for current user with passed id.
     * @param integer $id - database id of workflow session
     * @param integer $usr_id - database id of user
     * @return integer 1 for success, -1 for failure. 
     */
    function remove($id = null, $usr_id = null)
    {
        $dbtp =  APP_TABLE_PREFIX;
        if (empty($id)) {
            $id = Misc::GETorPOST('id');
        }
        if (empty($usr_id)) {
            $usr_id = Auth::getUserID();
        }
        $stmt = "DELETE FROM ".$dbtp."workflow_sessions " .
                "WHERE wfses_usr_id='".$usr_id."'  AND wfses_id='".$id."' ";  
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        return 1;
        
    }

}


?>
