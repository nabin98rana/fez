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
// |          Lachlun Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

include_once("../config.inc.php");
include_once(APP_INC_PATH. 'class.bgp_bulk_copy_record_collection.php');
include_once(APP_INC_PATH. 'class.error_handler.php');

$pids           = $this->pids;  /* The Pids to copy */
$collection_pid = $this->pid;   /* The collection to copy PID's into */
$regen          = false;

if ($this->wft_details['wft_type_id'] == WorkflowTrigger::getTriggerId('Bulk Change Search')) {
    
	$options = Pager::saveSearchParams($request_params);
	echo "<pre>";
	print_r($_REQUEST);
	echo "</pre>";
	exit;
	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
    $bgp = new BackgroundProcess_Bulk_Copy_Record_Collection; 
    $bgp->register(serialize(compact('options', 'collection_pid', 'regen')), Auth::getUserID());
    
} elseif (!empty($pids) && is_array($pids)) { 
    
    $bgp = new BackgroundProcess_Bulk_Copy_Record_Collection; 
    $bgp->register(serialize(compact('pids', 'collection_pid', 'regen')), Auth::getUserID());
    
}

?>
