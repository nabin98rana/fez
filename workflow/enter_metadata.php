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
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_relationship.php");
include_once(APP_INC_PATH . "class.workflow_status.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.uploader.php");

// Temporary solution for SWFUpload not working on HTTPS environment
if ( $_SERVER["SERVER_PORT"] == 443)  {
   header ("HTTP 302 Redirect");
   header ("Location: http://".APP_HOSTNAME.APP_RELATIVE_URL."workflow/enter_metadata.php"."?".$_SERVER['QUERY_STRING']);
}


Auth::checkAuthentication(APP_SESSION);
$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
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
$tpl->assign('type', 'enter_metadata');
$tpl->assign('enter_metadata', '1');

if (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['SERVER_PORT'] == 443 || strtolower(substr($_SERVER['SCRIPT_URI'], 0, 5)) == 'https') {
	$tpl->assign('http_protocol', 'https');
} else {
	$tpl->assign('http_protocol', 'http');
}


$isAdministrator = User::isUserAdministrator(Auth::getUsername());
//CK commented this out as it is not needed in a entermetadata form anymore
//$tpl->assign("pid", $pid);
if (empty($wfstatus->parent_pid)) {
	echo "The system is currently setup to only allow administrators to create objects without any communities/collections. Please go back and choose a community and collection.";
	exit;
}
$wfstatus->setTemplateVars($tpl);
$tpl->assign('header_include_flash_uploader_files', 1); // we want to set the header to include the files if possible

// get the xdis_id of what we're creating
$xdis_id = $wfstatus->getXDIS_ID();
$xdis_title = XSD_Display::getTitle($xdis_id);
$debug = @$_REQUEST['debug'];
if ($debug == 1) {
	$tpl->assign("debug", "1");
} else {
	$tpl->assign("debug", "0");	
}
$tpl->assign("extra_title", "Create New ".$xdis_title);
if ($wfstatus->parent_pid == -1 || $wfstatus->parent_pid == -2 || !$wfstatus->parent_pid) {
    $access_ok = $isAdministrator;
} else {
    $community_pid = $wfstatus->parent_pid;
    $collection_pid = $wfstatus->parent_pid;
    $record = new RecordObject($wfstatus->parent_pid);
    $access_ok = $record->canCreate();
	$canEdit = $record->canEdit();
}

