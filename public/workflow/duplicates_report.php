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
 
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.workflow_status.php");

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign('type',"duplicates_report");

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$tpl->assign('report_pid', $pid);

$wfstatus->setTemplateVars($tpl);

if (isset($_REQUEST['pid'])) {
    $dup_report_selected_pid = $_REQUEST['pid'];
    $wfstatus->assign('dup_report_left_pid',$dup_report_selected_pid);
}

$wfstatus->checkStateChange();

// break any record locks if we were just editing a record 
$left_pid = $wfstatus->getvar('dup_report_left_pid');
if (!empty($left_pid) && RecordLock::getOwner($left_pid) == Auth::getUserID()) {
    RecordLock::releaseLock($left_pid);
}

$compare_records_url = APP_RELATIVE_URL . 'workflow/compare_records_form.php?' 
	. http_build_query(array('id' => $wfstatus->id, 'left_pid' => '__pid__' ));
$tpl->assign('compare_records_url', $compare_records_url);

if (@$_REQUEST['action'] == 'show_resolved') {
    $wfstatus->assign('show_resolved', true);
    $wfstatus->setSession();
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_REQUEST['action'] == 'hide_resolved') {
    $wfstatus->assign('show_resolved', false);
    $wfstatus->setSession();
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
}

$show_resolved = $wfstatus->getvar('show_resolved');
$tpl->assign('show_resolved', $show_resolved);

list($page, $page_size) = Pager::doPaging($tpl, 'duplicates_report_');

$duplicates_report = new DuplicatesReport($pid);
$wfl_details = $wfstatus->getWorkflowDetails();
$duplicates_report->setWorkflowId($wfl_details['wfl_id']);
$listing = $duplicates_report->getListing($page, $page_size, $show_resolved);
// correct problem of paging off the end of the list.
if ($page != 0 && $page > $listing['list_meta']['pages'] - 1) {
	$page = 0;
	$listing = $duplicates_report->getListing($page, $page_size, $show_resolved);
	Pager::setParam('duplicates_report_page',$page);
	Pager::sendCookie();
}
$tpl->assign('listing', $listing['listing']);
$tpl->assign('list_meta', $listing['list_meta']);
$tpl->assign('pages', $listing['list_meta']['pages']);
$pager_self_link = $_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id, 'duplicates_report_page' => '__pp_page__'));
$tpl->assign('pager_self_link',$pager_self_link);

$exclude[] = 'rows';
$tpl->assign('url_wo_rows', Misc::query_string_encode($_GET,$exclude));

$duplicates_report_record = new RecordObject($pid);
$tpl->assign('report_title',$duplicates_report_record->getTitle());

$tpl->displayTemplate();
 
?>