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
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.workflow_state.php");
include_once(APP_INC_PATH . "class.workflow_status.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.foxml.php");


/**
 * Class handle the workflow definitions.  This is just the high level stuff like name of the workflow and
 *  few other settings that apply to the whol workflow
 */
class Workflow
{
    /**
     * Method used to remove a given list of custom fields.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id IN (".$items.")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
          Workflow_State::removeByWorkflow($HTTP_POST_VARS["items"]);
          WorkflowTrigger::removeByWorkflow($HTTP_POST_VARS["items"]);
		  return true;
        }
    }


    /**
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert($params = array())
    {
    	if (empty($params)) {
    		$params = &$_POST;
    	}
		
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "workflow
                 (
                    wfl_title,
                    wfl_version,
                    wfl_description,
                    wfl_end_button_label
                 ) VALUES (
                    '" . Misc::escapeString($params["wfl_title"]) . "',
                    '" . Misc::escapeString($params["wfl_version"]) . "',
                    '" . Misc::escapeString($params["wfl_description"]) . "',
                    '" . Misc::escapeString($params["wfl_end_button_label"]) . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_wfl_id = $GLOBALS["db_api"]->get_last_insert_id();
            // add the auth role associations!
            for ($i = 0; $i < count($params["wfl_roles"]); $i++) {
                if (!is_numeric($params["wfl_roles"][$i])) {
                  $aro_id = Auth::getRoleIDByTitle(trim($params["wfl_roles"][$i]));
                } else {
                  $aro_id = $params["wfl_roles"][$i];
                }
                if (is_numeric($aro_id)) {
                  Workflow::associateRole($aro_id, $new_wfl_id);
                }
            } 
			return $new_wfl_id;
        }
    }

    /**
     * Method used to associate an workflow to an auth role.
     *
     * @access  public
     * @param   integer $aro_id The auth role ID
     * @param   integer $wfl_id The workflow ID
     * @return  boolean
     */
    function associateRole($aro_id, $wfl_id)
    {
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "workflow_roles
                 (
                    wfr_wfl_id,
                    wfr_aro_id
                 ) VALUES (
                    ".$wfl_id.",
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
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($wfl_id, $params = array())
    {

		if (empty($params)) {
            $params = &$_POST;
        }
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "workflow
                 SET 
                    wfl_title = '" . Misc::escapeString($params["wfl_title"]) . "',
                    wfl_version = '" . Misc::escapeString($params["wfl_version"]) . "',
                    wfl_description = '" . Misc::escapeString($params["wfl_description"]) . "',
                    wfl_end_button_label = '" . Misc::escapeString($params["wfl_end_button_label"]) . "'
                 WHERE wfl_id = ".$wfl_id;
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
	        // update the auth role associations now
            $stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "workflow_roles
                     WHERE
                        wfr_wfl_id=" . $wfl_id;
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            } else {
                for ($i = 0; $i < count($params["wfl_roles"]); $i++) {
                    if (!is_numeric($params["wfl_roles"][$i])) {
                      $aro_id = Auth::getRoleIDByTitle(trim($params["wfl_roles"][$i]));
                    } else {
                      $aro_id = $params["wfl_roles"][$i];
                    }
                    if (is_numeric($aro_id)) {
                    $stmt = "INSERT INTO
                                " . APP_TABLE_PREFIX . "workflow_roles
                             (
                                wfr_wfl_id,
                                wfr_aro_id
                             ) VALUES (
                                " . $wfl_id . ",
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
     * Method used to get the title of a specific reminder.
     *
     * @access  public
     * @param   integer $rem_id The reminder ID
     * @return  string The title of the reminder
     */
    function getTitle($wfl_id)
    {
        $stmt = "SELECT
                    wfl_title
                 FROM
                    " . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id=".$wfl_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }

 	 
    /**
     * Extracts preservation metadata for a datastream.
     * This is hardcoded as an ingest trigger for all workflows.
     * The file is read and preservation metadata saved as a tmp file with presmd_ prefix
     *
     * @param string filename - the name of the file being ingested as a datastream
     * @return name of preservation metadata temporary file (it is not automatically ingested as a datastream).
     */
    function checkForPresMD($filename) { 	 
            $getString = APP_BASE_URL."webservices/wfb.presmd.php?file=".urlencode($filename); 	 
			$val = Misc::ProcessURL($getString);
//			print_r($val);
//            Error_Handler::logError($getString); 
            if (is_numeric(strpos($filename, "/"))) {
                $res = APP_TEMP_DIR."presmd_".Foxml::makeNCName(substr($filename, strrpos($filename, "/")+1)); 	 
            } else { 	 
                $res = APP_TEMP_DIR."presmd_".Foxml::makeNCName($filename); 	 
            }
            if (is_numeric(strpos($res, "."))) {
                $res = substr($res, 0, strrpos($res,'.')).'.xml';
            } else {
                $res .= '.xml';
            }
            return $res;
    }

    /**
     * Method used to get the list of custom fields available in the 
     * system.
     *
     * @access  public
     * @return  array The list of custom fields
     */
    function getList($where='')
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "workflow
                        ".$where.
                                " order by    wfl_title 
                    ";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
                $t = array();
                for ($i = 0; $i < count($res); $i++) {
                    // ignore workflow templates that have no states yet...
                    $states = Workflow_State::getList($res[$i]['wfl_id']);
                    $auth_roles = Workflow::getAuthRoles($res[$i]['wfl_id']);
					$res[$i]['wfl_roles'] = $auth_roles;
					$res[$i]['total_states'] = count($states);

                    if (count($states) == 0) {
	                    $t[] = $res[$i];
                        continue;
                    }
                    $res[$i]['states'] = $states;
                    $t[] = $res[$i];



                }
                return $t;
            }
        }
    }

	function getAuthRoles($wfl_id) {
        $stmt = "SELECT
                    wfr_aro_id
                 FROM
                    " . APP_TABLE_PREFIX . "workflow_roles
                 WHERE
                    wfr_wfl_id=".$wfl_id;
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }				
	}

	function getAuthRoleTitles($wfl_id) {
        $stmt = "SELECT
                    aro_role
                 FROM
                    " . APP_TABLE_PREFIX . "auth_roles
                 INNER JOIN " . APP_TABLE_PREFIX . "workflow_roles on wfr_aro_id = aro_id
                 AND
                    wfr_wfl_id=".$wfl_id;
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }				
	}

    /**
     * Method used to get the details of a specific custom field.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The custom field details
     */
    function getDetails($wfl_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id=".$wfl_id;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {		
	        $auth_roles = Workflow::getAuthRoles($res['wfl_id']);
			$res['wfl_roles'] = $auth_roles;
	        $res['wfl_role_titles'] = Workflow::getAuthRoleTitles($res['wfl_id']);
            return $res;
        }
    }

