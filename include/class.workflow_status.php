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

// for tracking status of objects in workflows
class WorkflowStatus {
    var $wfs_id;
    var $pid;
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
    
    function WorkflowStatus($pid=null, $wft_id=null, $xdis_id=null, $dsInfo=null)
    {
        $this->id = md5(uniqid(rand(), true));
        $this->pid = $pid;
        $this->wft_id= $wft_id;
        $this->xdis_id= $xdis_id;
        $this->dsInfo = $dsInfo;

    }

    function getRecordObject()
    {
        if (!$this->rec_obj || $this->rec_obj->pid != $this->pid) {
            $this->rec_obj = new RecordObject($this->pid);
        }
        return $this->rec_obj;
    }

    function setSession()
    {
        $_SESSION['workflow'][$this->id] = serialize($this);
    }
    function clearSession()
    {
        $_SESSION['workflow'][$this->id] = serialize($this);
     }


    function clearStateDetails()
    {
        $this->wfs_details = null;
        $this->wfb_details = null;
    }

    function setState($wfs_id)
    {
        if ($this->wfs_id != $wfs_id) {
            $this->wfs_id = $wfs_id;
            $this->clearStateDetails();
        }
    }

    function getTriggerDetails()
    {
        if (!$this->wft_details) {
            $this->wft_details = WorkflowTrigger::getDetails($this->wft_id);
            $wft_type = WorkflowTrigger::getTriggerName($this->wft_details['wft_type_id']);
            $this->parent_pid = null;
            $this->parents_list = null;
            if ($wft_type == 'Create') {
                $this->parent_pid = $this->pid;
            } elseif ($wft_type != 'Ingest') {
                $this->getRecordObject();
                $this->parents_list = $this->rec_obj->getParents();
            }
        }
        return $this->wft_details;
    }

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

    function getWorkflowDetails()
    {
        if (!$this->wfl_details) {
            $this->getTriggerDetails();
            $this->wfl_details = Workflow::getDetails($this->wft_details['wft_wfl_id']);
        }
        return $this->wfl_details;
    } 

    function getBehaviourDetails()
    {
        if (!$this->wfb_details) {
            $this->getStateDetails();
            $this->wfb_details = WF_Behaviour::getDetails($this->wfs_details['wfs_wfb_id']);
        }
        return $this->wfb_details;
    } 

    function auto_next()
    {
        $this->getWorkflowDetails();
        if (!$this->wfs_details['wfs_end']) {
            $this->setState($this->wfs_details['next_ids'][0]);
            $this->run();
        } else {
            $this->theend();
        }
    }

    function run()
    {
        $this->getBehaviourDetails();
        $this->getTriggerDetails();
        $this->getStateDetails();
        $this->states_done[] = $this->wfs_details;
        $this->setSession();
        if ($this->wfb_details['wfb_auto']) {
            include(APP_PATH.'workflow/'.$this->wfb_details['wfb_script_name']);
            $this->auto_next();
        } else {
            header("Location: ".APP_RELATIVE_URL.'workflow/'.$this->wfb_details['wfb_script_name']
                    ."?id={$this->id}&wfs_id={$this->wfs_id}");
            exit;
        }
    }

    function theend()
    {
        $this->getWorkflowDetails();
        $wfl_title = $this->wfl_details['wfl_title'];
        $wft_type = WorkflowTrigger::getTriggerName($this->wft_details['wft_type_id']);
        $pid = $this->pid;
        $parent_pid = $this->parent_pid;
        $parents_list = serialize($this->parents_list);
        $href= $this->href;
        $args = compact('wfl_title','wft_type','parent_pid','pid', 'parents_list', 'action', 'href');
        $argstrs = array();
        foreach ($args as $key => $arg) {
            $argstrs[] = "$key=".urlencode($arg);
        }
        $querystr=implode('&', $argstrs);
        
        $this->clearSession();
        if ($wft_type != 'Ingest') {
            header("Location: ".APP_RELATIVE_URL."workflow/end.php?$querystr");
            exit;
        }
    }

    function setCreatedPid($pid)
    {
        $this->parent_pid = $this->pid;
        $this->pid = $pid;
    }

    function setStateChangeOnRefresh($end=false)
    {
        $this->change_on_refresh = true;
        if ($end) {
            $this->end_on_refresh = true;
        }
        $this->setSession();
    }

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
    
    function getButtons()
    {
        $this->getStateDetails();
        $next_states = Workflow_State::getDetailsNext($this->wfs_id);
        $button_list = array();
        foreach ($next_states as $next) {
            // transparent states are hidden fromt he user so we make the button have the text of
            // the next non-transparent state.  Only auto states can be transparent.
            if ($next['wfs_auto'] && $next['wfs_transparent']) {
                $next2 = $next;
                while (!$next2['wfs_end'] && $next2['wfs_auto'] && $next2['wfs_transparent']) {
                    $next2_list = Workflow_State::getDetailsNext($next2['wfs_id']);
                    // this list should only have one item since an auto state can only have one next state
                    $next2 = $next2_list[0];
                }
                if ($next2['wfs_end'] && $next2['wfs_auto'] && $next2['wfs_transparent']) {
                    $title = 'Done';
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
        if ($this->wfs_details['wfs_end']) {
            $button_list[] = array(
                    'wfs_id' => -1,
                    'wfs_title' => 'Done'
                    );
        }
        return $button_list;
    }

    function getXDIS_ID()
    {
        if (!$this->xdis_id) {
            $this->getRecordObject();
            $this->xdis_id = $this->rec_obj->getXmlDisplayId();
        }
        return $this->xdis_id;
    }

    function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }

    function getvar($name)
    {
        return $this->vars[$name];
    }

    function setTemplateVars(&$tpl)
    {
        $tpl->assign('workflow_buttons', $this->getButtons());
        $this->getWorkflowDetails();
        $tpl->assign('wfl_title', $this->wfl_details['wfl_title']);
        $tpl->assign('states_done', $this->states_done);
    }

}

class WorkflowStatusStatic
{
    function getSession($id)
    {
        $obj = null;
        if (@$_SESSION['workflow'][$id]) {
            $obj = unserialize($_SESSION['workflow'][$id]);
        }
        return $obj;
    }

}


?>
