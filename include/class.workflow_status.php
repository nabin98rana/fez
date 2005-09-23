<?php
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
    var $dsTitle;
    
    function WorkflowStatus($pid=null, $wft_id=null, $xdis_id=null, $dsTitle=null)
    {
        $this->pid = $pid;
        $this->wft_id= $wft_id;
        $this->xdis_id= $xdis_id;
        $this->dsTitle = $dsTitle;
    }


    function setSession()
    {
        //put it in the DB too 
        $this->getTriggerDetails();
        $wft_type = WorkflowTrigger::getTriggerName($this->wft_details['wft_type_id']);
        if ($wft_type == 'Ingest') {
            // we may not need to even save the ingest stuff as we are only allowing auto processes
            $_SESSION['workflow'][$this->pid]['i'] = serialize($this);
        } else {
            $_SESSION['workflow'][$this->pid]['cud'] = serialize($this);
        }
    }
    function clearSession()
    {
        //clear it in the DB too 
        $this->getTriggerDetails();
        $wft_type = WorkflowTrigger::getTriggerName($this->wft_details['wft_type_id']);
        if ($wft_type == 'Ingest') {
            $_SESSION['workflow'][$this->pid]['i'] = null;
        } else {
            $_SESSION['workflow'][$this->pid]['cud'] = null;
        }
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
            $this->getStateDetails();
            $this->wfl_details = Workflow::getDetails($this->wfs_details['wfs_wfl_id']);
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
            $this->setState($wfs_details['next_ids'][0]);
            $this->run();
        } else {
            $this->theend();
        }
    }

    function run()
    {
        $this->getBehaviourDetails();
        $this->setSession();
        if ($this->wfb_details['wfb_auto']) {
            include(APP_PATH.'workflow/'.$this->wfb_details['wfb_script_name']);
            $this->auto_next();
        } else {
            header("Location: ".APP_RELATIVE_URL.'workflow/'.$this->wfb_details['wfb_script_name']
                    ."?pid={$this->pid}&wfs_id={$this->wfs_id}");
            exit;
        }
        
    }

    function theend()
    {
        $this->getWorkflowDetails();
        $wfl_title = $this->wfl_details['wfl_title'];
        $wft_type = WorkflowTrigger::getTriggerName($this->wft_details['wft_type_id']);
        $parent_pid = null;
        if ($wft_type == 'Create') {
            $pid = $this->created_pid;
            $parent_pid = $this->pid;
        } else {
            $pid = $this->pid;
        }
        $args = compact('wfl_title','wft_type','parent_pid','pid');
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
        $this->created_pid = $pid;
    }

    function checkStateChange()
    {
        $button = Misc::GETorPOST_prefix('workflow_button_');
        if ($button) {
            $this->getStateDetails();
            if (!$this->wfs_details['wfs_end']) {
                $this->setState($button);
                $this->run();
            } else {
                $this->theend();
            }
        }
    }
    
    function getButtons()
    {
        $this->getStateDetails();
        $next_states = Workflow_State::getDetailsNext($this->wfs_id);
        $button_list = array();
        foreach ($next_states as $next) {
            $button_list[] = array(
                    'wfs_id' => $next['wfs_id'],
                    'wfs_title' => $next['wfs_title']
                    );
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
            $record = new RecordObject($this->pid);
            $this->xdis_id = $record->getXmlDisplayId();
        }
        return $this->xdis_id;
    }

}

class WorkflowStatusStatic
{
    function getSession($pid)
    {
        if (@$_SESSION['workflow'][$pid]['cud']) {
            return unserialize($_SESSION['workflow'][$pid]['cud']);
        } else {
            // get from DB
        }
    }
}


?>
