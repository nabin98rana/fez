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
include_once(APP_INC_PATH . "class.my_research.php");
include_once(APP_INC_PATH . "class.datastream.php");
include_once(APP_INC_PATH . "class.api.php");

// Temporary solution for SWFUpload not working on HTTPS environment
if ( $_SERVER["SERVER_PORT"] == 443)  {
   header ("HTTP 302 Redirect");
   header ("Location: http://".APP_HOSTNAME.APP_RELATIVE_URL."workflow/enter_metadata.php"."?".$_SERVER['QUERY_STRING']);
}

Auth::checkAuthentication(APP_SESSION, $failed_url = NULL, $is_popup = false);

$wfstatus = &WorkflowStatusStatic::getSession($wfses_id); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
    if (APP_API) {
        API::reply(500, API::makeResponse(500, "This workflow has finished and cannot be resumed"), APP_API);
        exit;
    } else {
        echo "This workflow has finished and cannot be resumed";
        FezLog::get()->close();
        exit;
    }
}

if (empty($wfstatus->parent_pid)) {
    if (APP_API) {
        API::reply(500, API::makeResponse(500, "The system is currently setup to only allow administrators to create objects without any communities/collections. Please go back and choose a community and collection."), APP_API);
        exit;
    } else {
        echo "The system is currently setup to only allow administrators to create objects without any communities/collections. Please go back and choose a community and collection.";
        FezLog::get()->close();
        exit;
    }
}

// API: Update $_POST as if using the browser.
if (APP_API && (HTTP_METHOD == 'POST')) {
    API::populateThePOST();
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
    $tpl->assign('header_include_flash_uploader_files', 1); // we want to set the header to include the files if possible
}

$tpl->assign('type', 'enter_metadata');
$tpl->assign('enter_metadata', '1');

if (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['SERVER_PORT'] == 443 || strtolower(substr($_SERVER['SCRIPT_URI'], 0, 5)) == 'https') {
	$tpl->assign('http_protocol', 'https');
} else {
	$tpl->assign('http_protocol', 'http');
}

$username = Auth::getUsername();
$isUPO = User::isUserUPO($username);
$isAdministrator = User::isUserAdministrator($username);
$actingUser = Auth::getActingUsername();
$actingUserArray = Author::getDetailsByUsername($actingUser);
$actingUserArray['org_unit_description'] = MyResearch::getHRorgUnit($actingUser);
$tpl->assign("acting_user", $actingUserArray);
$tpl->assign("actual_user", $username);
$tpl->assign("isUPO", $isUPO);

$wfstatus->setTemplateVars($tpl);

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
    $canApprove = $record->canApprove();
}

if ($access_ok) {

    // check for post action
    if (@$_POST["cat"] == "report") {

        //Set file description variables and other stuff from the swf uploader section
        //filenames are sequential but fileperms, embargo and description are ordered but possibly with gaps ie 0,1,3,4  (2 being a cancelled file).
        //Since fileperms always has a value (Default = 0) we use it to track if files have been added and use it to find the index
        if (!empty($_POST['filePermissionsNew']) ) {
            //get maximum key in all three arrays
            $xsdmf_id = XSD_HTML_Match::getXSDMFIDByTitleXDIS_ID('Description for File Upload', $xdis_id); //XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID(), $xdis_id); 6165;
            $count = 0;
            foreach($_POST['filePermissionsNew'] as $i => $value) {
                $_POST['xsd_display_fields'][$xsdmf_id][$count] = $_POST['description'][$i];
                $count++;
            }
        }

        $rec = Record::insert();

        $wfstatus->setCreatedPid($rec->pid);
        $wfstatus->pid = $rec->pid;

        // Did we really ingest?
        if (!$rec->ingested) {
            $wfstatus->vars['outcome']= 'notCreated';
            $wfstatus->vars['outcome_details']= 'Ingest into fedora (record creation) failed';
            if (APP_API) {
                $wfstatus->theend();
                exit;
            }
        }

        if (!empty($_POST['filePermissionsNew'])) {
            $count = 0;
            foreach($_POST['filePermissionsNew'] as $i => $value) {
                $fileXdis_id = $_POST['uploader_files_uploaded'];
                $filename = $_FILES['xsd_display_fields']['name'][$fileXdis_id][$count];
                Datastream::saveDatastreamSelectedPermissions($wfstatus->pid, $filename, $_POST['filePermissionsNew'][$i], $_POST['embargo_date'][$i]);
                if ($_POST['filePermissionsNew'][$i] == 5 || !empty($_POST['embargo_date'][$i])) {
                    Datastream::setfezACML($wfstatus->pid, $filename, 10);
                }
                $count++;
            }
        }
    }

    // Record the Internal Note, if we've been handed one.
    if (isset($_POST['internal_notes']) && $isAdministrator) {
        $note = trim($_POST['internal_notes']);
        InternalNotes::recordNote($rec->pid, $note);
    }

    $wfstatus->checkStateChange();
	if ($canEdit === true) {
    	$tpl->assign("isEditor", 1);
	} else {
		$tpl->assign("isEditor", 0);
	}
    if ($canApprove === true) {
        $tpl->assign("isApprover", 1);
    } else {
        $tpl->assign("isApprover", 0);
    }

    $tpl->assign("isCreator", 1);
    if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
        Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=insert_form".$extra_redirect, false);
    }
    $tpl->assign("xdis_id", $xdis_id);
    $tpl->assign("xdis_title", $xdis_title);

    // if this is a thesis, hide the embargo date and file type picker because they will confuse students
    if ($xdis_title == 'Thesis') {
      $showFileUploadExtras = 0;
    } else {
      $showFileUploadExtras = 1;
    }
    $tpl->assign("showFileUploadExtras", $showFileUploadExtras);

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
		} else {
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
			if (($dis_field["xsdmf_html_input"] == 'author_suggestor') || ($dis_field["xsdmf_html_input"] == 'publisher_suggestor') || ($dis_field["xsdmf_html_input"] == 'conference_suggestor')) {
				foreach ($xsd_display_fields as $dis_key2 => $dis_field2) {
                    //Author is multiple selectors conference and publisher is not. This is hard coded since suggestors only work on multiples so Conference and Publisher are multiples limit 1
                    $xsd_display_fields[$dis_key]["suggestor"] = ($dis_field["xsdmf_html_input"] == 'author_suggestor') ? 2 : 1;
					if ($dis_field2['xsdmf_id'] == $dis_field['xsdmf_asuggest_xsdmf_id']) {
						$suggestor_count = $dis_field2['xsdmf_multiple_limit'];
					}
				}

				if (!is_numeric($suggestor_count)) {
					$suggestor_count = 1;
				}
			}
			if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'dual_multiple' || $xsd_display_fields[i].xsdmf_html_input == 'pid_selector') {
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
