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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
// set sta_id=2 for published

if ($this->wft_details['wft_type_id'] == WorkflowTrigger::getTriggerId('Bulk Change Search')) {
	$options = Pager::saveSearchParams($request_params);
	$bgp = new BackgroundProcess_Publish();
	$bgp->register(serialize(compact('options')), Auth::getUserID(), $this->id);
} else {
	$this->getRecordObject();
	if ($this->rec_obj->canApprove()) {
		$sta_id = Status::getID("Published");
		$this->rec_obj->setStatusId($sta_id);
		$historyExtra = $this->getHistoryDetail();
		History::addHistory($this->rec_obj->getPid(), null, '', '', true, 'Published', $historyExtra);
	}
	// Consideration: The default functionality is to fail silently if the role can not publish. For API work we may want to return an error or clean up the session through theend here.
	$this->rec_obj->releaseLock();
}
