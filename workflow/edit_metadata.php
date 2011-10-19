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
// |          Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_relationship.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.record_edit_form.php");
include_once(APP_INC_PATH . "class.uploader.php");
include_once(APP_INC_PATH . "class.internal_notes.php");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    FezLog::get()->close();
    exit;
}

//Generate a version
if(APP_FEDORA_BYPASS == 'ON')
{
    Zend_Registry::set('version', date('Y-m-d H:i:s'));    
}

// if we have uploaded files using the flash uploader, then generate $_FILES array entries for them
if (isset($_POST['uploader_files_uploaded']) && APP_FEDORA_BYPASS != 'ON')
{
	$tmpFilesArray = Uploader::generateFilesArray($wfstatus->id, $_POST['uploader_files_uploaded']);
	if (count($tmpFilesArray)) {
		$_FILES = $tmpFilesArray;
	}
}

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "edit_metadata");
$link_self = $_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id));
$tpl->assign('link_self', $link_self);

if (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['SERVER_PORT'] == 443 || strtolower(substr($_SERVER['SCRIPT_URI'], 0, 5)) == 'https') {
	$tpl->assign('http_protocol', 'https');
} else {
	$tpl->assign('http_protocol', 'http');
}

$pid = $wfstatus->pid;

// Determine if we are in a HERDC group.
$username = Auth::getUsername();
Auth::GetUsersInternalGroups(Auth::getUserID());
foreach ($_SESSION[APP_INTERNAL_GROUPS_SESSION] as $groupID) {
    $groupID = Group::getName($groupID);
    if ($groupID == 'HERDC2008' || $groupID == 'HERDC_CE') {
        $tpl->assign("in_herdc_group", "1");
    }
}

// Record the Internal Note, if we've been handed one.
if (isset($_POST['internal_notes']) && User::isUserAdministrator($username)) {
    $note = trim($_POST['internal_notes']);
    InternalNotes::recordNote($pid, $note);
}

// if the file descriptions have been changed, record this
if (isset($_POST['editedFileDescriptions']) && is_array($_POST['editedFileDescriptions'])) {
	$fileDetails = $_POST['editedFileDescriptions'];
	foreach($fileDetails as $counter => $descriptionDetails) {
		Record::updateDatastreamLabel($descriptionDetails['pid'], $descriptionDetails['filename'], $descriptionDetails['newLabel']);
	}
}

// if the file names have changed, record this
// this has to be done after the descriptions, otherwise, the datastream names will have changed and the description won't know which one to apply to
if (isset($_POST['editedFilenames']) && is_array($_POST['editedFilenames'])) {
	foreach($_POST['editedFilenames'] as $counter => $fileDetails) {
		Record::renameDatastream($fileDetails['pid'], $fileDetails['originalFilename'], $fileDetails['newFilename']);
	}
}


$wfstatus->setTemplateVars($tpl);
$wfstatus->checkStateChange();
$collection_pid=$pid;
$community_pid=$pid;
$tpl->assign("collection_pid", $pid);
$tpl->assign("community_pid", $pid);
$debug = @$_REQUEST['debug'];
if ($debug == 1) {
	$tpl->assign("debug", "1");
} else {
	$tpl->assign("debug", "0");
}

$extra_redirect = "";
if (!empty($collection_pid)) {
	$extra_redirect.="&collection_pid=".$pid;
}
if (!empty($community_pid)) {
	$extra_redirect.="&community_pid=".$pid;
}
$record = new RecordObject($pid);

if ($record->getLock(RecordLock::CONTEXT_WORKFLOW, $wfstatus->id) != 1) {
    // Someone else is editing this record.
    $owner_id = $record->getLockOwner();
    $tpl->assign('conflict', 1);
    $tpl->assign('conflict_user', User::getFullname($owner_id));
    $tpl->assign('disable_workflow', 1);
    $tpl->displayTemplate();
    exit;
}

$tpl->assign('header_include_flash_uploader_files', 1); // we want to set the header to include the files if possible

$record->getDisplay();
$xdis_id = $record->getXmlDisplayId();
$xdis_title = XSD_Display::getTitle($xdis_id);
$tpl->assign("xdis_title", $xdis_title);
$tpl->assign("extra_title", "Edit ".$xdis_title);
$tpl->assign("internal_notes", InternalNotes::readNote($pid));

$access_ok = $record->canEdit();
if ($access_ok) {

    if (!is_numeric($xdis_id)) {
        $xdis_id = @$_REQUEST["xdis_id"];	
        if (is_numeric($xdis_id)) { // must have come from select xdis so save xdis in the FezMD
            Record::updateAdminDatastream($pid, $xdis_id);
        }
    }
    
    if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
        Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=update_form&pid=".$pid.$extra_redirect, false);
    }

    $record_edit_form = new RecordEditForm();
    $record_edit_form->setTemplateVars($tpl, $record);
    $record_edit_form->setDatastreamEditingTemplateVars($tpl, $record);
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();

?>
