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
include_once(APP_INC_PATH . "class.record_view.php");

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

$left_pid = $_REQUEST['left_pid'];
if (empty($left_pid)) {
	$left_pid = $wfstatus->getvar('dup_report_left_pid');
} 
if ($wfstatus->getvar('dup_report_left_pid') != $left_pid) {
	$wfstatus->assign('dup_report_left_pid', $left_pid);
	$wfstatus->setSession(); 
}
$left_record = new RecordObject($left_pid);
$left_xdis_id = $left_record->getXmlDisplayId();

$link_self = $_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id));
$tpl->assign('link_self', $link_self);
$tpl->assign('link_to_list', APP_RELATIVE_URL . 'workflow/duplicates_report.php?' 
	. http_build_query(array('id' => $wfstatus->id)));

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
} elseif (@$_REQUEST['action'] == 'auto_merge') {
    $right_record = new RecordObject($current_dup_pid);
    $res = $duplicates_report->autoMergeRecords($left_record,$right_record);
    if ($res < 0) {
    	Session::setMessage('The records could not be automatically merged');
    } else {
    	Session::setMessage('The records were successfully automatically merged');
    }
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_REQUEST['action'] == 'link_to_prev') {
    $new_left_pid = $duplicates_report->getPrevItem($left_pid, $wfstatus->getvar('show_resolved'));
    if (is_string($new_left_pid)) {
    	$wfstatus->assign('dup_report_left_pid', $new_left_pid);
    	$wfstatus->assign('current_dup_pid', '');
    	$wfstatus->setSession();  // save the change to the workflow session
    	Auth::redirect($_SERVER['PHP_SELF'].'?'
    	    .http_build_query(array('id' => $wfstatus->id, 'left_pid' => $new_left_pid)));
	} else {
		Session::setMessage('Already at beginning of list');
	    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
	}
} elseif (@$_REQUEST['action'] == 'link_to_next') {
    $new_left_pid = $duplicates_report->getNextItem($left_pid, $wfstatus->getvar('show_resolved'));
    if (is_string($new_left_pid)) {
    	$wfstatus->assign('dup_report_left_pid', $new_left_pid);
    	$wfstatus->assign('current_dup_pid', '');
    	$wfstatus->setSession();  // save the change to the workflow session
    	Auth::redirect($_SERVER['PHP_SELF'].'?'
    	    .http_build_query(array('id' => $wfstatus->id, 'left_pid' => $new_left_pid)));
	} else {
		Session::setMessage('Already at end of list');
	    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
	}
}

$dup_list = $duplicates_report->getItemDetails($left_pid);
// make sure the current dup pid is actually in the list (it might not be if we've just viewed
// a different base pid
$dup_pids = Misc::keyArray($dup_list['listing'], 'pid');
if (!in_array($current_dup_pid,array_keys($dup_pids))) {
    $current_dup_pid = '';
}
if ($current_dup_pid == '') {
    $current_dup_pid = $dup_list['listing'][0]['pid'];
}
$current_dup_pid_details = $dup_pids[$current_dup_pid];
$wfstatus->assign('current_dup_pid', $current_dup_pid);

// prepare the links for choosing the dup pid in the html
$qparams = $_GET;
foreach ($dup_list['listing'] as $key => $dup_list_item) {
    $qparams['current_dup_pid'] = $dup_list_item['pid'];
    $qparams['action'] = 'change_dup_pid';
    $dup_list['listing'][$key]['link'] = $_SERVER['PHP_SELF'] . '?' . http_build_query($qparams);
}

$link_to_prev = $_SERVER['PHP_SELF'] . '?' 
	. http_build_query(array('id' => $wfstatus->id, 'action' => 'link_to_prev'));
$link_to_next = $_SERVER['PHP_SELF'] . '?' 
	. http_build_query(array('id' => $wfstatus->id, 'action' => 'link_to_next'));
$tpl->assign(compact('link_to_prev','link_to_next'));

$record_edit_form = new RecordEditForm();
$record_edit_form->setTemplateVars($tpl, $left_record);



$right_record = new RecordObject($current_dup_pid);
$right_xdis_id = $right_record->getXmlDisplayId();

if ($right_xdis_id == $left_xdis_id) {
	$tpl->assign('compare_and_merge_records', 1);
	$right_details = $right_record->getDetails();
	$record_edit_form->fixDetails($right_details);
} else {
	$tpl->assign('compare_unlike_records', 1);
	$record_view = new RecordView($right_record);
	$right_details = $record_view->getDetails();
	$right_xsd_display_fields = $record_view->getDisplayFields();
	$tpl->assign('right_xsd_display_fields',$right_xsd_display_fields);
	$right_xdis_title = $right_record->display->getTitle();
	$tpl->assign('right_xdis_title', $right_xdis_title);
}
$tpl->assign('left_isi_loc', $duplicates_report->getISI_LOC($left_record));
$tpl->assign('right_isi_loc', $duplicates_report->getISI_LOC($right_record));
$tpl->assign('left_rm_prn', $duplicates_report->getRM_PRN($left_record));
$tpl->assign('right_rm_prn', $duplicates_report->getRM_PRN($right_record));
$tpl->assign('left_issn', $duplicates_report->getIdentifier($left_record,'issn'));
$tpl->assign('right_issn', $duplicates_report->getRM_PRN($right_record,'issn'));
$tpl->assign('left_isbn', $duplicates_report->getIdentifier($left_record,'isbn'));
$tpl->assign('right_isbn', $duplicates_report->getRM_PRN($right_record,'isbn'));

$left_details = $record_edit_form->getRecordDetails();

$distances = $duplicates_report->generateLevenshteinScores(
									$left_details, $right_details);
$distances_colours = $duplicates_report->convertLevColours($distances);

//print_r($left_details[7989]);
//print_r($right_details[7989]);
//print_r($distances[7989]);
//print_r($distances_colours[7989]);

$tpl->assign(compact('dup_list','current_dup_pid','right_details','left_pid','current_dup_pid_details',
		'distances','distances_colours'));
$tpl->assign("hide_edit", true);

$tpl->registerNajax( NAJAX_Client::register('Author', APP_RELATIVE_URL.'ajax.php') . "\n"
		. NAJAX_Client::register('Controlled_Vocab', APP_RELATIVE_URL.'ajax.php') );

// Save the values we assigned to the workflow into the session - this is needed because we do multiple form
// submits to this page that aren't controlled by the workflow framework.
$wfstatus->setSession();

$tpl->displayTemplateRecord($pid);
?>