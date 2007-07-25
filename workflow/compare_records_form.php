<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 11/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.record_edit_form.php");

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign('type',"compare_records_form");


Auth::checkAuthentication(APP_SESSION);

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$tpl->assign("pid", $pid);
$wfstatus->setTemplateVars($tpl);

$wfstatus->checkStateChange();

$left_pid = $wfstatus->getvar('dup_report_left_pid');
$left_record = new RecordObject($left_pid);

$link_self = $_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id));
$tpl->assign('link_self', $link_self);

if ($left_record->getLock(RecordLock::CONTEXT_WORKFLOW, $wfstatus->id) != 1) {
    // Someone else is editing this record.
    $owner_id = $left_record->getLockOwner();
    $tpl->assign('conflict', 1);
    $tpl->assign('conflict_user', User::getFullname($owner_id));
    $tpl->assign('disable_workflow', 1);
    $tpl->displayTemplate();
    exit;
}
 

$current_dup_pid = $wfstatus->getvar('current_dup_pid');    
$duplicates_report = new DuplicatesReport($pid);
$duplicates_report->setWorkflowId($wfstatus->id);

if (@$_REQUEST['action'] == 'change_dup_pid') {
    $current_dup_pid = $_REQUEST['current_dup_pid'];
} elseif (@$_POST['action'] == 'save_base_record') {
    $res = Record::update($left_pid, array("FezACML"), array(""));
    Session::setMessage($res);
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_POST['action'] == 'mark_duplicate') {
    $duplicates_report->markDuplicate($left_pid,$current_dup_pid);
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_POST['action'] == 'not_duplicate') {
    $duplicates_report->markNotDuplicate($left_pid,$current_dup_pid);
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_POST['action'] == 'set_as_base') {
    $duplicates_report->swapBase($left_pid,$current_dup_pid);
    list($left_pid,$current_dup_pid) = array($current_dup_pid,$left_pid); // swap the values
    $wfstatus->assign('dup_report_left_pid', $left_pid);
    $wfstatus->assign('current_dup_pid', $current_dup_pid);
    $wfstatus->setSession();  // save the change to the workflow session
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
}


$dup_list = $duplicates_report->getItemDetails($left_pid);
// make sure the current dup pid is actually in the list (it might not be if we've just viewed
// a different base pid
$dup_pids = Misc::keyArray($dup_list, 'pid');
if (!in_array($current_dup_pid,array_keys($dup_pids))) {
    $current_dup_pid = '';
}
if ($current_dup_pid == '') {
    $current_dup_pid = $dup_list[0]['pid'];
}
$current_dup_pid_details = $dup_pids[$current_dup_pid];
$wfstatus->assign('current_dup_pid', $current_dup_pid);

// prepare the links for choosing the dup pid in the html
$qparams = $_GET;
foreach ($dup_list as $key => $dup_list_item) {
    $qparams['current_dup_pid'] = $dup_list_item['pid'];
    $qparams['action'] = 'change_dup_pid';
    $dup_list[$key]['link'] = $_SERVER['PHP_SELF'].'?'.http_build_query($qparams);
}

$record_edit_form = new RecordEditForm();
$record_edit_form->setTemplateVars($tpl, $left_record);


$right_record = new RecordObject($current_dup_pid);
$right_details = $right_record->getDetails();
$record_edit_form->fixDetails($right_details);

$tpl->assign(compact('dup_list','current_dup_pid','right_details','left_pid','current_dup_pid_details'));
$tpl->assign("hide_edit", true);

// Save the values we assigned to the workflow into the session - this is needed because we do multiple form
// submits to this page that aren't controlled by the workflow framework.
$wfstatus->setSession();

$tpl->displayTemplateRecord($pid);
 
?>