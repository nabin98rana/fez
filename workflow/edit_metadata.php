<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.new.php 1.14 03/07/11 05:04:05-00:00 jpm $
//

include_once("../config.inc.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.group.php");
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
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");


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

//$col_id = Auth::getCurrentCollection();
//$role_id = User::getRoleByUserCollection($user_id, $col_id);

//$record_id = @$HTTP_POST_VARS["record_id"] ? $HTTP_POST_VARS["record_id"] : $HTTP_GET_VARS["pid"];
$id = Misc::GETorPOST('id');
$tpl->assign("id", $id);
$wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$tpl->assign('workflow_buttons', $wfstatus->getButtons());
$wfstatus->checkStateChange();
 
$collection_pid = @$HTTP_POST_VARS["collection_pid"] ? $HTTP_POST_VARS["collection_pid"] : @$HTTP_GET_VARS["collection_pid"];	
$community_pid = @$HTTP_POST_VARS["community_pid"] ? $HTTP_POST_VARS["community_pid"] : @$HTTP_GET_VARS["community_pid"];	

$tpl->assign("collection_pid", $collection_pid);
$tpl->assign("community_pid", $community_pid);

$community_list = Community::getAssocList();
$collection_list = Collection::getAssocList();

$internal_user_list = User::getAssocList();
$internal_group_list = Group::getAssocListAll();
$extra_redirect = "";
if (!empty($collection_pid)) {
	$extra_redirect.="&collection_pid=".$collection_pid;
}
if (!empty($community_pid)) {
	$extra_redirect.="&community_pid=".$community_pid;
}

//if ($role_id == User::getRoleID('standard user') || ($role_id == User::getRoleID('administrator')) || ($role_id == User::getRoleID('manager'))) {
$tpl->assign("pid", $pid);
$record = new RecordObject($pid);
$record->getDisplay();
$xdis_id = $record->getXmlDisplayId();
$xdis_list = XSD_Display::getAssocListDocTypes(); // @@@ CK - 24/8/05 added for collections to be able to select their child document types/xdisplays

$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Community_Admin");
if (Auth::checkAuthorisation($pid, $acceptable_roles, $HTTP_SERVER_VARS['PHP_SELF']."?".$HTTP_SERVER_VARS['QUERY_STRING']) == true) {

//echo "XDIS_ID -> ".$xdis_id;
if (!is_numeric($xdis_id)) {
	$xdis_id = @$HTTP_POST_VARS["xdis_id"] ? $HTTP_POST_VARS["xdis_id"] : $HTTP_GET_VARS["xdis_id"];	
	if (is_numeric($xdis_id)) { // must have come from select xdis so save xdis in the eSpace MD
		Record::updateAdminDatastream($pid, $xdis_id);
	}
}

if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
//	echo "XDIS_ID -> ".$xdis_id;
//	echo "redirecting";
	Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=update_form&pid=".$pid.$extra_redirect, false);
}

$sta_id = $record->getPublishedStatus();
if (!$sta_id) {
    $sta_id = 1;
}
$tpl->assign('sta_id', $sta_id); 
//if (@$HTTP_POST_VARS["cat"] == "report") {
//    $res = Record::update($pid);
//}

$jtaskData = "";
$maxG = 0;
//$tpl->assign("col_id", $col_id);
//$tpl->assign("user_id", $user_id);
//$xdis_id = 5;
$cvo_list = Controlled_Vocab::getAssocListFullDisplay();
$xsd_display_fields = $record->display->getMatchFieldsList();  // XSD_DisplayObject
//print_r($xsd_display_fields);
//(XSD_HTML_Match::getListByDisplay($xdis_id));
//$prior = array(''=>array('xsdmf_'=>array('')));
//$priority = array(''=>array('xsdmf_order'=>''));
//$priority = array(''=>array('xsdmf_order'=>''));
//$priority = array(''=>array('xsdmf_order'=>''));
//$new_fields = Misc::multiSortAssocR($xsd_display_fields, $priority);
//print_r($new_fields);
//$xsd_display_fields = $new_fields;
//$xsd_auth_display_fields = (XSD_HTML_Match::getListByDisplaySpecify($xdis_id));
//print_r($xsd_display_fields);
//@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups - should probably move this into a function somewhere later
foreach ($xsd_display_fields  as $dis_key => $dis_field) {
	if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple') {
		if (!empty($dis_field["xsdmf_smarty_variable"]) && $dis_field["xsdmf_smarty_variable"] != "none") {
			eval("\$xsd_display_fields[\$dis_key]['field_options'] = " . $dis_field["xsdmf_smarty_variable"] . ";");
		}
		if (!empty($dis_field["xsdmf_dynamic_selected_option"]) && $dis_field["xsdmf_dynamic_selected_option"] != "none") {
			eval("\$xsd_display_fields[\$dis_key]['selected_option'] = " . $dis_field["xsdmf_dynamic_selected_option"] . ";");
		}


	}
	if ($dis_field["xsdmf_html_input"] == 'contvocab') {
		$xsd_display_fields[$dis_key]['field_options'] = $cvo_list['data'][$dis_field['xsdmf_cvo_id']];
	}
}

