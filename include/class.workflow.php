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
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id IN ($items)";
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
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 (
                    wfl_title,
                    wfl_version,
                    wfl_description,
                    wfl_roles,
                    wfl_end_button_label
                 ) VALUES (
                    '" . Misc::escapeString($params["wfl_title"]) . "',
                    '" . Misc::escapeString($params["wfl_version"]) . "',
                    '" . Misc::escapeString($params["wfl_description"]) . "',
                    '" . Misc::escapeString($params["wfl_roles"]) . "',
                    '" . Misc::escapeString($params["wfl_end_button_label"]) . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
        	return $GLOBALS['db_api']->get_last_insert_id();
        }
    }

    /**
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($wfl_id)
    {
//		echo $HTTP_POST_VARS["xsd_source"];
        global $HTTP_POST_VARS;

		
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 SET 
                    wfl_title = '" . Misc::escapeString($HTTP_POST_VARS["wfl_title"]) . "',
                    wfl_version = '" . Misc::escapeString($HTTP_POST_VARS["wfl_version"]) . "',
                    wfl_description = '" . Misc::escapeString($HTTP_POST_VARS["wfl_description"]) . "',
                    wfl_roles = '" . Misc::escapeString($HTTP_POST_VARS["wfl_roles"]) . "',
                    wfl_end_button_label = '" . Misc::escapeString($HTTP_POST_VARS["wfl_end_button_label"]) . "'
                 WHERE wfl_id = $wfl_id";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			//
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
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id=$wfl_id";
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
        if (is_numeric(strpos($filename, "."))) { 	 
            $getString = APP_RELATIVE_URL."webservices/wfb.presmd.php?file=".urlencode($filename); 	 
            $http_req = new HTTP_Request($getString, array("http" => "1.0")); 	 
            $http_req->setMethod("GET");
            $http_req->sendRequest();
            $xml = $http_req->getResponseBody();
            if (is_numeric(strpos($filename, "/"))) {
                return APP_TEMP_DIR."presmd_".Foxml::makeNCName(substr(substr($filename, 0, strrpos($filename, ".")), strrpos($filename, "/")+1)).".xml"; 	 
            } else { 	 
                return APP_TEMP_DIR."presmd_".Foxml::makeNCName(substr($filename, 0, strrpos($filename, "."))).".xml"; 	 
            } 	 
        } else { 	 
            return false; 	 
        }         	 
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
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                        $where    
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
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id=$wfl_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
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
    function start($wft_id, $pid, $xdis_id, $href='', $dsID='')
    {
        $wfstatus = new WorkflowStatus($pid, $wft_id, $xdis_id, '', $dsID);
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
      */
    function canTrigger($wfl_id, $pid)
    {
        if (Auth::isAdministrator()) {
            return true;
        }
        $wfl = Workflow::getDetails($wfl_id);
        if (!empty($wfl['wfl_roles'])) {
            // the roles must be comma separated
            $wfl_roles = preg_split("/[\s,;]+/", $wfl['wfl_roles']);
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

    function exportWorkflows($wfl_ids)
    {
        $doc = new DOMDocument('1.0','utf-8');
        $doc->formatOutput = true;
        $doc->appendChild($doc->createElement('workflows'));
        $root = $doc->documentElement;
        $root->setAttribute('schema_version','1.0');
        WF_Behaviour::exportBehaviours($root);
        foreach ($wfl_ids as $wfl_id) {
            $workflow = Workflow::getDetails($wfl_id);
            $workflow_elem = $doc->createElement('workflow');
            $workflow_elem->setAttribute('wfl_id', $wfl_id);
            $workflow_elem->setAttribute('wfl_title', $workflow['wfl_title']);
            $workflow_elem->setAttribute('wfl_version', $workflow['wfl_version']);
            $workflow_elem->setAttribute('wfl_description', $workflow['wfl_description']);
            $workflow_elem->setAttribute('wfl_roles', $workflow['wfl_roles']);
            $workflow_elem->setAttribute('wfl_end_button_label', $workflow['wfl_end_button_label']);

            $states = Workflow_State::getList($wfl_id);
            if (!empty($states)) {
                foreach ($states as $state) {
                    Workflow_State::exportStates($state['wfs_id'], $workflow_elem);
                }
            }
            $links = WorkflowStateLink::getList($workflow['wfl_id']);
            foreach ($links as $link) {
                WorkflowStateLink::exportLinks($link['wfsl_id'], $workflow_elem);
            }
            WorkflowTrigger::exportTriggers($workflow['wfl_id'],$workflow_elem);
            $root->appendChild($workflow_elem);
        }
       return $doc->saveXML();

    }

    function exportAllWorkflows()
    {
        $workflows = Workflow::getList();
        return Workflow::exportWorkflows(array_keys(Misc::keyArray($workflows, 'wfl_id')));
    }

    /**
     * Import workflows from a XML doc that was previously exported
     */
    function importWorkflows($filename)
    {
    	$doc = DOMDocument::load($filename);
        $feedback = array();
        // get the behaviours and map the existing DB id to the ids in the xml doc
        $behaviour_ids_map = WF_Behaviour::importBehaviours($doc, $feedback);
        // Now import the workflows
        $xpath = new DOMXPath($doc);
        $xworkflows = $xpath->query('/workflows/workflow');
        foreach ($xworkflows as $xworkflow) {
            Workflow::importWorkflow($xworkflow, $behaviour_ids_map, $feedback);
        }
        return $feedback;
    }
    
    function importWorkflow($xworkflow, $behaviour_ids_map, &$feedback)
    {
        $title = Misc::escapeString(trim($xworkflow->getAttribute('wfl_title')));
        $version = Misc::escapeString(trim($xworkflow->getAttribute('wfl_version')));
    
    	// Does the workflow exist already?
        $list = Workflow::getList($where="WHERE wfl_title='$title'");
        if (!is_numeric($version)) {
            // we don't insert workflows unless they have a version number        	
            $ok_to_insert = false;
            $feedback[] = "Not importing workflow $title $version. Version number must be numeric";
        } elseif (!empty($list)) {
            $ok_to_insert = true;
            // We'll insert the workflow if it has a higher version number than the one in the DB
            foreach ($list as $item) {
                if (is_numeric($item['wfl_version'])) {
                	if (floatval($item['wfl_version']) >= floatval($version)) {
                        $ok_to_insert = false;
                        $feedback[] = "Not importing existing workflow $title $version";
                	}
                }	
            }
        } else {
            // import the workflow is it isn't already in the DB
            $ok_to_insert = true;
        }
        if ($ok_to_insert) {
        	// insert the new workflow from the XML
            $feedback[] = "Importing workflow $title $version";
            $params = array(
                'wfl_title' => $xworkflow->getAttribute('wfl_title'),
                'wfl_version' => $xworkflow->getAttribute('wfl_version'),
                'wfl_description' => $xworkflow->getAttribute('wfl_description'),
                'wfl_roles' => $xworkflow->getAttribute('wfl_roles'),
                'wfl_end_button_label' => $xworkflow->getAttribute('wfl_end_button_label')
            );
            $wfl_id = Workflow::insert($params);
            // Insert the states
            $state_ids_map = Workflow_State::importStates($xworkflow, $wfl_id, $behaviour_ids_map);
            
            // Insert the State Links
            WorkflowStateLink::importLinks($xworkflow, $wfl_id, $state_ids_map);
            
            // Insert the triggers
            WorkflowTrigger::importTriggers($xworkflow, $wfl_id);
        } 
    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Workflow Class');
}
?>
