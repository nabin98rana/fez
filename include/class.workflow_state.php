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

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.workflow_state_link.php");

/**
  * Manages the the states that make up a workflow.  Each state stores the action to
  * be performed while in that state and how those actions happen.
  */
class Workflow_State
{
 
    /**
     * Method used to get the details for a state
     *
     * @access  public
     * @param   integer $wfs_id The state
     * @return  array The details for the specified workflow state
     */
    function getDetails($wfs_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state 
                 WHERE
                    wfs_id=$wfs_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            $res['next_ids'] = WorkflowStateLink::getListNext($wfs_id);
            $res['prev_ids'] = WorkflowStateLink::getListPrev($wfs_id);
            return $res;
        }
    }


    /**
     * Method used to create a new workflow state.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 or -2 otherwise
     */
    function insert($params = array())
    {
    	if (empty($params)) {
            $params = &$_POST;
        }
        $wfs_auto = Misc::checkBox(@$params['wfs_auto']);
        $wfs_wfb_id = $wfs_auto ? $params['wfs_wfb_id'] : @$params['wfs_wfb_id2'];

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state
                 (
                    wfs_wfl_id,
                    wfs_title,
                    wfs_description,
                    wfs_roles,
                    wfs_auto,
                    wfs_wfb_id,
                    wfs_start,
                    wfs_end,
                    wfs_transparent
                 ) VALUES (
                    '" . $params['wfs_wfl_id'] . "',
                    '" . Misc::escapeString($params['wfs_title']) . "',
                    '" . Misc::escapeString($params['wfs_description']) . "',
                    '" . Misc::escapeString($params['wfs_roles']) . "',
                    '$wfs_auto',
                    '$wfs_wfb_id',
                    '" . Misc::checkBox(@$params['wfs_start']) . "',
                    '" . Misc::checkBox(@$params['wfs_end']) . "',
                    '" . Misc::checkBox(@$params['wfs_transparent']) . "'
                 )";		
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
        	$wfs_id = $GLOBALS['db_api']->get_last_insert_id();
            WorkflowStateLink::insertPost($wfs_id);
            return $wfs_id;
        }
    }


    /**
     * Method used to update the details of a specific workflow state.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function update()
    {
        global $HTTP_POST_VARS;
        $wfs_auto = Misc::checkBox(@$HTTP_POST_VARS['wfs_auto']);
        $wfs_wfb_id = $wfs_auto ? $HTTP_POST_VARS['wfs_wfb_id'] : $HTTP_POST_VARS['wfs_wfb_id2'];

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state
                 SET
                    wfs_title='" . Misc::escapeString($HTTP_POST_VARS['wfs_title']) . "',
                    wfs_description='" . Misc::escapeString($HTTP_POST_VARS['wfs_description']) . "',
                    wfs_roles='" . Misc::escapeString($HTTP_POST_VARS['wfs_roles']) . "',
                    wfs_auto='$wfs_auto',
                    wfs_wfb_id='$wfs_wfb_id',
                    wfs_start='".Misc::checkBox(@$HTTP_POST_VARS['wfs_start'])."',
                    wfs_end='".Misc::checkBox(@$HTTP_POST_VARS['wfs_end'])."',
                    wfs_transparent='".Misc::checkBox(@$HTTP_POST_VARS['wfs_transparent'])."'
                 WHERE
                    wfs_id=" . $HTTP_POST_VARS['id'];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return WorkflowStateLink::updatePost();
        }
    }

    /**
     * Method used to remove a given list of workflow states.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state
                 WHERE
                    wfs_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return WorkflowStateLink::removePost();
        }
    }
    
    function removeByWorkflow($wfl_ids)
    {
        if (empty($wfl_ids)) {
    	   return;
        } 
    	$items = Misc::arrayToSQL($wfl_ids);
        $stmt = "SELECT wfs_id FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state" .
                " WHERE wfs_wfl_id IN ($items)";
        $wfs_ids = $GLOBALS["db_api"]->dbh->getCol($stmt);        
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state
                 WHERE
                    wfs_wfl_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
          return WorkflowStateLink::removeAll($wfs_ids);
        }
    }

   /**
     * Method used to get the list of workflow states associated with a given
     * reminder ID.
     *
     * @access  public
     * @param   integer $wfl_id The workflow state ID
     * @param   string $andstr an optional query to refine the search
     * @return  array The list of workflow states
     */
    function getList($wfl_id, $andstr='')
    {
        if ($wfl_id) {
            $wherestr = " wfs_wfl_id=$wfl_id ";
        } else {
            $wherestr = " 1 ";			
        }

        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state AS ws
                    LEFT JOIN ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."wfbehaviour AS wb ON ws.wfs_wfb_id=wb.wfb_id
                 WHERE
                     $wherestr $andstr 
                    ";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            if (empty($res)) {
                return array();
            } else {
                if ($wfl_id) {
                    // get all the links from these to others
                    $nexts = WorkflowStateLink::getNextByWkFlow($wfl_id);
                    $prevs = WorkflowStateLink::getPrevByWkFlow($wfl_id);
                    foreach ($res as &$row) {
                        // items that come from this id
                        $row['next_ids'] = @$nexts[$row['wfs_id']];
                        // items that go to this id
                        $row['prev_ids'] = @$prevs[$row['wfs_id']];
                    }
                }
                return $res;
            }
        }
    }

    /**
     * Find the first state for a workflow
     * @param integer $wfl_id The workflow id
     * @return integer $wfs_id the id of the workflowstate
     */
    function getStartState($wfl_id)
    {
        $states = Workflow_State::getList($wfl_id, " AND wfs_start=1 ");
        return $states[0];
    }

    /**
     * Get the state details of the next workflow states
     * @param integer wfs_id The current workflow state
     * @return array List if workflow states details for the next possibel states
     */
    function getDetailsNext($wfs_id)
    {
        $ids = WorkflowStateLink::getListNext($wfs_id);
        if (!empty($ids)) {
            $ids_str = implode(',', $ids);
            $states = Workflow_State::getList(null, " AND wfs_id IN ($ids_str) ");
            return $states;
        } else {
            return array();
        }
    }

    /**
     * Used to find out if the current user can enter a state.
     * This is used to restrict the list of next states for the user.  The workflow state has a list of roles 
     * that the user must have on an object in order to be able to enter the state.  This function checks that 
     * the user has the roles listed in the workflow state on the given pid.
     * 
     * @param integer wfs_id - The id of the workflow state
     * @param integer pid - the pid of the record that the user wants to run the workflow state on
      */
    function canEnter($wfs_id, $pid)
    {
        if (Auth::isAdministrator()) {
            return true;
        }
        $wfs = Workflow_State::getDetails($wfs_id);
        if (!empty($wfs['wfs_roles'])) {
            // the roles may be space or comma separated
            $wfs_roles = preg_split("/[\s,]+/", $wfs['wfs_roles']);
            $pid_roles = Auth::getAuthorisationGroups($pid);
            foreach ($wfs_roles as $wfs_role) {
                if (in_array($wfs_role, $pid_roles)) {
                    return true;
                }
            }
        } else {
            return true;
        }
        return false;
    }


    function exportStates($wfs_id, &$workflow_elem)
    {
        $wfs_details = Workflow_State::getDetails($wfs_id);
        $state_elem = $workflow_elem->ownerDocument->createElement('WorkflowState');
        $state_elem->setAttribute('wfs_id', $wfs_details['wfs_id']);
        $state_elem->setAttribute('wfs_title', $wfs_details['wfs_title']);
        $state_elem->setAttribute('wfs_description', $wfs_details['wfs_description']);
        $state_elem->setAttribute('wfs_auto', $wfs_details['wfs_auto']);
        $state_elem->setAttribute('wfs_wfb_id', $wfs_details['wfs_wfb_id']);
        $state_elem->setAttribute('wfs_start', $wfs_details['wfs_start']);
        $state_elem->setAttribute('wfs_end', $wfs_details['wfs_end']);
        $state_elem->setAttribute('wfs_assigned_role_id', $wfs_details['wfs_assigned_role_id']);
        $state_elem->setAttribute('wfs_transparent', $wfs_details['wfs_transparent']);
        $state_elem->setAttribute('wfs_roles', $wfs_details['wfs_roles']);
        $workflow_elem->appendChild($state_elem);
    }
    /**
     * @returns array $state_ids_map
     */
    function importStates($xworkflow, $wfl_id, $behaviour_ids_map)
    {
    	$xpath = new DOMXPath($xworkflow->ownerDocument);
        $xstates = $xpath->query('WorkflowState', $xworkflow);
        $state_ids_map = array();
        foreach ($xstates as $xstate) {
        	$params = array(
                'wfs_wfl_id' => $wfl_id,
                'wfs_title' => $xstate->getAttribute('wfs_title'),
                'wfs_description' => $xstate->getAttribute('wfs_description'),
                'wfs_auto' => $xstate->getAttribute('wfs_auto'),
                'wfs_wfb_id' => $behaviour_ids_map[$xstate->getAttribute('wfs_wfb_id')],
                'wfs_wfb_id2' => $behaviour_ids_map[$xstate->getAttribute('wfs_wfb_id')],
                'wfs_start' => $xstate->getAttribute('wfs_start'),
                'wfs_end' => $xstate->getAttribute('wfs_end'),
                'wfs_assigned_role_id' => $xstate->getAttribute('wfs_assigned_role_id'),
                'wfs_transparent' => $xstate->getAttribute('wfs_transparent'),
                'wfs_roles' => $xstate->getAttribute('wfs_roles'),
            );
            $state_ids_map[$xstate->getAttribute('wfs_id')] = Workflow_State::insert($params);
        }
        return $state_ids_map;
    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Reminder_Action Class');
}
?>
