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

$duplicates_report = new DuplicatesReport($pid);

$left_pid = $wfstatus->getvar('dup_report_left_pid');
$dup_list = $duplicates_report->getItemDetails($left_pid);
$qparams = $_GET;
foreach ($dup_list as $key => $dup_list_item) {
    $qparams['current_dup_pid'] = $dup_list_item['pid'];
    $qparams['action'] = 'change_dup_pid';
    $dup_list[$key]['link'] = $_SERVER['PHP_SELF'].'?'.http_build_query($qparams);
}

$left_record = new RecordObject($left_pid);
$record_edit_form = new RecordEditForm();
$record_edit_form->setTemplateVars($tpl, $left_record);

$current_dup_pid = $wfstatus->getvar('current_dup_pid');    
if (@$_GET['action'] == 'change_dup_pid') {
    $current_dup_pid = $_GET['current_dup_pid'];
}
if ($current_dup_pid == '') {
    $current_dup_pid = $dup_list[0]['pid'];
}
$wfstatus->assign('current_dup_pid', $current_dup_pid);
$right_record = new RecordObject($current_dup_pid);
$right_details = $right_record->getDetails();
$record_edit_form->fixDetails($right_details);

$tpl->assign(compact('dup_list','current_dup_pid','right_details','left_pid'));


$tpl->assign("hide_edit", true);
$tpl->displayTemplateRecord($pid);
 
 
?>