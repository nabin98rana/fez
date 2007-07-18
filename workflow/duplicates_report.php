<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 3/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.workflow_status.php");
 
$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign('type',"duplicates_report");

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = Auth::isAdministrator(); 
$tpl->assign("isAdministrator", $isAdministrator);

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

list($page, $page_size) = Pager::doPaging($tpl, 'duplicates_report_');

$duplicates_report = new DuplicatesReport($pid);

$listing = $duplicates_report->getListing($page, $page_size);
$tpl->assign('listing', $listing['listing']);
$tpl->assign('list_meta', $listing['list_meta']);
$tpl->assign('pages', $listing['list_meta']['pages']);
$pager_self_link = $_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id, 'duplicates_report_page' => '__pp_page__'));
$tpl->assign('pager_self_link',$pager_self_link);

$duplicates_report_record = new RecordObject($pid);
$tpl->assign('report_title',$duplicates_report_record->getTitle());

$tpl->displayTemplate();
 
?>