$tpl->assign("xsd_display_fields", $xsd_display_fields);

$tpl->assign("xdis_id", $xdis_id);
//$pid = 'UQL-fed:32'; // will replace with a value from Get or Post after testing

//$xdis_id = 5; //will replace with value from the eSpace mysql database after testing

$details = $record->getDetails();
$controlled_vocabs = Controlled_Vocab::getAssocListAll();
//@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups - should probably move this into a function somewhere later
foreach ($xsd_display_fields  as $dis_field) {
	if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'contvocab' || $dis_field["xsdmf_html_input"] == 'contvocab_selector') {
		if (@$details[$dis_field["xsdmf_id"]]) { // if a record detail matches a display field xsdmf entry
			if ($dis_field["xsdmf_html_input"] == 'contvocab_selector') {			
				$tempArray = $details[$dis_field["xsdmf_id"]];
				if (is_array($tempArray)) {
					$details[$dis_field["xsdmf_id"]] = array();
					foreach ($tempArray as $cv_key => $cv_value) {
						$details[$dis_field["xsdmf_id"]][$cv_value] = $controlled_vocabs[$cv_value];
					}
				} else {
//					$details[$dis_field["xsdmf_id"]] = $controlled_vocabs[$details[$dis_field["xsdmf_id"]]];
					$tempValue = $details[$dis_field["xsdmf_id"]];
					$details[$dis_field["xsdmf_id"]] = array();
					$details[$dis_field["xsdmf_id"]][$tempValue] = $controlled_vocabs[$tempValue];

				}
			} else {
				if (is_array($dis_field["field_options"])) { // if the display field has a list of matching options
	
					foreach ($dis_field["field_options"] as $field_key => $field_option) { // for all the matching options match the set the details array the template uses
						if (is_array($details[$dis_field["xsdmf_id"]])) { // if there are multiple selected options (it will be an array)
							foreach ($details[$dis_field["xsdmf_id"]] as $detail_key => $detail_value) {
								if ($field_option == $detail_value) {
	//								echo "field key = ".$field_key."\n";
									$details[$dis_field["xsdmf_id"]][$detail_key] = $field_key;
								}
							}					
						} else {
							if ($field_option == $details[$dis_field["xsdmf_id"]]) {
	//							echo "field key = ".$field_key."\n";
								$details[$dis_field["xsdmf_id"]] = $field_key;
							}
						}
					}
				}
			}
		}
	} elseif (($dis_field["xsdmf_multiple"] == 1) && (!@is_array($details[$dis_field["xsdmf_id"]])) ){ // makes the 'is_multiple' tagged display fields into arrays if they are not already so smarty renders them correctly
		$tmp_value = @$details[$dis_field["xsdmf_id"]];
		$details[$dis_field["xsdmf_id"]] = array();
		array_push($details[$dis_field["xsdmf_id"]], $tmp_value);
	}
}

$datastreams = Fedora_API::callGetDatastreams($pid);
//print_r($datastreams);

$datastreams = Misc::cleanDatastreamList($datastreams);
//print_r($datastreams);
$parents = $record->getParents(); // RecordObject
$tpl->assign("parents", $parents);
$title = $record->getTitle(); // RecordObject
$tpl->assign("title", $title);
if ($record->isCollection()) {
    $tpl->assign('record_type', 'Collection');
    $tpl->assign('parent_type', 'Community');
    $tpl->assign('view_href', APP_RELATIVE_URL."list.php?collection_pid=$pid");
} elseif ($record->isCommunity()) {
    $tpl->assign('record_type', 'Community');
    $tpl->assign('view_href', APP_RELATIVE_URL."list.php?community_pid=$pid");
} else {
    $tpl->assign('record_type', 'Record');
    $tpl->assign('parent_type', 'Collection');
    $tpl->assign('view_href', APP_RELATIVE_URL."view.php?pid=$pid");
}


$tpl->assign("datastreams", $datastreams);
$tpl->assign("fez_root_dir", APP_PATH);
$tpl->assign("eserv_url", APP_BASE_URL."eserv.php?pid=".$pid."&dsID=");
$tpl->assign("local_eserv_url", APP_RELATIVE_URL."eserv.php?pid=".$pid."&dsID=");

$tpl->assign('triggers', count(WorkflowTrigger::getList($pid)));


$tpl->assign("ds_get_path", APP_FEDORA_GET_URL."/".$pid."/");
$tpl->assign("isEditor", 1);

$tpl->assign("details", $details);
$setup = Setup::load();

// if user is an espace user then get prefs
if (Auth::userExists($username)) {
	$prefs = Prefs::get(Auth::getUserID());
}
$tpl->assign("user_prefs", $prefs);
//$user_details = User::getDetails(Auth::getUserID());

} else {
//	Auth::redirect(APP_RELATIVE_URL . "list.php", false);
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();


?>
