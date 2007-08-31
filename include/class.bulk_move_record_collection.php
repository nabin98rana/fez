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
    
    /**
     * @param $request_params - a copy of $_REQUEST from which the search was run
     */
    function moveFromSearch($parent_pid, $request_params=array(), $regen=false)
    {
		$options = Pager::saveSearchParams($request_params);
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
	    $bgp = new BackgroundProcess_Bulk_Move_Record_Collection; 
        $bgp->register(serialize(compact('options', 'parent_pid', 'regen')), Auth::getUserID());
	}

	function getPidsFromSearchBGP($options)
	{
		$list = Record::getListing($options, array("Lister", "Viewer"), 0, 'ALL', 'searchKey0');
		$pids = array_keys(Misc::keyArray($list['list'],'pid'));
		return $pids;
	}
	

    function moveBGP($pids, $parent_pid, $regen=false,$topcall=true)
    {
        $this->regen = $regen;
		foreach ($pids as $pid) {
	        $this->bgp->setHeartbeat();
    	    $this->bgp->setProgress(++$this->pid_count);
			$record = new RecordObject($pid);
			if ($record->canEdit()) {
				$record->updateRELSEXT("rel:isMemberOf", $parent_pid);
	        	$this->bgp->setStatus("Moved '".$record->getTitle()."'");	
			} else {
				$this->bgp->setStatus("Skipped '".$record->getTitle()."'. User can't edit this record");
			}
		}
    	$this->bgp->setStatus("Finished Bulk Move to Collection for ".$record->getTitle());	
    }



}


?>
