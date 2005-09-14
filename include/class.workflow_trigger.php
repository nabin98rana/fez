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
            0 => "Ingest",
            1 => "Update", 
            3 => "Delete");
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
      $post_fields = array('wft_pid', 'wft_type_id', 'wft_xdis_id', 'wft_wfl_id');
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
      echo $stmt;
      $res = $GLOBALS["db_api"]->dbh->query($stmt);
      if (PEAR::isError($res)) {
          Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
          return -1;
      }
      return 1;
    }

    function getList($pid)
    {
        $stmt = "SELECT * FROM ".APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX."workflow_trigger WHERE wft_pid='$pid'";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        return $res;
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
