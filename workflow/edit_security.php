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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
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
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");

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

$collection_pid = @$_POST["collection_pid"] ? $_POST["collection_pid"] : @$_GET["collection_pid"];	
$community_pid = @$_POST["community_pid"] ? $_POST["community_pid"] : @$_GET["community_pid"];	

$tpl->assign("collection_pid", $collection_pid);
$tpl->assign("community_pid", $community_pid);

$quick_auth_templates = FezACML::getQuickTemplateAssocList();

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

$tpl->assign("pid", $pid);
$tpl->assign("dsID", $dsID);
$record = new RecordObject($pid);
$record->getDisplay();

$xdis_id = $record->getXmlDisplayId();

$xdis_title = XSD_Display::getTitle($xdis_id);
$tpl->assign("xdis_title", $xdis_title);
$tpl->assign("extra_title", "Edit Security for ".$pid_title." (".$xdis_title.")");
//$xdis_list = XSD_Display::getAssocListDocTypes(); // @@@ CK - 24/8/05 added for collections to be able to select their child document types/xdisplays

$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Community_Admin");
if (Auth::checkAuthorisation($pid, $dsID, $acceptable_roles, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']) == true) {

if (!is_numeric($xdis_id)) {
	$xdis_id = @$_REQUEST["xdis_id"];	
	if (is_numeric($xdis_id)) { // must have come from select xdis so save xdis in the FezMD
		Record::updateAdminDatastream($pid, $xdis_id);
	}
}

if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
	Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=update_form&pid=".$pid.$extra_redirect, false);
}


$jtaskData = "";
$maxG = 0;
if ($dsID != "") {
	$FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
	$xsd_display_fields = $record->display->getMatchFieldsList(array(), array("FezACML for Datastreams"));  // Specify FezACML as the only display needed for security
	$details = $record->getDetails($dsID, $FezACML_xdis_id);
	$record->clearDetails();
} else {
	$xsd_display_fields = $record->display->getMatchFieldsList(array(), array("FezACML"));  // Specify FezACML as the only display needed for security
	$FezACML_xdis_id = XSD_Display::getID('FezACML');
	$details = $record->getDetails("", $FezACML_xdis_id);
}

$sta_id = $record->getPublishedStatus();
if (!$sta_id) {
    $sta_id = 1;
}
$tpl->assign('sta_id', $sta_id); 


//@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups 
// - should probably move this into a function somewhere later
foreach ($xsd_display_fields  as $dis_key => $dis_field) {
	if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'dual_multiple') {
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

//@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups 
// - should probably move this into a function somewhere later
foreach ($xsd_display_fields  as $dis_field) {
	if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'dual_multiple' || $dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'contvocab' || $dis_field["xsdmf_html_input"] == 'contvocab_selector') {
		if (@$details[$dis_field["xsdmf_id"]]) { // if a record detail matches a display field xsdmf entry
			if ($dis_field["xsdmf_html_input"] == 'contvocab_selector') {			
				$tempArray = $details[$dis_field["xsdmf_id"]];
				if (is_array($tempArray)) {
					$details[$dis_field["xsdmf_id"]] = array();
					foreach ($tempArray as $cv_key => $cv_value) {
						$details[$dis_field["xsdmf_id"]][$cv_value] = Controlled_Vocab::getTitle($cv_value);
					}
				} else {
					$tempValue = $details[$dis_field["xsdmf_id"]];
					$details[$dis_field["xsdmf_id"]] = array();
					$details[$dis_field["xsdmf_id"]][$tempValue] = Controlled_Vocab::getTitle($tempValue);

				}
			} elseif ($dis_field["xsdmf_html_input"] == 'dual_multiple') {
                //todo: track back what is placing extra spaces in $cv_value/$tempValue for datastreams with inherent from parent unticked (Ie unparent security on pdf)
                // A.B. 7/12/2011 This should be fixed but will leave here since old data might still have issues. Will be fixed post Fedora anyway
                 $tempArray = $details[$dis_field["xsdmf_id"]];
                 if (is_array($tempArray)) {
                     $details[$dis_field["xsdmf_id"]] = array();
                     foreach ($tempArray as $cv_key => $cv_value) {
                         $cv_value = trim($cv_value);
                         $details[$dis_field["xsdmf_id"]][$cv_value] = $dis_field['field_options'][$cv_value];
                     }
                 } elseif  (trim($details[$dis_field["xsdmf_id"]]) != "") {
                     $tempValue = $details[$dis_field["xsdmf_id"]];
                     $tempValue = trim($tempValue);
                     $details[$dis_field["xsdmf_id"]] = array();
                     $details[$dis_field["xsdmf_id"]][$tempValue] = $dis_field['field_options'][$tempValue];
                 }
			} else {
				if (is_array($dis_field["field_options"])) { // if the display field has a list of matching options
	
					foreach ($dis_field["field_options"] as $field_key => $field_option) { // for all the matching options match the set the details array the template uses
						if (is_array($details[$dis_field["xsdmf_id"]])) { // if there are multiple selected options (it will be an array)
							foreach ($details[$dis_field["xsdmf_id"]] as $detail_key => $detail_value) {
								if ($field_option == $detail_value) {
									$details[$dis_field["xsdmf_id"]][$detail_key] = $field_key;
								}
							}					
						} else {
							if ($field_option == $details[$dis_field["xsdmf_id"]]) {
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
$FezACML_exists = 0;
$datastreams = Fedora_API::callListDatastreamsLite($pid);
if ($dsID == "") {
	foreach ($datastreams as $security_check) {
		if ($security_check['dsid'] == 'FezACML') {
			$FezACML_exists = 1;
		}
	}
} else {
	foreach ($datastreams as $security_check) {
		$FezACML_DS_name = FezACML::getFezACMLDSName($dsID);
		if (strtolower($security_check['dsid']) == strtolower($FezACML_DS_name)) {
			$FezACML_exists = 1;
		}
	}
}

$tpl->assign("FezACML_exists", $FezACML_exists);
$parents = $record->getParents(); // RecordObject
$tpl->assign("parents", $parents);
$title = $record->getTitle(); // RecordObject
$tpl->assign("title", $title);
if ($record->isCollection()) {
    $tpl->assign('record_type', 'Collection');
    $tpl->assign('parent_type', 'Community');
    $tpl->assign('view_href', APP_RELATIVE_URL."collection/$pid");
} elseif ($record->isCommunity()) {
    $tpl->assign('record_type', 'Community');
    $tpl->assign('view_href', APP_RELATIVE_URL."community/$pid");
} else {
    $tpl->assign('record_type', 'Record');
    $tpl->assign('parent_type', 'Collection');
    $tpl->assign('view_href', APP_RELATIVE_URL."view/$pid");
}
$pid_title = $record->getTitle();
$tpl->assign("pid_title", $pid_title);

$tpl->assign("datastreams", $datastreams);
$tpl->assign("fez_root_dir", APP_PATH);
$tpl->assign("eserv_url", APP_BASE_URL."eserv/".$pid."/");
$tpl->assign("local_eserv_url", APP_RELATIVE_URL."/".$pid."/");
$tpl->assign('triggers', count(WorkflowTrigger::getList($pid)));
$tpl->assign("ds_get_path", APP_FEDORA_GET_URL."/".$pid."/");
$tpl->assign("isEditor", 1);

$tpl->assign("details", $details);

} else {
    $tpl->assign("show_not_allowed_msg", true);
}
$tpl->displayTemplate();

?>