    /**
     * starts a workflow running.
     * @param string pid the PID of the record that workflow runs on.
     * @param integer wft_id the workflow trigger that kicked off this workflow
     * @param integer xdis_id the display id for the record - used for the create trigger
     * @param string href - the originating web page stored so we can return to it later.
     */
    function start($wft_id, $pid, $xdis_id, $href='', $dsID='', $pids=array())
    {
        $wfstatus = new WorkflowStatus($pid, $wft_id, $xdis_id, '', $dsID, $pids);
        $wfstatus->href=$href;
        $wfstatus->run();
    }

    /**
      * This is called for each ingest of a datastream.  The list of triggers for 
      * the pid and mimetype are searched to see if there is an ingest trigger that needs to run.
      * If a match is found, then the ingest trigger is run with the dsID as the parameter.
      *
      * @param string pid - the record id to look for a trigger match
      * @param string dsID - the datastream id (usually a filename)
      * @param string mimetype - the mimetype of the datastream
      */
    function processIngestTrigger($pid, $dsID, $mimetype)
    {
        // find first matching trigger

        $record = new RecordObject($pid);
		$record->getImageFezACML($dsID); 
        $wft_details = $record->getIngestTrigger($mimetype);

	    if (!empty($wft_details)) {
            // run it
            $dsInfo = array(
                    'ID' => $dsID,
                    'MIMETYPE' => $mimetype);
            $wfstatus = new WorkflowStatus($record->pid, $wft_details['wft_id'], $record->xdis_id, $dsInfo);
            $wfstatus->run();
        }
    }