if ($access_ok) {
	
    // check for post action
    if (@$_POST["cat"] == "report") {
        $res = Record::insert();
        $wfstatus->setCreatedPid($res);
        $wfstatus->pid = $res;
    }
    $wfstatus->checkStateChange();
	if ($canEdit === true) {
    	$tpl->assign("isEditor", 1);
	} else {
		$tpl->assign("isEditor", 0);
	}
    $tpl->assign("isCreator", 1);
    if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
        Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=insert_form".$extra_redirect, false);
    }
    $tpl->assign("xdis_id", $xdis_id);
    $tpl->assign("xdis_title", $xdis_title);
    $sta_id = Status::getID("In Creation"); // set to unpublished to start with
    $tpl->assign('sta_id', $sta_id);
	$xdis_collection_list = XSD_Display::getAssocListCollectionDocTypes(); // @@@ CK - 13/1/06 added for communities to be able to select their collection child document types/xdisplays
    $xdis_list = XSD_Display::getAssocListDocTypes();
    $xdis_collection_and_object_list = $xdis_list + $xdis_collection_list;

    // LUR: get the communities and collections where the user is allowed to create collections   
    $communities = Community::getCreatorList(0, 1000);
	$index=0;
	foreach ($communities['list'] as $item) {
		if ($item['isCreator'] != 1)
		{
			array_splice($communities['list'], $index,1);
		}
		else {
			$index++;
		}
	}
	$community_list = array();
    $community_list = Community::getCreatorListAssoc(0, 1000);
	
	$default_depositor_org_id = -1;
	$collection_list = array();
	$collection_list = Collection::getCreatorListAssoc();
	$community_and_collection_list = $community_list + $collection_list;

    $jtaskData = "";
    $maxG = 0;
	$xsd_display_fields = XSD_HTML_Match::getListByDisplay($xdis_id, array("FezACML"), array(""));  // XSD_DisplayObject

	if (!is_numeric($wfstatus->parent_pid) && $wfstatus->parent_pid != "") {
	  $parent_record = new RecordObject($wfstatus->parent_pid);	  
	  $parent_xdis_id = $parent_record->getXmlDisplayId();
	  $parent_relationships = XSD_Relationship::getColListByXDIS($parent_xdis_id);
      array_push($parent_relationships, $parent_xdis_id);
    } else {
    	$parent_relationships = array();
    }

    //@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups
    // - should probably move this into a function somewhere later
    foreach ($xsd_display_fields as $dis_key => $dis_field) {
		if ($dis_field["xsdmf_enabled"] == 1) {
			if ($dis_field["xsdmf_html_input"] == 'org_selector') {
				if ($dis_field["xsdmf_org_level"] != "") {
					$xsd_display_fields[$dis_key]['field_options'] = Org_Structure::getAssocListByLevel($dis_field["xsdmf_org_level"]);
				}
			}
			if ($dis_field["xsdmf_html_input"] == 'depositor_org') {
				$xsd_display_fields[$dis_key]['field_options'] = Org_Structure::getAssocListHR();
				$username = Auth::getUsername();
				$default_depositor_org_id = Org_Structure::getDefaultOrgIDByUsername($username);
			}
			if ($dis_field["xsdmf_html_input"] == 'author_selector') {
				if ($dis_field["xsdmf_use_parent_option_list"] == 1) {
					// Loop through the parents - there is only one parent for entering metadata
					if (in_array($dis_field["xsdmf_parent_option_xdis_id"], $parent_relationships)) {
						$parent_details = $parent_record->getDetails();
						if (is_numeric($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]])) {
							$authors_sub_list = Org_Structure::getAuthorsByOrgID($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]]);
							$xsd_display_fields[$dis_key]['field_options'] = $authors_sub_list;
						}
					}
				}
			}
			if ($dis_field["xsdmf_html_input"] == 'author_suggestor') {

				foreach ($xsd_display_fields as $dis_key2 => $dis_field2) {
					if ($dis_field2['xsdmf_id'] == $dis_field['xsdmf_asuggest_xsdmf_id']) {
						$suggestor_count = $dis_field2['xsdmf_multiple_limit'];
					}
				}

				if (!is_numeric($suggestor_count)) {
					$suggestor_count = 1;
				}
			}
			if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'dual_multiple') {
				if (!empty($dis_field["xsdmf_smarty_variable"]) && $dis_field["xsdmf_smarty_variable"] != "none") {
					eval("\$xsd_display_fields[\$dis_key]['field_options'] = " . $dis_field["xsdmf_smarty_variable"] . ";");
				}
				if (!empty($dis_field["xsdmf_dynamic_selected_option"])
						&& $dis_field["xsdmf_dynamic_selected_option"] != "none") {
					eval("\$xsd_display_fields[\$dis_key]['selected_option'] = "
							. $dis_field["xsdmf_dynamic_selected_option"] . ";");
				}
				if ($dis_field["xsdmf_use_parent_option_list"] == 1) { // if the display field inherits this list from a parent then get those options
					// Loop through the parents
					if (in_array($dis_field["xsdmf_parent_option_xdis_id"], $parent_relationships)) {
						$parent_details = $parent_record->getDetails();
						if (is_array($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]])) {
							$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($dis_field["xsdmf_parent_option_child_xsdmf_id"]);
							if ($xsdmf_details['xsdmf_smarty_variable'] != "" && ($xsdmf_details['xsdmf_html_input'] == "multiple" || $xsdmf_details['xsdmf_html_input'] == "dual_multiple")) {
								$temp_parent_options = array();
								$temp_parent_options_final = array();
								eval("\$temp_parent_options = ". $xsdmf_details['xsdmf_smarty_variable'].";");
								$xsd_display_fields[$dis_key]['field_options'] = array();
								foreach ($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]] as $parent_smarty_option) {
									if (array_key_exists($parent_smarty_option, $temp_parent_options)) {
										$xsd_display_fields[$dis_key]['field_options'][$parent_smarty_option] = $temp_parent_options[$parent_smarty_option];
									}
								}
							} else {
								$xsd_display_fields[$dis_key]['field_options'] = array();
								foreach ($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]] as $parent_detail_text) {
									$xsd_display_fields[$dis_key]['field_options'][$parent_detail_text] = $parent_detail_text;
								}
							}
						}
					}
				}
			}
			if ($dis_field["xsdmf_html_input"] == 'dual_multiple') {
                $tempArray = $xsd_display_fields[$dis_key]["selected_option"];
                if (is_array($tempArray)) {
                    $xsd_display_fields[$dis_key]["selected_option"] = array();
                    foreach ($tempArray as $cv_key => $cv_value) {
                        if (($dis_field["xsdmf_smarty_variable"] != "") && ($dis_field["xsdmf_smarty_variable"] != '$collection_list') && (!empty($cv_value))) {
                            $smartyFunction = $dis_field["xsdmf_smarty_variable"];
                            $smartyFunction = str_replace("()", "('".$cv_value."')", $smartyFunction);
                            eval("\$xsd_display_fields[\$dis_key][\"selected_option\"] += " . $smartyFunction . ";");
                        } else {
                            $xsd_display_fields[$dis_key]["selected_option"][$cv_value] = Record::getTitleFromIndex($cv_value);
                        }
                    }
                } else {
                    $tempValue = $xsd_display_fields[$dis_key]["selected_option"];
                    $xsd_display_fields[$dis_key]["selected_option"] = array();
                    if (($dis_field["xsdmf_smarty_variable"] != "") && (!empty($tempValue)) && ($dis_field["xsdmf_smarty_variable"] != '$collection_list')) {
                        $smartyFunction = $dis_field["xsdmf_smarty_variable"];
                        $smartyFunction = str_replace("()", "('".$tempValue."')", $smartyFunction);
                        eval("\$result = " . $smartyFunction . ";");
                        $xsd_display_fields[$dis_key]["selected_option"]= $result;
                    } else {
                        $xsd_display_fields[$dis_key]["selected_option"][$tempValue] = Record::getTitleFromIndex($tempValue);
                    }
            	}
			}

			if (($dis_field["xsdmf_html_input"] == 'contvocab')
					|| ($dis_field["xsdmf_html_input"] == 'contvocab_selector')) {
				$xsd_display_fields[$dis_key]['field_options'] = @$cvo_list['data'][$dis_field['xsdmf_cvo_id']];
				if ($dis_field["xsdmf_html_input"] == "contvocab_selector" && $dis_field["xsdmf_cvo_min_level"] == 3) {
					$xsd_display_fields[$dis_key]['field_options'] = Controlled_Vocab::getAssocListFullDisplay($dis_field["xsdmf_cvo_id"], '',  1,2);
				}				
			}
		}
    }
    
    $tpl->assign("xsd_display_fields", $xsd_display_fields);
    $tpl->assign("xdis_id", $xdis_id);
    $tpl->assign("default_depositor_org_id", $default_depositor_org_id);
    $tpl->assign("form_title", "Create New Record");
    $tpl->assign("form_submit_button", "Create Record");
}

$isAdmin = Auth::isAdministrator();
if(!$isAdmin) {
	$isAdmin = User::isUserSuperAdministrator(Auth::getUsername());	
}

$tpl->assign("isAdmin", $isAdmin);
$tpl->displayTemplate();
?>
