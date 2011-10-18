<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

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
		$list = Record::getListing($options, array(9,10), 0, 'ALL', 'searchKey0');
		$pids = array_keys(Misc::keyArray($list['list'],'rek_pid'));
		return $pids;
	}


	function moveBGP($pids, $parent_pid, $regen=false,$topcall=true)
	{
		$this->regen = $regen;
		$this->bgp->setStatus("Moving ".count($pids)." Records to ".$parent_pid);

        $record_counter = 0;
        $record_count = count($pids);

        // Get the configurations for ETA calculation
        $eta_cfg = $this->bgp->getETAConfig();

		foreach ($pids as $pid) {
            $this->bgp->setHeartbeat();
            $this->bgp->setProgress(++$this->pid_count);

            $record_counter++;

            // Get the ETA calculations
            $eta = $this->bgp->getETA($record_counter, $record_count, $eta_cfg);

            $this->bgp->setProgress($eta['progress']);
            $this->bgp->setStatus( "Moving:  '" . $pid . "' " .
                                      "(" . $record_counter . "/" . $record_count . ") <br />".
                                      "(Avg " . $eta['time_per_object'] . "s per Object. " .
                                        "Expected Finish " . $eta['expected_finish'] . ")"
                                    );

			$record = new RecordObject($pid);

			if ($record->canEdit()) {
					
				$res = $record->updateRELSEXT("rel:isMemberOf", $parent_pid);
					
				if($res == -3) {
					$this->bgp->setStatus("Skipped '".$pid."' because PID does not exist");
				} elseif($res == -2) {
					$this->bgp->setStatus("Skipped '".$pid."' because xquery did not return any results");
				} elseif($res == 1) {
					$this->bgp->setStatus("Moved '".$pid."'");
					$this->pid_count++;
				} else {
					$this->bgp->setStatus("Moving '".$pid."' failed");
				}

			} else {
				$this->bgp->setStatus("Skipped '".$pid."'. User can't edit this record");
			}

			$this->bgp->markPidAsFinished($pid);
		}

		$extra_msg = '';
		if($this->pid_count != count($pids)) {
			$skipped =  count($pids) - $this->pid_count;
			$extra_msg = ' Skipped ' . $skipped;
		}
        $this->bgp->setProgress(100);
		$this->bgp->setStatus("Finished Bulk Move to Collection.".$extra_msg);
	}

	function splitCollection($collection_pid, $chunk_size)
	{
		$col_record = new RecordGeneral($collection_pid);
		$pids = $col_record->getChildrenPids();
		$col_title = $col_record->getTitle();
		$remaining_pids = $pids;

		$sek_id = Search_Key::getID('Title');
		$title_xsdmf_id = $col_record->display->xsd_html_match->getXSDMF_IDBySEK($sek_id);

		for ($chunk_number = 0; count($remaining_pids) > 0; $chunk_number++) {
			// create a collection to hold this chunk of records
			$dest_pid = $col_record->copyToNewPID();
			$dest_record = new RecordObject($dest_pid);
			// set the title with an index chunk number
			$dest_title = $col_title.' '. ($chunk_number + 1);
			$dest_record->setValue($title_xsdmf_id, $dest_title, 0);
			// move a chunk of records into the new collection
			$chunk = array_slice($remaining_pids, 0, $chunk_size);
			$remaining_pids = array_diff($remaining_pids, $chunk);
			$this->moveBGP($chunk, $dest_pid);
		}

	}
}
