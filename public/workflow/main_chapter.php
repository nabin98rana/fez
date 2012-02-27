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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.main_chapter.php");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    exit;
}

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "main_chapter");

$pid = $wfstatus->pid;
$wfstatus->setTemplateVars($tpl);
$wf_id = $wfstatus->id;
$wfstatus->checkStateChange();
$record = new RecordObject($pid);
$access_ok = $record->canEdit();

if ($access_ok) {
	if ($_POST['action'] == 'save') {
		$tpl->assign('action', 'save');
		$nukeResult = MainChapter::nukeExisting($pid);
		$saveResult = MainChapter::saveMainChapters($pid, $_POST['author_id']);
		if ($saveResult != -1) {
			Auth::redirect(APP_BASE_URL.'workflow/main_chapter.php?id='.$wf_id);
		} else {
            $tpl->assign("error_message", "Error on save main chapter");
		}
	}
	$listAll = MainChapter::getListAll($pid);
	$orphaned = MainChapter::getListOrphans($pid);
	
	$tpl->assign("list", $listAll);
	$tpl->assign("orphans", $orphaned);
	$list_keyed = Misc::keyArray($listAll, 'af_id');
	$tpl->assign("cycle_colours", "#" . APP_CYCLE_COLOR_TWO . ",#FFFFFF");
	$tpl->assign(compact('list','authors','author_ids','wf_id'));

} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();

?>
