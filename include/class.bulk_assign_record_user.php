<?php


include_once(APP_INC_PATH . "class.bgp_bulk_assign_record_user.php");


class Bulk_Assign_Record_User {

    var $bgp;


    function setBGP(&$bgp) {
        $this->bgp = &$bgp;
    }

    function assignUserPids($pids, $assign_usr_ids, $regen=false) {
       $bgp = new BackgroundProcess_Bulk_Assign_Record_User; 
       $bgp->register(serialize(compact('pids', 'assign_usr_ids', 'regen')), Auth::getUserID());
    }

    function assignUserBGP($pid, $assign_usr_id, $regen=false,$topcall=true)
    {
        $this->regen = $regen;
        $this->bgp->setHeartbeat();
        $this->bgp->setProgress(++$this->pid_count);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
		$record = new RecordObject($pid);		
		$record->updateFezMD_User("usr_id", $assign_usr_id);
        $this->bgp->setStatus("Finished Bulk Assign Record to User for ".$record->getTitle());				
        

    }



}


?>
