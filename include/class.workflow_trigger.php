<?php

/**
  * WorkflowTrigger
  * Defines when workflows are triggered
  */
class WorkflowTrigger
{
    function getTriggerTypes()
    {
        return array(
            0 => "Ingest", // for uploading the actual binaries
            1 => "Update", // updating metadta records
            3 => "Delete", // deleting a record
            4 => "Create"  // creating a record
            );
    }
    function getTriggerId($trigger)
    {
        $triggers = WorkflowTrigger::getTriggerTypes();
        return array_search(ucfirst($trigger), $triggers);
    }
    function getTriggerName($trigger)
    {
        $triggers = WorkflowTrigger::getTriggerTypes();
        return $triggers[$trigger];
    }



    function update()
    {
        return WorkflowTrigger::edit('update');
    }

    function insert()
    {
        return WorkflowTrigger::edit('insert');
    }

    function remove()
    {
        return WorkflowTrigger::edit('delete');
    }

    function getPostSetStr()
    {
      $post_fields = array('wft_pid', 'wft_type_id', 'wft_xdis_id', 'wft_wfl_id', 'wft_mimetype');
      $set_str = 'SET ';
      foreach ($post_fields as $post_field) {
          $set_str .= " $post_field='".Misc::escapeString($_POST[$post_field])."', ";
      }
      $set_str = rtrim($set_str,', ');
      return $set_str;
     }

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

    function getList($pid, $wherestr='')
    {
        $stmt = "SELECT * FROM ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."workflow_trigger WHERE wft_pid='$pid'
            $wherestr";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        return $res;
    }

    function getListByTrigger($pid, $trigger)
    {
        if (!Misc::isInt($trigger)) {
            $trigger = WorkflowTrigger::getTriggerId($trigger);
        }
        return WorkflowTrigger::getList($pid, " AND wft_type_id=$trigger ");
    }

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
