<?php
/**
 * A script to debug the Fedora datastream of a PID that having email confirmation issue
 */

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");


// Authentication check
Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

// PID we going to debug
$pid = $_REQUEST['pid'];

if (empty($pid)){
    echo 'Did you forget something? ';
    exit;
}

// Utilising Fez_Workflow_Sfa_Confirm class to produce a clean metadata that we can use on the template
// Instantiate Confirm class
$confirmation = new Fez_Workflow_Sfa_Confirm($pid);

// Get display data to be used by smarty template
$display_data = $confirmation->getDisplayData();

// Assigns the URL for viewing the thesis' record
$view_record_url = $confirmation->getViewURL();

// Assigns the record title
$record_title = $confirmation->getRecordTitle();

$usrDetails = User::getDetailsByID($confirmation->record->depositor);

$attached_files = $confirmation->getAttachedFiles();


// Display Submission confirmation
$tpl = new Template_API();
$tpl->setTemplate("workflow/sfa_student_thesis_confirm.tpl.html");
//$tpl->assign("type", 'sfa_student_thesis_confirm');
$tpl->assign('application_name', APP_NAME);
$tpl->assign('view_record_url', $view_record_url);
$tpl->assign('record_title', $record_title);
$tpl->assign('title', $record_title);
$tpl->assign('name', $usrDetails['usr_full_name']);
$tpl->assign("display_data", $display_data);
$tpl->assign("attached_files", $attached_files);

$tpl->displayTemplate();

