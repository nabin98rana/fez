<?php


include_once(APP_INC_PATH . "class.bgp_bulk_move_record_collection.php");


class Bulk_Move_Record_Collection {

    var $bgp;


    function setBGP(&$bgp) {
        $this->bgp = &$bgp;
    }

    function movePids($pids, $parent_pid, $regen=false) {
       $bgp = new BackgroundProcess_Bulk_Move_Record_Collection; 
       $bgp->register(serialize(compact('pids', 'parent_pid', 'regen')), Auth::getUserID());
    }

    function moveBGP($pid, $parent_pid, $regen=false,$topcall=true)
    {
        $this->regen = $regen;
        $this->bgp->setHeartbeat();
        $this->bgp->setProgress(++$this->pid_count);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
		$record = new RecordObject($pid);		
		$record->updateRELSEXT("rel:isMemberOf", $parent_pid);
        $this->bgp->setStatus("Finished Bulk Move to Collection for ".$record->getTitle());				
        

    }



}


?>
