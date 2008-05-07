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

include_once(APP_INC_PATH. 'class.batchadd.php');
include_once(APP_INC_PATH. 'class.auth.php');
include_once(APP_INC_PATH . "class.bgp_batchadd_record.php");

$pid = $this->pid;
$xdis_id = $this->getXDIS_ID();
$wftpl = $this->getvar('template');
$temp_files = $this->getVar('files');
if (is_array($temp_files)) {
	$files = array();
	$username = Auth::getUsername();
	foreach ($temp_files as $t_file) {
		$t2_file = APP_SAN_IMPORT_DIR.$username."/".$t_file;
		if (is_file($t2_file)) {
			array_push($files, $t2_file);
		}
	}
	$bgp_batch = new BackgroundProcess_BatchAdd_Record;
	$inputs = compact('files', 'xdis_id', 'pid', 'wftpl');
	$inputs_str = serialize($inputs);
	$bgp_batch->register($inputs_str, Auth::getUserID());
}

?>