    /**
     * Used to find out if the current user can trigger a workflow.
     * This is used to restrict the list of workflow triggers for the user to workflows that
     * they can actually run.  The workflow has a list of roles that the user must have on an object in
     * order to be able to run the workflow on it.  This function checks that the user has the roles listed in 
     * the workflow on the given pid.
     * 
     * @param integer wfl_id - The id of the workflow
     * @param integer pid - the pidof the record that the user wants to run the workflow on
     * @param array indexArray - This function can be passed an array that already has the acml's to increase speed eg for my_fez getassigned and lists and searches in general 
      */
    function canTrigger($wfl_id, $pid, $indexArray="")
    {
        if (Auth::isAdministrator()) {
            return true;
        }
        $wfl = Workflow::getDetails($wfl_id);
		$wfl_roles = $wfl['wfl_role_titles'];
        if (is_array($wfl_roles)) {
			$pid_roles = Auth::getAuthorisationGroups($pid);							
            foreach ($wfl_roles as $wfl_role) {
                if (in_array(trim($wfl_role), $pid_roles)) {
                    return true;
                }
            }
        } else {
            return true;
        }
        return false;
    }
    
    /**
     * Test if a user can run the given workflow based on whether they meet the required role for any 
     * records in the repository.  This is used when the workflow is a trigger type -1 or -2 so it can't test
     * against a specific pid.
     * @param int $wfl_id - The workflow id
     * @param int $user_id - the user id
     * @param array of string $trigger_role - the role to test for if the workflow doesn't have any role restrictions
     * @return boolean - true if the user has the rights to run this workflow on at least one record in the system
     */
    function userCanTrigger($wfl_id, $user_id, $trigger_role = array('Editor'))
    {
        if (Auth::isAdministrator()) {
            return true;
        }
        $wfl = Workflow::getDetails($wfl_id);
        // assume roles must include edit
            if (empty($wfl['wfl_role_titles'])) {
            	$wfl_roles = $trigger_role;
            } else {
                $wfl_roles = $wfl['wfl_role_titles'];
            }
            $pid_roles = Auth::getAllIndexAuthorisationGroups($user_id);
            //Error_Handler::logError(print_r($wfl_roles, true), __FILE__,__LINE__);
            foreach ($wfl_roles as $wfl_role) {
                if (in_array(trim($wfl_role), $pid_roles)) {
                    return true;
                }
            }
        
        return false;
    }

    function exportWorkflows($wfl_ids)
    {
        $doc = new DOMDocument('1.0','utf-8');
        $doc->formatOutput = true;
        $doc->appendChild($doc->createElement('workflows'));
        $root = $doc->documentElement;
        $root->setAttribute('schema_version','1.0');
        $wfb_ids = array();
        foreach ($wfl_ids as $wfl_id) {
            $workflow = Workflow::getDetails($wfl_id);
            $workflow_elem = $doc->createElement('workflow');
            $workflow_elem->setAttribute('wfl_id', $wfl_id);
            $workflow_elem->setAttribute('wfl_title', $workflow['wfl_title']);
            $workflow_elem->setAttribute('wfl_version', $workflow['wfl_version']);
            $workflow_elem->setAttribute('wfl_description', $workflow['wfl_description']);
            $workflow_elem->setAttribute('wfl_roles', implode(",",$workflow['wfl_role_titles']));
            $workflow_elem->setAttribute('wfl_end_button_label', $workflow['wfl_end_button_label']);

            $states = Workflow_State::getList($wfl_id);
            if (!empty($states)) {
                foreach ($states as $state) {
                    Workflow_State::exportStates($state['wfs_id'], $workflow_elem, $wfb_ids);
                }
            }
            $links = WorkflowStateLink::getList($workflow['wfl_id']);
            foreach ($links as $link) {
                WorkflowStateLink::exportLinks($link['wfsl_id'], $workflow_elem);
            }
            WorkflowTrigger::exportTriggers($workflow['wfl_id'],$workflow_elem);
            $root->appendChild($workflow_elem);
        }
       WF_Behaviour::exportBehaviours($root,$wfb_ids);
       return $doc->saveXML();

    }

    function exportAllWorkflows()
    {
        $workflows = Workflow::getList();
        return Workflow::exportWorkflows(array_keys(Misc::keyArray($workflows, 'wfl_id')));
    }

