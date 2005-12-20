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
  * WorkflowTrigger
  * Defines when workflows are triggered
  */
class WorkflowTrigger
{
    /**
     * @return array The types of triggers supported.  key is the trigger id, value is the name of the trigger.
     */
    function getTriggerTypes()
    {
        return array(
            0 => "Ingest", // for uploading the actual binaries and datastreams
            1 => "Update", // updating metadta records
            3 => "Delete", // deleting a record
            4 => "Create",  // creating a record
            5 => "Datastream"  // triggers on datastreams
            );
    }



    /**
     * Maps the trigger name back to a trigger id
     * Uses just the first letter of the trigger name supplied.
     * @param string $trigger The trigger name
     * @return integer trigger id.
     */
    function getTriggerId($trigger)
    {
        $triggers = WorkflowTrigger::getTriggerTypes();
        return array_search(ucfirst($trigger), $triggers);
    }

    /**
     * Maps a trigger id to trigger name
     * @param integer trigger id
     * @return string trigger name
     */
    function getTriggerName($trigger)
    {
        $triggers = WorkflowTrigger::getTriggerTypes();
        return $triggers[$trigger];
    }

    /**
     * Updates a trigger from the POST variables
     */
    function update()
    {
        return WorkflowTrigger::edit('update');
    }

    /**
     * Inserts a trigger from the POST variables
     */
    function insert()
    {
        return WorkflowTrigger::edit('insert');
    }

    /**
     * Removes a trigger from the POST variables
     */
    function remove()
    {
        return WorkflowTrigger::edit('delete');
    }

    /** 
     * Gets the values for updating or inserting a trigger from the POST variables
     * @return string MySQL style string to be put in the SET part of the query
     */
    function getPostSetStr()
    {
      $post_fields = array('wft_pid', 'wft_type_id', 'wft_xdis_id', 'wft_wfl_id', 'wft_mimetype', 'wft_icon');
      $set_str = 'SET ';
      foreach ($post_fields as $post_field) {
          $set_str .= " $post_field='".Misc::escapeString($_POST[$post_field])."', ";
      }
      $set_str = rtrim($set_str,', ');
      return $set_str;
     }

    /**
     * Modifies, Creates or Deletes a trigger record (depending on $action).  get's values from the
     * POST variables
     */
    function edit($action='insert')
    {
      switch($action) {
          case 'update':
              $wft_id = Misc::GETorPOST('wft_id');
              $wherestr = " WHERE wft_id=$wft_id";
              $actionstr ="UPDATE";
              $set_str = WorkflowTrigger::getPostSetStr();
              break;
          case 'insert':
              $wherestr = "";
              $actionstr ="INSERT INTO";
              $set_str = WorkflowTrigger::getPostSetStr();
              break;
          case 'delete':
              $items = @implode(", ", Misc::GETorPOST("items"));
              $wherestr = " WHERE wft_id IN ($items)";
              $actionstr ="DELETE FROM";
              $set_str = '';
              break;
      }
      $stmt = "$actionstr ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."workflow_trigger $set_str $wherestr";
      $res = $GLOBALS["db_api"]->dbh->query($stmt);
      if (PEAR::isError($res)) {
          Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
          return -1;
      }
      return 1;
    }

    /**
     * Get a list of workflow triggers
     * @param string $pid Record that triggers are associcated with
     * @param string  wherestr extra query refinement
     * @return array List of trigger items.
     */
    function getList($pid, $wherestr='')
    {
        $stmt = "SELECT * FROM ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."workflow_trigger
				LEFT JOIN ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."workflow on (wfl_id = wft_wfl_id) WHERE wft_pid='$pid'
            $wherestr ORDER BY wft_type_id, wft_xdis_id";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        return $res;
    }

    /**
     * get list of triggers for a pid of a certain trigger type
     * @param string pid Record that has the triggers
     * @param string or integer $trigger The trigger id
     * @return list of trigger items
     */
    function getListByTrigger($pid, $trigger)
    {
        if (!Misc::isInt($trigger)) {
            $trigger = WorkflowTrigger::getTriggerId($trigger);
        }
        return WorkflowTrigger::getList($pid, " AND wft_type_id=$trigger ");
    }

    /**
     * Get list of triggers for a record that have a given trgger type and xdis_id
     * @param string pid record id
     * @param string or integer Trigger id
     * @param integer $xdis_id Display id
     * @param boolean $strict If strict is true, then the default xdis_id won't be allowed in the list.  
     * This means that triggers where xdis_id = -1 (for Any) won't be allowed in the results.
     * @return List of triggers
     */
    function getListByTriggerAndXDIS_ID($pid, $trigger, $xdis_id, $strict=false)
    {
        if (!Misc::isInt($trigger)) {
            $trigger = WorkflowTrigger::getTriggerId($trigger);
        }
        if (!$strict) {
            $orstr = " OR wft_xdis_id=-1 ";
        } else {
            $orstr = "";
        }
        return WorkflowTrigger::getList($pid, " AND wft_type_id=$trigger 
                AND (wft_xdis_id=$xdis_id $orstr ) ");
    }

    /**
     * Get an ingest trigger for a datastream based on PID, XDIS_ID and mimetype
     * @param string pid record id
     * @param integer $xdis_id Display id
     * @param string mimetype The mimetype of the datastream
     * @param boolean $strict_xdis_id If strict is true, then the default xdis_id won't be allowed in the list.  
     * This means that triggers where xdis_id = -1 (for Any) won't be allowed in the results.
     * @return List of triggers
     */
    function getIngestTrigger($pid, $xdis_id, $mimetype, $strict_xdis_id=false) 
    {
        $trigger = WorkflowTrigger::getTriggerId('Ingest');
        $list = WorkflowTrigger::getList($pid, " AND wft_type_id=$trigger
                AND wft_xdis_id=$xdis_id AND wft_mimetype LIKE '%$mimetype%' ");
        if (empty($list) && !$strict_xdis_id) {
            $list = WorkflowTrigger::getList($pid, " AND wft_type_id=$trigger
                    AND wft_xdis_id=-1 AND wft_mimetype LIKE '%$mimetype%' ");
        }
        if (empty($list)) {
            $list = WorkflowTrigger::getList($pid, " AND wft_type_id=$trigger
                    AND wft_xdis_id=$xdis_id AND wft_mimetype='' ");
        }
        if (empty($list) && !$strict_xdis_id) {
            $list = WorkflowTrigger::getList($pid, " AND wft_type_id=$trigger
                    AND wft_xdis_id=-1 AND wft_mimetype='' ");
        }
        return @$list[0];
    }

    /**
     * Get the trigger details
     * @param integer $wft_id trigger id
     * @return array Associtaive array of trigger record details
     */
    function getDetails($wft_id)
    {
        $stmt = "SELECT * FROM ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."workflow_trigger 
            WHERE wft_id=$wft_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        return $res;
    }
    

}

?>
