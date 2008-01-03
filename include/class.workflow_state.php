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

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");
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
                    " . APP_TABLE_PREFIX . "workflow_state 
                 WHERE
                    wfs_id=".$wfs_id;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
	        $auth_roles = Workflow_State::getAuthRoles($res['wfs_id']);
			$res['wfs_roles'] = $auth_roles;	    
			$res['wfs_role_titles'] = Workflow_State::getAuthRoleTitles($res['wfs_id']);
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
                    " . APP_TABLE_PREFIX . "workflow_state
                 (
                    wfs_wfl_id,
                    wfs_title,
                    wfs_description,
                    wfs_auto,
                    wfs_wfb_id,
                    wfs_start,
                    wfs_end,
                    wfs_transparent
                 ) VALUES (
                    '" . $params['wfs_wfl_id'] . "',
                    '" . Misc::escapeString($params['wfs_title']) . "',
                    '" . Misc::escapeString($params['wfs_description']) . "',
                    '".$wfs_auto."',
                    '".$wfs_wfb_id."',
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
            // add the auth role associations!
            for ($i = 0; $i < count($params["wfs_roles"]); $i++) {
                if (!is_numeric($params["wfs_roles"][$i])) {
                  $aro_id = Auth::getRoleIDByTitle(trim($params["wfs_roles"][$i]));
                } else {
                  $aro_id = $params["wfs_roles"][$i];
                }
                if (is_numeric($aro_id)) {
                  Workflow_State::associateRole($aro_id, $wfs_id);
                }
            }
            return $wfs_id;
        }
    }


    /**
     * Method used to associate a workflow state to an auth role.
     *
     * @access  public
     * @param   integer $aro_id The auth role ID
     * @param   integer $wfl_id The workflow state ID
     * @return  boolean
     */
    function associateRole($aro_id, $wfs_id)
    {
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "workflow_state_roles
                 (
                    wfsr_wfs_id,
                    wfsr_aro_id
                 ) VALUES (
                    ".$wfs_id.",
                    ".$aro_id."
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Method used to update the details of a specific workflow state.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function update($params = array())
    {
		if (empty($params)) {
            $params = &$_POST;
        }
        $wfs_auto = Misc::checkBox(@$params['wfs_auto']);
        $wfs_wfb_id = $wfs_auto ? $params['wfs_wfb_id'] : $params['wfs_wfb_id2'];
		$wfs_id = $params['id'];
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "workflow_state
                 SET
                    wfs_title='" . Misc::escapeString($params['wfs_title']) . "',
                    wfs_description='" . Misc::escapeString($params['wfs_description']) . "',
                    wfs_auto='".$wfs_auto."',
                    wfs_wfb_id='".$wfs_wfb_id."',
                    wfs_start='".Misc::checkBox(@$params['wfs_start'])."',
                    wfs_end='".Misc::checkBox(@$params['wfs_end'])."',
                    wfs_transparent='".Misc::checkBox(@$params['wfs_transparent'])."'
                 WHERE
                    wfs_id=" . $params['id'];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            WorkflowStateLink::updatePost();
	        // update the auth role associations now
            $stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "workflow_state_roles
                     WHERE
                        wfsr_wfs_id=" . $wfs_id;
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            } else {
                for ($i = 0; $i < count($params["wfs_roles"]); $i++) {
                    if (!is_numeric($params["wfs_roles"][$i])) {
                      $aro_id = Auth::getRoleIDByTitle(trim($params["wfs_roles"][$i]));
                    } else {
                      $aro_id = $params["wfs_roles"][$i];
                    }
                    if (is_numeric($aro_id)) {
                    $stmt = "INSERT INTO
                                " . APP_TABLE_PREFIX . "workflow_state_roles
                             (
                                wfsr_wfs_id,
                                wfsr_aro_id
                             ) VALUES (
                                " . $wfs_id . ",
                                " . $aro_id . "
                             )";
                    $res = $GLOBALS["db_api"]->dbh->query($stmt);
                    if (PEAR::isError($res)) {
                        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                        return -1;
                    }
                    }
                }
            }
            return 1;
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
        $items = @implode(", ", $_POST["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "workflow_state
                 WHERE
                    wfs_id IN (".$items.")";
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
        $stmt = "SELECT wfs_id FROM " . APP_TABLE_PREFIX . "workflow_state" .
                " WHERE wfs_wfl_id IN (".$items.")";
        $wfs_ids = $GLOBALS["db_api"]->dbh->getCol($stmt);        
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "workflow_state
                 WHERE
                    wfs_wfl_id IN (".$items.")";
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
            $wherestr = " wfs_wfl_id=".$wfl_id." ";
        } else {
            $wherestr = " 1 ";			
        }

        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "workflow_state AS ws
                    LEFT JOIN " . APP_TABLE_PREFIX . "wfbehaviour AS wb ON ws.wfs_wfb_id=wb.wfb_id
                 WHERE
                     ".$wherestr." ".$andstr." 
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
	
	                    $auth_roles = Workflow_State::getAuthRoles($row['wfs_id']);
						$row['wfs_roles'] = $auth_roles;	
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

	function getAuthRoles($wfs_id) {
        $stmt = "SELECT
                    wfsr_aro_id
                 FROM
                    " . APP_TABLE_PREFIX . "workflow_state_roles
                 WHERE
                    wfsr_wfs_id=".$wfs_id;
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }		
		
	}

	function getAuthRoleTitles($wfs_id) {
        $stmt = "SELECT
                    aro_role
                 FROM
                    " . APP_TABLE_PREFIX . "auth_roles
                 INNER JOIN " . APP_TABLE_PREFIX . "workflow_state_roles on wfsr_aro_id = aro_id
                 AND
                    wfsr_wfs_id=".$wfs_id;
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
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
            $states = Workflow_State::getList(null, " AND wfs_id IN (".$ids_str.") ");
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

        if (is_array($wfs['wfs_role_titles'])) {
			if (count($wfs['wfs_role_titles']) == 0) {
				return true;
			} elseif (count($wfs['wfs_role_titles']) == 1) {
				if ($wfs['wfs_role_titles'][0] == '') {
					return true;
				}
			}
            $wfs_roles = $wfs['wfs_role_titles'];
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

    /** 
     * exportStates
     * Converts a workflow state to XML and appends it to the given node.  It also keeps track of
     * the workflow behaviours referenced by the state so that the required behaviours are also exported later.
     * @param integer $wfs_id - the id of the workflow state to export
     * @param object $workflow_elem - the DOM node to attach the exported state XML to
     * @param array $wfb_ids - array of behaviour ids to collect any referenced behaviours in.
     */
    function exportStates($wfs_id, &$workflow_elem, &$wfb_ids)
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
        $state_elem->setAttribute('wfs_roles', implode(",",$wfs_details['wfs_role_titles']));
        $workflow_elem->appendChild($state_elem);
        $wfb_id = $wfs_details['wfs_wfb_id'];
        if (!in_array($wfb_id, $wfb_ids)) {
            $wfb_ids[] = $wfb_id;
        }
    }
    /**
     * @returns array $state_ids_map
     */
    function importStates($xworkflow, $wfl_id, $behaviour_ids_map, &$feedback)
    {
    	$xpath = new DOMXPath($xworkflow->ownerDocument);
        $xstates = $xpath->query('WorkflowState', $xworkflow);
        $state_ids_map = array();
        foreach ($xstates as $xstate) {
            $wfb_id = $xstate->getAttribute('wfs_wfb_id');
        	if (!isset($behaviour_ids_map[$wfb_id])) {
                $bNodes = $xpath->query("//WorkflowBehaviour[@wfb_id='".$wfb_id."']");
                if ($bNodes->length > 0) {
                    $btitle = $bNodes->item(0)->getAttribute('wfb_title');
                    $feedback[] = "This workflow requires behaviour ".$btitle;
                } else {
                    $feedback[] = "This workflow requires a behaviour that wasn't found in the XML file (".$wfb_id.")";
                }
                $wfb_remapped = $wfb_id;
        	} else {
        		$wfb_remapped = $behaviour_ids_map[$wfb_id];
        	}
            $params = array(
                'wfs_wfl_id' => $wfl_id,
                'wfs_title' => $xstate->getAttribute('wfs_title'),
                'wfs_description' => $xstate->getAttribute('wfs_description'),
                'wfs_auto' => $xstate->getAttribute('wfs_auto'),
                'wfs_wfb_id' => $wfb_remapped,
                'wfs_wfb_id2' => $wfb_remapped,
                'wfs_start' => $xstate->getAttribute('wfs_start'),
                'wfs_end' => $xstate->getAttribute('wfs_end'),
                'wfs_assigned_role_id' => $xstate->getAttribute('wfs_assigned_role_id'),
                'wfs_transparent' => $xstate->getAttribute('wfs_transparent'),
                'wfs_roles' => explode(",",$xstate->getAttribute('wfs_roles')),
            );
            $state_ids_map[$xstate->getAttribute('wfs_id')] = Workflow_State::insert($params);
        }
        return $state_ids_map;
    }
}

// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Workflow State Class');
}
?>
