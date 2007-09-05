<?php


include_once(APP_INC_PATH . "class.bgp_bulk_assign_record_group.php");
include_once(APP_INC_PATH . "class.history.php");
include_once(APP_INC_PATH . "class.group.php");


class Bulk_Assign_Record_Group {

    var $bgp;


    function setBGP(&$bgp) {
        $this->bgp = &$bgp;
    }

    function assignGroupPids($pids, $assign_grp_id, $regen=false) {
       $bgp = new BackgroundProcess_Bulk_Assign_Record_Group; 
       $bgp->register(serialize(compact('pids', 'assign_grp_id', 'regen')), Auth::getUserID());
    }

    function assignGroupBGP($pid, $assign_grp_id, $regen=false,$topcall=true)
    {
        $this->regen = $regen;
        $this->bgp->setHeartbeat();
        $this->bgp->setProgress(++$this->pid_count);
        $dbtp =  APP_TABLE_PREFIX;
		$record = new RecordObject($pid);		
		$record->updateFezMD_Group("grp_id", $assign_grp_id);
		History::addHistory($pid, null, "", "", true, "Assigned Record to Group ".Group::getName($assign_grp_id)." (".$assign_grp_id.")");
        $this->bgp->setStatus("Finished Bulk Assign Record to Group ".Group::getName($assign_grp_id)."(".$assign_grp_id.") for ".$record->getTitle());				
        

    }



}


?>
