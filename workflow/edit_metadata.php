<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.group.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_relationship.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.select_org_structure.php");
include_once(APP_INC_PATH . "najax_objects/class.suggestor.php");
include_once(APP_INC_PATH . "class.record_edit_form.php");

NAJAX_Server::allowClasses(array('SelectOrgStructure', 'Suggestor'));
if (NAJAX_Server::runServer()) {
	exit;
}
$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "edit_metadata");
Auth::checkAuthentication(APP_SESSION, $HTTP_SERVER_VARS['PHP_SELF']."?".$HTTP_SERVER_VARS['QUERY_STRING']);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
}
$tpl->assign("isAdministrator", $isAdministrator);

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$wfstatus->setTemplateVars($tpl);
$tpl->assign("submit_to_popup", true);
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
/* $internal_user_list = User::getAssocList();
$internal_group_list = Group::getAssocListAll(); */
$extra_redirect = "";
if (!empty($collection_pid)) {
	$extra_redirect.="&collection_pid=".$pid;
}
if (!empty($community_pid)) {
	$extra_redirect.="&community_pid=".$pid;
}
$record = new RecordObject($pid);
$record->getDisplay();
$xdis_id = $record->getXmlDisplayId();
$xdis_title = XSD_Display::getTitle($xdis_id);
//$author_list = Author::getAssocListAll();
$tpl->assign("xdis_title", $xdis_title);
$tpl->assign("extra_title", "Edit ".$xdis_title);

$access_ok = $record->canEdit();
if ($access_ok) {

    if (!is_numeric($xdis_id)) {
        $xdis_id = @$HTTP_POST_VARS["xdis_id"] ? $HTTP_POST_VARS["xdis_id"] : $HTTP_GET_VARS["xdis_id"];	
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
    
    $setup = Setup::load();

    // if user is a fez user then get prefs
    if (Auth::userExists($username)) {
        $prefs = Prefs::get(Auth::getUserID());
    }
    $tpl->assign("user_prefs", $prefs);
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();

?>
