<?php
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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH.'class.crossref.php');

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
$isAdministrator = Auth::isAdministrator();

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    FezLog::get()->close();
    exit;
}

if ($isAdministrator) {
    $pid = $this->pid;
    $crossref = new Crossref;
    $existingDoiIfExists = $crossref->hasDoi($pid);
    $username = Auth::getUsername();
    $doi = ($existingDoiIfExists) ? $existingDoiIfExists : $crossref->getNextDoi();
    $crossref->upload($crossref->xmlForPid($pid, $doi));
    if (!$existingDoiIfExists) {
        $crossref->saveDoi($pid, $doi, $username);
    } else {
        History::addHistory($pid, $wfl_id, "", "", false, 'Send update to crossref');
    }
    $record = new RecordObject($pid);
    $doiCurrent = $record->getFieldValueBySearchKey("DOI");
    if (empty($doiCurrent[0])) {
        $history = '- Automated DOI assigned';
        $record->addSearchKeyValueList(array("DOI"), array($doi), true, $history);
    }
    $log = FezLog::get();
    $log->err('Pid:'.$pid);
} else {
    $tpl->assign("show_not_allowed_msg", true);
}