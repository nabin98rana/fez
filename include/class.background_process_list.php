<?php

include_once(APP_INC_PATH.'class.background_process.php');

class BackgroundProcessList 
{

    function getList($usr_id)
    {
        $usr_id = Misc::escapeString($usr_id);
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "SELECT bgp_id, bgp_status_message, bgp_progress, bgp_state, bgp_heartbeat,bgp_name,bgp_started
            FROM {$dbtp}background_process
            WHERE bgp_usr_id='$usr_id'
            ORDER BY bgp_started";
        $res = $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        return $res;
    }
    
   function getDetails($id)
   {
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "SELECT *
            FROM {$dbtp}background_process
            WHERE bgp_id='$id'";
        $res = $GLOBALS['db_api']->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        return $res;
   }

    function getStates()
    {
        $bgp = new BackgroundProcess;
        return $bgp->states;
    }

    function delete($items) 
    {
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $items_str = Misc::arrayToSQL($items);
        $stmt = "DELETE FROM {$dbtp}background_process WHERE bgp_id IN ($items_str) ";
        $res = $GLOBALS['db_api']->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        foreach ($items as $item) {
            BackgroundProcessList::deleteLog($item);
        }
        return 1;
    }

    function getLog($bgp_id)
    {
        return file_get_contents(APP_TEMP_DIR."fezbgp_{$bgp_id}.log");
    }

    function deleteLog($bgp_id)
    {
        $deleteCommand = APP_DELETE_CMD." ".APP_TEMP_DIR."fezbgp_{$bgp_id}.log";
        exec($deleteCommand);
    }

   
}

?>
