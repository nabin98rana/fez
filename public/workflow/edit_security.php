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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>,       |
// |                           |
// +----------------------------------------------------------------------+
//
//
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.group.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.fezacml.php");
include_once(APP_INC_PATH . "class.date.php");
//include_once(APP_INC_PATH . "class.doc_type_xsd.php");
//include_once(APP_INC_PATH . "class.xsd_display.php");
//include_once(APP_INC_PATH . "class.fedora_api.php");
//include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.auth_no_fedora_datastreams.php");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "edit_security");

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
	echo "This workflow has finished and cannot be resumed";
	exit;
}

$pid = $wfstatus->pid;
$dsID = $wfstatus->dsID;
$wfstatus->setTemplateVars($tpl);

$tpl->assign("submit_to_popup", true);
$wfstatus->checkStateChange();


$internal_user_list = User::getAssocList();
$internal_group_list = Group::getAssocListAll();

$tpl->assign("pid", $pid);
$tpl->assign("dsID", $dsID);

$record = new RecordObject($pid);
$record->getDisplay();

$xdis_id = $record->getXmlDisplayId();

$xdis_title = XSD_Display::getTitle($xdis_id);
$tpl->assign("xdis_title", $xdis_title);
$tpl->assign("extra_title", "Edit Security for ".$pid_title." (".$xdis_title.")");

$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Community_Admin");
if (Auth::checkAuthorisation($pid, $dsID, $acceptable_roles, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']) == true) {
    $tpl->assign("jquery", true);
    $internal_user_list = User::getAssocList();
    $internal_group_list = Group::getAssocListAll();
    $tpl->assign("internal_user_list", $internal_user_list);
    $tpl->assign("internal_group_list", $internal_group_list);
    $tpl->assign("group_types", AuthNoFedora::getAllGroupTypes());
    $tpl->assign("possible_roles", Auth::getAssocRoleIDs());
    if (empty($dsID)) {
        $tpl->assign("current_security_permissions", AuthNoFedora::getSecurityPermissionsDisplay($pid, $dsID));
        $tpl->assign("inherits_security",AuthNoFedora::isInherited($pid, $dsID));
    } else {
        $did = AuthNoFedoraDatastreams::getDid($pid, $dsID);
        $tpl->assign("current_security_permissions", AuthNoFedoraDatastreams::getSecurityPermissionsDisplay($did));
        $tpl->assign("inherits_security",AuthNoFedoraDatastreams::isInherited($did));
        $tpl->assign("watermark", AuthNoFedoraDatastreams::isWatermarked($did));
        $tpl->assign("copyright", AuthNoFedoraDatastreams::isCopyrighted($did));
        $tpl->assign("did", $did);
    }


    $tpl->assign("row", serialize($row));
    $tpl->assign("extra_title", "Edit Security for ".$pid_title);
} else {
    $tpl->assign("show_not_allowed_msg", true);
}
$tpl->displayTemplate();

?>
