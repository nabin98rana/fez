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

include_once(APP_INC_PATH . "class.bgp_bulk_assign_record_user.php");
include_once(APP_INC_PATH . "class.history.php");


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
		$dbtp =  APP_TABLE_PREFIX;
		$record = new RecordObject($pid);
		$record->updateFezMD_User("usr_id", $assign_usr_id);
		History::addHistory($pid, null, "", "", true, "Assigned Record to User ".User::getFullName($assign_usr_id)." (".$assign_usr_id.")");
		$this->bgp->setStatus("Finished Bulk Assign Record to User for ".$record->getTitle());
	}
}
