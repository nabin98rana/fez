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

if (!(stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin'))) {
    proc_nice(10);
}

$ARGV = $_SERVER['argv'];
$base = $ARGV[2];
include_once($base.'config.inc.php');
include_once(APP_INC_PATH.'class.background_process.php');

$bgp_id = $ARGV[1];
//print_r($ARGV);

$dbtp =  APP_TABLE_PREFIX;
$stmt = "SELECT * FROM ".$dbtp."background_process WHERE bgp_id='".$bgp_id."'";
$res = $GLOBALS['db_api']->dbh->getAll($stmt,DB_FETCHMODE_ASSOC);
if (PEAR::isError($res)) {
    Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
}

include_once(APP_INC_PATH.$res[0]['bgp_include']);
$bgp = unserialize($res[0]['bgp_serialized']);

$bgp->setAuth();

$bgp->run();

if (!empty($bgp->wfses_id)) {
    $wfstatus = WorkflowStatusStatic::getSession($bgp->wfses_id);
    $wfstatus->auto_next();
}

?>
