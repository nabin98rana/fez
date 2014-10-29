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
include_once(APP_INC_PATH . "class.datastream.php");
include_once(APP_INC_PATH . "class.api.php");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
$isAdministrator = Auth::isAdministrator();

// Retrieve session/pid/record firstly...
$wfstatus = &WorkflowStatusStatic::getSession();
if (empty($wfstatus)) {
    if (APP_API) {
        API::reply(500, API::makeResponse(500, "This workflow cannot be resumed."), APP_API);
        exit;
    } else {
        echo "This workflow has finished and cannot be resumed";
        FezLog::get()->close();
        exit;
    }
}
$pid = $wfstatus->pid;
$record = new RecordObject($pid);
$record->getDisplay();

// API: Update $_POST as if using the browser.
if (APP_API && (HTTP_METHOD == 'POST')) {
    $xsd_df = $record->display->getMatchFieldsList(array("FezACML"), array());
    $details = $record->getDetails();
    API::populateThePOST($xsd_df, $details);
    if (isset($_POST['edit_reason'])) {
        $wfstatus->setHistoryDetail(trim(@$_POST['edit_reason']));
    }
}

//Generate a version
if(APP_FEDORA_BYPASS == 'ON')
{
    Zend_Registry::set('version', Date_API::getCurrentDateGMT());
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

if (APP_API) {
    switch (HTTP_METHOD) {

        case 'GET':
            $tpl->setTemplate("workflow/workflow.tpl.xml");
            break;

        case 'POST':
            // Results are returned via the workflow through end.php
            break;

    }
} else {
    $tpl->setTemplate("workflow/index.tpl.html");
    $tpl->assign("jqueryUI", true);
}
$tpl->assign("type", "edit_metadata");
$tpl->assign('file_options', Datastream::$file_options);

$link_self = $_SERVER['PHP_SELF'].'?'.http_build_query(array('id' => $wfstatus->id));
$tpl->assign('link_self', $link_self);

$tpl->assign("isAdmin", $isAdministrator);

if ((array_key_exists('HTTPS', $_SERVER) && strtolower($_SERVER['HTTPS'])) == 'on'
    || $_SERVER['SERVER_PORT'] == 443 || (array_key_exists('SCRIPT_URI', $_SERVER) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 5)) == 'https')) {
	$tpl->assign('http_protocol', 'https');
} else {
	$tpl->assign('http_protocol', 'http');
}

// Determine if we are allow to display a link to Fedora Object Profile View
if ($isAdministrator) {
	if (APP_FEDORA_SETUP == 'sslall' || APP_FEDORA_SETUP == 'sslapim') {
		$get_url = APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION."/get"."/".$pid;
	} else {
		$get_url = APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_LOCATION."/get"."/".$pid;
	}
	$tpl->assign("fedora_get_view", $get_url);
} else {
	$tpl->assign("fedora_get_view", 0);
}

// Determine if we are in a HERDC group.
$username = Auth::getUsername();
$isSuperAdministrator = User::isUserSuperAdministrator($username);
Auth::GetUsersInternalGroups(Auth::getUserID());
foreach ($_SESSION[APP_INTERNAL_GROUPS_SESSION] as $groupID) {
    $groupID = Group::getName($groupID);
    if ($groupID == 'HERDC2008' || $groupID == 'HERDC_CE') {
        $tpl->assign("in_herdc_group", "1");
    }
}

// Record the Internal Note, if we've been handed one.
//if (isset($_POST['internal_notes']) && User::isUserAdministrator($username)) {
if (isset($_POST['internal_notes']) && $record->canEdit()) {
    $note = trim($_POST['internal_notes']);
    InternalNotes::recordNote($pid, $note);
}

// if the file descriptions have been changed, record this
if (isset($_POST['editedFileDescriptions']) && is_array($_POST['editedFileDescriptions'])) {
	$fileDetails = $_POST['editedFileDescriptions'];
	foreach($fileDetails as $counter => $descriptionDetails) {
		Record::updateDatastreamLabel($pid, $descriptionDetails['filename'], $descriptionDetails['newLabel']);
	}
}

