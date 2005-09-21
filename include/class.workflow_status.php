<?php
include_once(APP_INC_PATH.'class.wfbehaviours.php');
include_once(APP_INC_PATH.'class.workflow_state.php');
include_once(APP_INC_PATH.'class.workflow_trigger.php');

// for tracking status of objects in workflows
class WorkflowStatus {
    var $wfs_id;
    var $pid;
    var $wft_id;
    var $wfl_details;
    var $wfs_details;
    var $wft_details;
    var $wfb_details;
    
    function WorkflowStatus($pid=null, $wft_id=null)
    {
        $this->pid = $pid;
        $this->wft_id= $wft_id;
    }


    function setSession()
    {
        //put it in the DB too 

        $_SESSION['workflow'][$this->pid] = serialize($this);
    }
    function clearSession()
    {
        //clear it in the DB too 

        $_SESSION['workflow'][$this->pid] = null;
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
        if (!$wfs_details['wfs_end']) {
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
            include_once(APP_PATH.'workflow/'.$this->wfb_details['wfb_script_name']);
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
        $parent_title = '';
        if ($wft_type == 'Create') {
            $record = new RecordObject($this->created_pid);
            if ($this->pid && $this->pid != -1) {
                $precord = new RecordObject($this->pid);
                $parent_title = $precord->getTitle();
            } 
        } else {
            $record = new RecordObject($this->pid);
        }
        $record_title = $record->getTitle();
        $args = compact('wfl_title','wft_type','parent_title','record_title');
        $argstrs = array();
        foreach ($args as $key => $arg) {
            $argstrs[] = "$key=".urlencode($arg);
        }
        $querystr=implode('&', $argstrs);
        
        $this->clearSession();
        header("Location: ".APP_RELATIVE_URL."workflow/end.php?$querystr");
        exit;
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

}

class WorkflowStatusStatic
{
    function getSession($pid)
    {
        if (@$_SESSION['workflow'][$pid]) {
            return unserialize($_SESSION['workflow'][$pid]);
        } else {
            // get from DB
        }
    }
}


?>