    function listXML($filename)
    {
        $doc = DOMDocument::load($filename);
        $xpath = new DOMXPath($doc);
        $xworkflows = $xpath->query('/workflows/workflow');
        $list = array();
        foreach ($xworkflows as $xworkflow) {
            $title = $xworkflow->getAttribute('wfl_title');
            $item = array(
                'wfl_id' => $xworkflow->getAttribute('wfl_id'),
                'wfl_title' => $title,
                'wfl_version' => $xworkflow->getAttribute('wfl_version'),
                );
            $elist = Workflow::getList($where="WHERE wfl_title='".$title."'");
            if (!empty($elist)) {
                $overwrite = true;
            } else {
                $overwrite = false;
            }
            $item['overwrite'] = $overwrite;   
            $item['overwrite_details'] = $elist[0]['wfl_title'].' '.$elist[0]['wfl_version'];   
            $list[] = $item;
        }
        return $list;
    }

    /**
     * Import workflows from a XML doc that was previously exported
     */
    function importWorkflows($filename,$wfl_ids)
    {
    	$doc = DOMDocument::load($filename);
        $feedback = array();
        $xpath = new DOMXPath($doc);
        $xworkflows = $xpath->query('/workflows/workflow');
        // Find referenced behaviours to import
        $wfb_ids = array();
        foreach ($xworkflows as $xworkflow) {
            if (!in_array($xworkflow->getAttribute('wfl_id'), $wfl_ids)) {
                continue; // only check the workflows that were selected in the form
            }
            $wfb_ids = Workflow::collectWorkflowBehaviours($xworkflow, $wfb_ids);
        }
        // get the behaviours and map the existing DB id to the ids in the xml doc
        $behaviour_ids_map = WF_Behaviour::importBehaviours($doc, $wfb_ids, $feedback);
        // Now import the workflows
        foreach ($xworkflows as $xworkflow) {
            if (!in_array($xworkflow->getAttribute('wfl_id'), $wfl_ids)) {
            	continue; // only import the workflows that were selected in the form
            }
            Workflow::importWorkflow($xworkflow, $behaviour_ids_map, $feedback);
        }
        return $feedback;
    }
    
    function importWorkflow($xworkflow, $behaviour_ids_map, &$feedback)
    {
        $title = Misc::escapeString(trim($xworkflow->getAttribute('wfl_title')));
        $version = Misc::escapeString(trim($xworkflow->getAttribute('wfl_version')));
    
    	// Does the workflow exist already?
        $list = Workflow::getList($where="WHERE wfl_title='".$title."'");
        if (!empty($list)) {
        	$overwrite = true;
            $wfl_id = $list[0]['wfl_id'];
        } else {
            $overwrite = false;
        }
        	// insert the new workflow from the XML
            $feedback[] = "Importing workflow ".$title." ".$version;
            $params = array(
                'wfl_title' => $xworkflow->getAttribute('wfl_title'),
                'wfl_version' => $xworkflow->getAttribute('wfl_version'),
                'wfl_description' => $xworkflow->getAttribute('wfl_description'),
                'wfl_roles' => explode(",",$xworkflow->getAttribute('wfl_roles')),
                'wfl_end_button_label' => $xworkflow->getAttribute('wfl_end_button_label')
            );
            if ($overwrite) {
                Workflow::update($wfl_id, $params);
                Workflow_State::removeByWorkflow(array($wfl_id));
                WorkflowTrigger::removeByWorkflow(array($wfl_id));
            } else {
                $wfl_id = Workflow::insert($params);
            }
            // Insert the states
            $state_ids_map = Workflow_State::importStates($xworkflow, $wfl_id, $behaviour_ids_map, $feedback);
            
            // Insert the State Links
            WorkflowStateLink::importLinks($xworkflow, $wfl_id, $state_ids_map);
            
            // Insert the triggers
            WorkflowTrigger::importTriggers($xworkflow, $wfl_id);
    }
    
    /**
     * collectWorkflowBehaviours
     * Looks at an XML workflow import file and collects any behaviours it references so we 
     * can import the required behaviours.
     * @param object $xworkflow - DOM node of the workflow to look for behaviours ids in the workflow states.
     * @param array $wfb_ids - Array for collecting workflow behaviour ids that we found.
     */
    function collectWorkflowBehaviours($xworkflow, &$wfb_ids)
    {
        $xpath = new DOMXPath($xworkflow->ownerDocument);
        $xstates = $xpath->query('WorkflowState', $xworkflow);
        foreach ($xstates as $xstate) {
            $wfb_id = $xstate->getAttribute('wfs_wfb_id');
            if (!in_array($wfb_id,$wfb_ids)) {
                $wfb_ids[] = $wfb_id;
            }
        }
        return $wfb_ids;
    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Workflow Class');
}
?>