if (isset($_POST['fileNamesOld']) && is_array($_POST['fileNamesOld'])) {
    $fileNames = $_POST['fileNamesOld'];
    $filePermissions = $_POST['filePermissionsOld'];
    $embargoDate = $_POST['embargoDateOld'];

    foreach ($fileNames as $counter => $dsId) {
        if ( !empty($filePermissions[$counter]) || !empty($embargoDate[$counter]) ) {
            Datastream::saveDatastreamSelectedPermissions($pid, $dsId, $filePermissions[$counter], $embargoDate[$counter]);
        }
    }
}
// if the file names have changed, record this
// this has to be done after the descriptions, otherwise, the datastream names will have changed and the description won't know which one to apply to
if (isset($_POST['editedFilenames']) && is_array($_POST['editedFilenames'])) {
	foreach($_POST['editedFilenames'] as $counter => $fileDetails) {
		Record::renameDatastream($pid, $fileDetails['originalFilename'], $fileDetails['newFilename']);
        //We will check and rename the embargo file if it exists as well
        Datastream::embargoFileRename($pid, $fileDetails['originalFilename'], $fileDetails['newFilename']);
	}
}

//filenames are sequential but fileperms, embargo and description are ordered but possibly with gaps ie 0,1,3,4  (2 being a cancelled file).
//Since fileperms always has a value (Default = 0) we use it to track if files have been added and use it to find the index
if (!empty($_POST['filePermissionsNew'])) {
    $count=0;
    foreach($_POST['filePermissionsNew'] as $i => $value) {
        $xsdmf_id = XSD_HTML_Match::getXSDMFIDByTitleXDIS_ID('Description for File Upload', $_POST['xdis_id']);
        $_POST['xsd_display_fields'][$xsdmf_id][$count] = $_POST['description'][$i];
        $fileXdis_id = $_POST['uploader_files_uploaded'];
        $filename = $_FILES['xsd_display_fields']['name'][$fileXdis_id][$count];
        Datastream::saveDatastreamSelectedPermissions($pid, $filename, $_POST['filePermissionsNew'][$i], $_POST['embargo_date'][$i]);
        if ($_POST['filePermissionsNew'][$i] == 5 || !empty($_POST['embargo_date'][$i]) ) {
            Datastream::setfezACML($pid, $filename, 10);
        } else if ($_POST['filePermissionsNew'][$i] == 8) {
            Datastream::setfezACML($pid, $filename, 11);
        }
        $count++;
    }
}

$wfstatus->setTemplateVars($tpl);
$wfstatus->checkStateChange();
$collection_pid = $pid;
$community_pid = $pid;
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

if ($record->getLock(RecordLock::CONTEXT_WORKFLOW, $wfstatus->id) != 1) {
    // Someone else is editing this record.
    $owner_id = $record->getLockOwner();
    $tpl->assign('conflict', 1);
    $tpl->assign('conflict_user', User::getFullname($owner_id));
    $tpl->assign('disable_workflow', 1);
    if (APP_API_JSON) {
        $xml = $tpl->getTemplateContents();
        $xml = simplexml_load_string($xml);
        echo json_encode($xml);
    } else {
        $tpl->displayTemplate();
    }
    exit;
}

$tpl->assign('header_include_flash_uploader_files', 1); // we want to set the header to include the files if possible

$xdis_id = $record->getXmlDisplayId();
$xdis_title = XSD_Display::getTitle($xdis_id);
$tpl->assign("xdis_title", $xdis_title);
// if this is a thesis, hide the embargo date and file type picker because they will confuse students
if ($xdis_title == 'Thesis') {
  $showFileUploadExtras = 0;
} else {
  $showFileUploadExtras = 1;
}
$tpl->assign("showFileUploadExtras", $showFileUploadExtras);
$tpl->assign("extra_title", "Edit ".$xdis_title);
$tpl->assign("internal_notes", InternalNotes::readNote($pid));
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

$canApprove = $record->canApprove();
if ($canApprove === true) {
  $tpl->assign("isApprover", 1);
} else {
  $tpl->assign("isApprover", 0);
}

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
