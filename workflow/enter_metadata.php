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
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.workflow_status.php");


$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign('type', 'enter_metadata');

Auth::checkAuthentication(APP_SESSION);

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

$id = Misc::GETorPOST('id');
$tpl->assign("id", $id);
$wfs_id = Misc::GETorPOST('wfs_id');
$wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$tpl->assign("pid", $pid);

$wfstatus->setTemplateVars($tpl);
// get the xdis_id of what we're creating
$xdis_id = $wfstatus->getXDIS_ID();
$xdis_title = XSD_Display::getTitle($xdis_id);
if ($pid == -1 || !$pid) {
    $access_ok = $isAdministrator;
} else {
    $community_pid = $pid;
    $collection_pid = $pid;
    $record = new RecordObject($pid);
    $access_ok = $record->canCreate();
}
if ($access_ok) {
    // check for post action
    if (@$HTTP_POST_VARS["cat"] == "report") {
        $res = Record::insert();
        $wfstatus->setCreatedPid($res);
        $wfstatus->parent_pid = $wfstatus->pid;
        $wfstatus->pid = $res;
    }
    $wfstatus->checkStateChange();

    $tpl->assign("isCreator", 1);
    if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
        print_r($wfstatus);
        exit;
        Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=insert_form".$extra_redirect, false);
    }
    $tpl->assign("xdis_id", $xdis_id);
    $tpl->assign("xdis_title", $xdis_title);
    $sta_id = 1; // set to unpublished to start with
    $tpl->assign('sta_id', $sta_id); 
	$xdis_collection_list = XSD_Display::getAssocListCollectionDocTypes(); // @@@ CK - 13/1/06 added for communities to be able to select their collection child document types/xdisplays
    $xdis_list = XSD_Display::getAssocListDocTypes(); 
    $community_list = Community::getAssocList();
    $collection_list = Collection::getAssocList();
    $internal_user_list = User::getAssocList();
    $internal_group_list = Group::getAssocListAll();
	$author_list = Author::getAssocListAll();
    $jtaskData = "";
    $maxG = 0;
//    $xsd_display_fields = (XSD_HTML_Match::getListByDisplay($xdis_id));
	$xsd_display_fields = XSD_HTML_Match::getListByDisplay($xdis_id, array("FezACML"), array(""));  // XSD_DisplayObject
//	print_r($xsd_display_fields);
//    $xsd_display_fields = (XSD_HTML_Match::getListByDisplay($xdis_id,array("FezACML"), array("")));
//    $cvo_list = Controlled_Vocab::getAssocListFullDisplay(false, "", 0, 2);

    //@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups 
    // - should probably move this into a function somewhere later
    foreach ($xsd_display_fields  as $dis_key => $dis_field) {
        if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple') {
            if (!empty($dis_field["xsdmf_smarty_variable"]) && $dis_field["xsdmf_smarty_variable"] != "none") {
                eval("\$xsd_display_fields[\$dis_key]['field_options'] = " . $dis_field["xsdmf_smarty_variable"] . ";");
            }
            if (!empty($dis_field["xsdmf_dynamic_selected_option"]) 
                    && $dis_field["xsdmf_dynamic_selected_option"] != "none") {
                eval("\$xsd_display_fields[\$dis_key]['selected_option'] = " 
                        . $dis_field["xsdmf_dynamic_selected_option"] . ";");
            }
        }
        if (($dis_field["xsdmf_html_input"] == 'contvocab') 
                || ($dis_field["xsdmf_html_input"] == 'contvocab_selector')) {
            $xsd_display_fields[$dis_key]['field_options'] = $cvo_list['data'][$dis_field['xsdmf_cvo_id']];
        }
    }

    $tpl->assign("xsd_display_fields", $xsd_display_fields);
    $tpl->assign("xdis_id", $xdis_id);
    $tpl->assign("form_title", "Create New Record");
    $tpl->assign("form_submit_button", "Create Record");

    $setup = Setup::load();

}


$tpl->displayTemplate();
?>
