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
$wfl_details = $wfstatus->getWorkflowDetails();
$duplicates_report->setWorkflowId($wfl_details['wfl_id']);

if (@$_REQUEST['action'] == 'change_dup_pid') {
    $current_dup_pid = $_REQUEST['current_dup_pid'];
	$wfstatus->assign('current_dup_pid', $current_dup_pid);
    $wfstatus->setSession();  // save the change to the workflow session
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_POST['action'] == 'save_base_record') {
    $res = Record::update($left_pid, array("FezACML"), array(""));
    Session::setMessage($res);
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_POST['action'] == 'mark_duplicate') {
	$filesExistInPids = $duplicates_report->filesExistInDuplicatePid($current_dup_pid);
	if ($filesExistInPids)
		$wfstatus->assign('filesExist', true);
	else
	{
    	$duplicates_report->markDuplicate($left_pid,$current_dup_pid);
		Statistics::moveAbstractStats($current_dup_pid, $left_pid);
	}
	$wfstatus->setSession();  // save the change to the workflow session
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_POST['action'] == 'save_file_merge') {
	// save the fixed file duplicates
	$files = $_POST['dupFilenames'];
	foreach($files as $dupFilename => $action) {
		switch ($action) {
			case 'NO-STATS':
				// do nothing if user requests this
				break;
			case 'COPY-FILE':
				// copy the file datastream from the current dup pid to the base pid
				$duplicates_report->copyFileDatastream($current_dup_pid, $dupFilename, $left_pid);
				// update statistics to match
				Statistics::moveFileStats($current_dup_pid, $dupFilename, $left_pid, $dupFilename);
				break;
			default:
				// default means the user has picked a file to assign the stats to so move the stats into the chosen file
				Statistics::moveFileStats($current_dup_pid, $dupFilename, $left_pid, $action);
		}
	}
	
	$wfstatus->assign('filesExist', ''); // clear out the filesExist var, otherwise we'll never move on
	$duplicates_report->markDuplicate($left_pid,$current_dup_pid);
	$wfstatus->setSession();  // save the change to the workflow session
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
    if (PEAR::isError($res)) {
    	Session::setMessage('The records could not be automatically merged: '.$res->getMessage());
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
	} else {
		Session::setMessage('Already at beginning of list');
	}
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
} elseif (@$_REQUEST['action'] == 'link_to_next') {
    $new_left_pid = $duplicates_report->getNextItem($left_pid, $wfstatus->getvar('show_resolved'));
    if (is_string($new_left_pid)) {
    	$wfstatus->assign('dup_report_left_pid', $new_left_pid);
    	$wfstatus->assign('current_dup_pid', '');
    	$wfstatus->setSession();  // save the change to the workflow session
	} else {
		Session::setMessage('Already at end of list');
	}
    Auth::redirect($_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id)));
}

// check if we need to deal with any file duplicates
$filesExist = $wfstatus->getVar('filesExist');
if ($filesExist) {
	// if so, then we will display a different template
	$tpl->assign('type',"compare_duplicate_files");
	$baseFiles = $duplicates_report->getFilesForPid($left_pid);
	$duplicateFiles = $duplicates_report->getFilesForPid($current_dup_pid);

	$log->debug("base files: " . print_r($baseFiles, true));
	$log->debug("duplicate files: " . print_r($duplicateFiles, true));

	$stats = array();
	foreach($duplicateFiles as $filename) {
		$stats[$filename] = Statistics::getStatsByDatastream($current_dup_pid, $filename);
	}

	$fileList = array();
	foreach($stats as $dupFilename => $fileStatistics) {
		$optionsArray = array();
		$default = '';
		
		$optionsArray['General']['NO-STATS'] = "Don't copy statistics";
		foreach ($baseFiles as $baseFilename) {
			if ($fileStatistics != 0) 
			{
				$optionsArray['Files'][$baseFilename] = "Copy statistics to {$baseFilename}";
				if ($baseFilename == $dupFilename)
					$default = $baseFilename;
			}
		}
		// check if we can copy this file across
		if (!in_array($dupFilename, $baseFiles))
			$optionsArray['General']['COPY-FILE'] = 'Copy file to base pid';
		
		$selectFormName = "dupFilenames[{$dupFilename}]";
		$fileList[] = array('filename'=>$dupFilename, 'stats'=>$fileStatistics, 'options'=>$optionsArray, 'selectFormName'=>$selectFormName, 'default'=>$default);
	}

	$tpl->assign('duplicateFileList', $fileList);
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
$wfstatus->assign('current_dup_pid', $current_dup_pid);

// see if this record has been already resolved 
$right_record = new RecordObject($current_dup_pid);
$changed = $duplicates_report->checkResolvedElsewhere($left_record, $right_record);
if ($changed > 0) {
	// Need to get the details again if it has changed.
	$dup_list = $duplicates_report->getItemDetails($left_pid);
	$dup_pids = Misc::keyArray($dup_list['listing'], 'pid');
}
$current_dup_pid_details = $dup_pids[$current_dup_pid];

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
$tpl->assign('right_issn', $duplicates_report->getIdentifier($right_record,'issn'));
$tpl->assign('left_isbn', $duplicates_report->getIdentifier($left_record,'isbn'));
$tpl->assign('right_isbn', $duplicates_report->getIdentifier($right_record,'isbn'));


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
$tpl->onload('unHideCompareRows();');

// Save the values we assigned to the workflow into the session - this is needed because we do multiple form
// submits to this page that aren't controlled by the workflow framework.
$wfstatus->setSession();

$tpl->displayTemplateRecord($pid);
?>