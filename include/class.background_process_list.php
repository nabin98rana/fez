<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH . "class.date.php");

class BackgroundProcessList 
{

    var $auto_delete_names = "'Index Auth','Fulltext Index'";
    
    function getList($usr_id)
    {
        $usr_id = Misc::escapeString($usr_id);
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "SELECT bgp_id, bgp_usr_id, bgp_status_message, bgp_progress, bgp_state, bgp_heartbeat,bgp_name,bgp_started," .
                "if (bgp_heartbeat < DATE_SUB(CURDATE(),INTERVAL 1 DAY), 1, 0) as is_old
            FROM ".$dbtp."background_process
            WHERE bgp_usr_id='".$usr_id."'
            ORDER BY bgp_started";
        $res = $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
		foreach ($res as $key => $row) {			
			$tz = Date_API::getPreferredTimezone($res[$key]["bgp_usr_id"]);			
			$res[$key]["bgp_started"] = Date_API::getFormattedDate($res[$key]["bgp_started"], $tz);
			$res[$key]["bgp_heartbeat"] = Date_API::getFormattedDate($res[$key]["bgp_heartbeat"], $tz);
		}
        return $res;
        
    }
    
   function getDetails($id)
   {
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "SELECT *,if (bgp_heartbeat < DATE_SUB(CURDATE(),INTERVAL 1 DAY), 1, 0) as is_old
            FROM ".$dbtp."background_process
            WHERE bgp_id='".$id."'";
        $res = $GLOBALS['db_api']->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {		
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
        foreach ($items as $item) {
            BackgroundProcessList::deleteLog($item);
        }
        $items_str = Misc::arrayToSQL($items);

        // get the filenames and delete them
        $stmt = "SELECT bgp_filename FROM ".$dbtp."background_process WHERE bgp_id IN (".$items_str.") ";
        $res = $GLOBALS['db_api']->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
        foreach ($res as $filename) {
            if (!empty($filename)) {
                $deleteCommand = APP_DELETE_CMD." $filename";
                exec($deleteCommand);
            }
        }

        $stmt = "DELETE FROM ".$dbtp."background_process WHERE bgp_id IN (".$items_str.") ";
        $res = $GLOBALS['db_api']->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        return 1;
    }
    
    function autoDeleteOld($usr_id)
    {
        $auto_delete_names = $this->auto_delete_names;
    	$usr_id = Misc::escapeString($usr_id);
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "SELECT bgp_id FROM ".$dbtp."background_process 
                WHERE 
                    bgp_usr_id='".$usr_id."'  " .
                    "AND bgp_name IN (".$auto_delete_names.") " .
                    "AND ((bgp_state = '0' AND bgp_started < DATE_SUB(CURDATE(),INTERVAL 1 DAY) )  " .
                    "OR ((bgp_state = '2') AND (bgp_heartbeat < DATE_SUB(CURDATE(),INTERVAL 1 DAY) ) ) )";
        $res = $GLOBALS['db_api']->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        if (!empty($res)) {
            $this->delete($res);
        }
        $stmt = "SELECT bgp_id FROM ".$dbtp."background_process 
                WHERE 
                    bgp_usr_id='".$usr_id."'  " .
                    "AND bgp_name IN (".$auto_delete_names.") " .
                    "AND (bgp_state = '0' OR bgp_state = '2') " .
                    "ORDER BY bgp_started ASC";
        $res = $GLOBALS['db_api']->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        if (count($res) > 3) {
        	array_pop($res);
            array_pop($res);
            array_pop($res);
            $this->delete($res);
        }
    }

    function getLog($bgp_id)
    {
        return file_get_contents(APP_TEMP_DIR."fezbgp/fezbgp_".$bgp_id.".log");
    }

    function deleteLog($bgp_id)
    {
        unlink(APP_TEMP_DIR."fezbgp/fezbgp_".$bgp_id.".log");
    }

}

?>
