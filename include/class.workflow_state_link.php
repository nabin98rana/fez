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


/**
 * Workflow State Link
 * Manages the links between workflow states.  A simple prev / next relationship.
 */
class WorkflowStateLink 
{

    /**
      * Insert links from a form post.  The $_POST var has the prev and next states to be inserted
      * @param integer $id current state id
      * @return integer 1 for success, -1 for error.
      */
    function insertPost($id)
    {
        $wfl_id = @$_POST['wfl_id'];
        if (empty($wfl_id)) {
        	return;
        }
        WorkflowStateLink::removeAll($id);
        $stmt = '';
        foreach ($_POST['wfsl_prev_id'] as $prev_id) {
            if ($prev_id > 0) {
                $stmt .= "($wfl_id, $prev_id, $id), "; 
            }
        }
        foreach ($_POST['wfsl_next_id'] as $next_id) {
            // check for duplicating a link-to-self 
            if (($next_id > 0) 
                    && (!($next_id == $id && in_array($id, $_POST['wfsl_prev_id'])))) {
                $stmt .= "($wfl_id, $id, $next_id), "; 
            }
        }
        $stmt = rtrim($stmt,', ');
        if (!empty($stmt)) {
            $stmt = "INSERT INTO 
                " . APP_TABLE_PREFIX . "workflow_state_link 
                (wfsl_wfl_id, wfsl_from_id, wfsl_to_id) VALUES
                ".$stmt;
            $res1 = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res1)) {
                Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            }
        }
        return 1;
    }

    /**
     * Update states links from a form post.  Same as insertPost
     * 
     * @return integer 1 for success, -1 for error.
     */
    function updatePost()
    {
        $id = $_POST['id'];
        return WorkflowStateLink::insertPost($id);
    }

    /**
     * Delete state links from a form post.  $_POST has the list of link ids to delete
     * @return 1 for success, -1 for failure.
     */ 
    function removePost()
    {
        return WorkflowStateLink::removeAll($_POST["items"]);
    }
   
    
    function insert($from, $to, $wfl_id) 
    {
    	$stmt = "INSERT INTO 
                " . APP_TABLE_PREFIX . "workflow_state_link 
                (wfsl_wfl_id, wfsl_from_id, wfsl_to_id) VALUES
                ('".$wfl_id."','".$from."','".$to."')";
        $res1 = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
        	return $GLOBALS['db_api']->get_last_insert_id();
        }
    }

    function remove($from, $to)
    {
    }

    function getDetails($wfsl_id)
    {
        $stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "workflow_state_link
            WHERE wfsl_id='".$wfsl_id."'";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt,DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        return $res;
    }

    /**
     * Remove state links.
     * @param array $items - list of workflow state ids to be deleted
     * @return 1 on success, -1 on failure.
     */
    function removeAll($items)
    {
        if (is_array($items)) {
            $items = @implode(", ", $items);
        }
        if (empty($items)) {
        	return;
        }
        $stmt = "DELETE FROM 
            " . APP_TABLE_PREFIX . "workflow_state_link 
            WHERE wfsl_from_id IN (".$items.") OR wfsl_to_id IN (".$items.")
            ";
        $res1 = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
        }
    }

    /**
     * Get the list of next states.
     * @param integer $state The id of the state
     * @return array List of next states
     */
    function getListNext($state)
    {
        $stmt = "SELECT wfsl_to_id FROM 
            " . APP_TABLE_PREFIX . "workflow_state_link 
            WHERE wfsl_from_id=".$state;
        $res1 = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        return $res1;
     }
    
    /**
     * Get the list of previous states.
     * @param integer $state The id of the state
     * @return array List of previous states
     */
    function getListPrev($state)
    {
        $stmt = "SELECT wfsl_from_id FROM 
            " . APP_TABLE_PREFIX . "workflow_state_link 
            WHERE wfsl_to_id=".$state;
        $res1 = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        return $res1;

    }
    
    /**
      * Get list of next states per state for a workflow
      * @param integer $wfl_id workflow id
      * @return Array Keys of the array are the originating states and the values are arrays of next states
      */
    function getNextByWkFlow($wfl_id)
    {
        $stmt = "SELECT * FROM 
            " . APP_TABLE_PREFIX . "workflow_state_link 
            WHERE wfsl_wfl_id=".$wfl_id;
        $res1 = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        $nexts = Misc::collate2ColArray($res1, 'wfsl_from_id','wfsl_to_id');
        return $nexts;
    }

    /**
     * Get list of prev states per state for a workflow
     * @param integer $wfl_id workflow id
     * @return Array Keys of the array are the destination states and the values are arrays of prev states
     */
    function getPrevByWkFlow($wfl_id)
    {
        $stmt = "SELECT * FROM 
            " . APP_TABLE_PREFIX . "workflow_state_link 
            WHERE wfsl_wfl_id=".$wfl_id;
        $res1 = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        $prevs = Misc::collate2ColArray($res1, 'wfsl_to_id','wfsl_from_id');
        return $prevs;
    }

    /**
     * Get a list of state links for a workflow.
     * @return Array The list of state links fo rhte workflow - each link record is a from / to pair.
     */
    function getList($wfl_id)
    {
        $stmt = "SELECT * FROM 
            " . APP_TABLE_PREFIX . "workflow_state_link 
            WHERE wfsl_wfl_id=".$wfl_id;
        $res1 = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        return $res1;
    }

    
    /**
     * Get the graphviz dot script to draw a representation of the workflow states as clickable diagram.
     * See http://www.graphviz.org/
     * @param integer $wfl_id The workflow id
     * @param string $url - the base URL used as the href for each of the states.  The URL should have a 
     * '@id@' substring which will be replaced by the workflow state id for each state.
     * @return string The dot script to draw the diagram
     */
    function getDot($wfl_id, $url)
    {
        $res1 = WorkflowStateLink::getList($wfl_id);
        // get the list of details for each node in the to and from columns 
        $states = Workflow_State::getList($wfl_id);
        $states1 = Misc::keyArray($states, 'wfs_id');
        $dot = <<<EOT
digraph States {
  graph [fontpath="/usr/share/fonts/default/Type1/"];
  rankdir=LR;
  node [color=lightblue, style=filled, fontname=n019003l, fontsize=10];
EOT;
        //}
        foreach ($states1 as $state) {
            $title = wordwrap($state['wfs_title'], '20', '\\n');
            $url1 = str_replace('@id@', $state['wfs_id'], $url);
            $subtitle_array = array();
            $style = '';
            if ($state['wfs_start']) {
                $style .= " shape=box ";
                $subtitle_array[] = "start";
            }
            if ($state['wfs_end']) {
                $style .= " style=bold ";
                $subtitle_array[] = "end";
            }
            if ($state['wfs_auto']) {
                $style .= " color=\"lightgoldenrod1\" ";
                $subtitle_array[] = "auto";
            }
            $subtitle = implode('|',$subtitle_array);
            if (!empty($subtitle)) {
                $subtitle = "($subtitle)";
            }
            $dot .= $state['wfs_id']." [label=\"".$title."\\n".$subtitle."\" URL=\"".$url1."\" ".$style." ];\n";
        }
        foreach ($res1 as $link) {
            $dot .= "\"".$link['wfsl_from_id']."\" -> "
                ."\"".$link['wfsl_to_id']."\";\n";
        }
        $dot .= "}\n";
        return $dot;
     }

    /**
      * Determines if the way the states are linked can be run as a workflow.  Some combinations are not allowed.
      * @param integer $wfl_id The workflow Id.
      * @return true if the workflow states are valid.
      */
    function checkLinks($wfl_id) 
    {
        $res1 = WorkflowStateLink::getList($wfl_id);
        // get the list of details for each node in the to and from columns
        $states = Workflow_State::getList($wfl_id);
        $states1 = Misc::keyArray($states, 'wfs_id');
        $txt = '';
        $start_state = array();
        $end_state = array();
        foreach ($states as $state) {
            if ($state['wfs_auto'] == 1 && count($state['next_ids']) > 1) {
                $txt .= "ALERT: Not allowed: ".$state['wfs_title']." is automatic and has more than 1 following states<br/>";
            }
            // if the state has not previous states or it has only one previous state that is itself
            if ($state['wfs_start']) {
                $start_state[] = $state['wfs_title'];
            }
            if ($state['wfs_end']) {
                $end_state[] = $state['wfs_title'];
            }
        }
        if (count($start_state) > 1) {
            $str = implode(', ', $start_state);
            $txt .= "ALERT: Too many start states: ".$str." <br/>";
        }
        if (count($start_state) < 1) {
            $txt .= "ALERT: Not allowed: there is no start state.  <br/>";
        }
        if (count($end_state) < 1) {
            $txt .= "ALERT: Not allowed: there is no end state.  <br/>";
        }
        return $txt;
    }
        
    function exportLinks($wfsl_id, &$workflow_elem)
    {
        $wfsl_details = WorkflowStateLink::getDetails($wfsl_id);
        $state_elem = $workflow_elem->ownerDocument->createElement('WorkflowStateLinks');
        $state_elem->setAttribute('wfsl_id', $wfsl_details['wfsl_id']);
        $state_elem->setAttribute('wfsl_from_id', $wfsl_details['wfsl_from_id']);
        $state_elem->setAttribute('wfsl_to_id', $wfsl_details['wfsl_to_id']);
        $workflow_elem->appendChild($state_elem);
    }

    function importLinks($xworkflow, $wfl_id, $state_ids_map)
    {
        $xpath = new DOMXPath($xworkflow->ownerDocument);
        $xlinks = $xpath->query('WorkflowStateLinks', $xworkflow);
        foreach ($xlinks as $xlink) {
        	WorkflowStateLink::insert(
                $state_ids_map[$xlink->getAttribute('wfsl_from_id')],
                $state_ids_map[$xlink->getAttribute('wfsl_to_id')],
                $wfl_id
            );
        }
    }



}

?>
