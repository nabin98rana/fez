<?php


class WorkflowStateLink 
{

    function insertPost($id)
    {
        $wfl_id = $_POST['wfl_id'];
        WorkflowStateLink::removeAll($id);
        $stmt = "INSERT INTO 
            ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."workflow_state_link 
            (wfsl_wfl_id, wfsl_from_id, wfsl_to_id) VALUES
            ";
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
        $res1 = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        return 1;
    }

    function updatePost()
    {
        $id = $_POST['id'];
        return WorkflowStateLink::insertPost($id);
    }

    function removePost()
    {
        return WorkflowStateLink::removeAll($_POST["items"]);
    }
  
    function insert($from, $to) 
    {
    }

    function remove($from, $to)
    {
    }

    function removeAll($items)
    {
        if (is_array($items)) {
            $items = @implode(", ", $items);
        }
        $stmt = "DELETE FROM 
            ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."workflow_state_link 
            WHERE wfsl_from_id IN ($items) OR wfsl_to_id IN ($items)
            ";
        $res1 = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
        }
    }

    function getListNext($state)
    {
        $stmt = "SELECT wfsl_to_id FROM 
            ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."workflow_state_link 
            WHERE wfsl_from_id=$state";
        $res1 = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        return $res1;
     }
    
    function getListPrev($state)
    {
        $stmt = "SELECT wfsl_from_id FROM 
            ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."workflow_state_link 
            WHERE wfsl_to_id=$state";
        $res1 = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        return $res1;

    }
    
    function getNextByWkFlow($wfl_id)
    {
        $stmt = "SELECT * FROM 
            ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."workflow_state_link 
            WHERE wfsl_wfl_id=$wfl_id";
        $res1 = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        $nexts = Misc::collate2ColArray($res1, 'wfsl_from_id','wfsl_to_id');
        return $nexts;
    }
    function getPrevByWkFlow($wfl_id)
    {
        $stmt = "SELECT * FROM 
            ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."workflow_state_link 
            WHERE wfsl_wfl_id=$wfl_id";
        $res1 = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        $prevs = Misc::collate2ColArray($res1, 'wfsl_to_id','wfsl_from_id');
        return $prevs;
    }

    function getList($wfl_id)
    {
        $stmt = "SELECT * FROM 
            ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."workflow_state_link 
            WHERE wfsl_wfl_id=$wfl_id";
        $res1 = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res1)) {
            Error_Handler::logError(array($res1->getMessage(), $res1->getDebugInfo()), __FILE__, __LINE__);
            $res1 = array();
        }
        return $res1;
     }

    function getDot($wfl_id, $url)
    {
        $res1 = WorkflowStateLink::getList($wfl_id);
        // get the list of details for each node in the to and from columns 
        $states = Workflow_State::getList($wfl_id);
        $states1 = Misc::keyArray($states, 'wfs_id');
        $dot = <<<EOT
digraph States {
  graph [fontpath="/usr/share/fonts/default/Type1/"];
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
            $dot .= "{$state['wfs_id']} [label=\"$title\\n$subtitle\" URL=\"$url1\" $style ];\n";
        }
        foreach ($res1 as $link) {
            $dot .= "\"{$link['wfsl_from_id']}\" -> "
                ."\"{$link['wfsl_to_id']}\";\n";
        }
        $dot .= "}\n";
        return $dot;
     }

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
                $txt .= "ALERT: Not allowed: {$state['wfs_title']} is automatic and has more than 1 following states<br/>";
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
            $txt .= "ALERT: Too many start states: $str <br/>";
        }
        if (count($start_state) < 1) {
            $txt .= "ALERT: Not allowed: there is no start state.  <br/>";
        }
        if (count($end_state) < 1) {
            $txt .= "ALERT: Not allowed: there is no end state.  <br/>";
        }
        return $txt;
    }
        



}

?>
