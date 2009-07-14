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

include_once(APP_INC_PATH.'class.background_process.php');

class BackgroundProcess_Bulk_Remove_Record_Collection extends BackgroundProcess
{
	function __construct()
	{
		parent::__construct();
		$this->include = 'class.bgp_bulk_remove_record_collection.php';
		$this->name = 'Bulk Remove Records from Collection';
	}

	function run()
	{
		$this->setState(BGP_RUNNING);
		extract(unserialize($this->inputs));

		/*
		 * Remove pid(s) to collection
		 */
		if (!empty($pids) && is_array($pids)) {

			foreach ($pids as $pid) {
				$this->setHeartbeat();
				$this->setProgress(++$this->pid_count);
				 
				$record = new RecordObject($pid);
				if ($record->canEdit()) {

					$res = $record->removeFromCollection($collection_pid);
					if( $res ) {
						$this->setStatus("Removed record '".$pid."' from collection '".$collection_pid."'");
					} else {
						$this->setStatus("ERROR Removing '".$pid."' from collection '".$collection_pid."'");
					}

				} else {
					$this->setStatus("Skipped '".$pid."'. User can't edit this record");
				}
			}

			$this->setStatus("Finished Bulk Remove from Collection");

		}
		$this->setState(BGP_FINISHED);
	}
}
