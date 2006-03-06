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

include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.image_preview.php");
include_once(APP_INC_PATH . "class.author.php");

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = Auth::isAdministrator();
$tpl->assign("isAdministrator", $isAdministrator);

$tpl->assign("fez_root_dir", APP_PATH);
$tpl->assign("eserv_url", APP_BASE_URL."eserv.php?pid=".$pid."&dsID=");
$tpl->assign("local_eserv_url", APP_RELATIVE_URL."eserv.php?pid=".$pid."&dsID=");
$tpl->assign("extra_title", "Record #$pid Details");
if (!empty($pid)) {
	$tpl->assign("pid", $pid);
	$record = new RecordObject($pid);
	$xdis_id = $record->getXmlDisplayId();
	$xdis_title = XSD_Display::getTitle($xdis_id);	
    $tpl->assign("xdis_title", $xdis_title);
	if (!is_numeric($xdis_id)) {
		$xdis_id = @$HTTP_POST_VARS["xdis_id"] ? $HTTP_POST_VARS["xdis_id"] : @$HTTP_GET_VARS["xdis_id"];	
		if (is_numeric($xdis_id)) { // must have come from select xdis so save xdis in the Fez MD
			$record->updateAdminDatastream($xdis_id);
		}
	}
	if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
		Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=view_form&pid=".$pid.$extra_redirect, false);
	}
	$tpl->assign("isViewer", $record->canView(true));
	if ($record->canView()) {
		$tpl->assign("isEditor", $record->canEdit(false));
		$tpl->assign("sta_id", $record->getPublishedStatus()); 
		$display = new XSD_DisplayObject($xdis_id);
		//$xsd_display_fields = $display->getMatchFieldsList();
		$xsd_display_fields = $record->display->getMatchFieldsList(array("FezACML"), array(""));  // XSD_DisplayObject
		$tpl->assign("xsd_display_fields", $xsd_display_fields);
		$details = $record->getDetails();
		$controlled_vocabs = Controlled_Vocab::getAssocListAll();
		$tpl->assign("details_array", $details);
		$parents = $record->getParents();
		$author_list = Author::getAssocListAll();
		$parent_relationships = array();
		foreach ($parents as $parent) {
			$parent_rel = XSD_Relationship::getColListByXDIS($parent['display_type'][0]);
			$parent_relationships[$parent['pid']] = array();
			foreach ($parent_rel as $prel) {
				array_push($parent_relationships[$parent['pid']], $prel);
			}
			array_push($parent_relationships[$parent['pid']], $parent['display_type'][0]);
		}
		
		foreach ($xsd_display_fields as $dis_key => $dis_field) {
			if (($dis_field['xsdmf_html_input'] == "contvocab") || ($dis_field['xsdmf_html_input'] == "contvocab_selector")) {
				if (!empty($details[$dis_field['xsdmf_id']])) {
					if (is_array($details[$dis_field['xsdmf_id']])) {
						foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
							$details[$dis_field['xsdmf_id']][$ckey] = $controlled_vocabs[$cdata];
						}
					} else {
						$details[$dis_field['xsdmf_id']] = $controlled_vocabs[$details[$dis_field['xsdmf_id']]];
					}
				}
			}
			if ($dis_field['xsdmf_html_input'] == "author_selector") {
				if (!empty($details[$dis_field['xsdmf_id']])) {
					if (is_array($details[$dis_field['xsdmf_id']])) {
						foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
							$details[$dis_field['xsdmf_id']][$ckey] = $author_list[$cdata];
						}
					} else {
						$details[$dis_field['xsdmf_id']] = $author_list[$details[$dis_field['xsdmf_id']]];
					}
				}
			} 
			if ($dis_field["xsdmf_use_parent_option_list"] == 1) { 
                // if the display field inherits this list from a parent then get those options
				// Loop through the parents
				foreach ($parent_relationships as $pkey => $prel) {
					if (in_array($dis_field["xsdmf_parent_option_xdis_id"], $prel)) {
						$parent_record = new RecordObject($pkey);
						$parent_details = $parent_record->getDetails();
						if (is_array($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]])) {
							$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($dis_field["xsdmf_parent_option_child_xsdmf_id"]);
							if ($xsdmf_details['xsdmf_smarty_variable'] != "" && $xsdmf_details['xsdmf_html_input'] == "multiple") {
								$temp_parent_options = array();
								$temp_parent_options_final = array();
								eval("\$temp_parent_options = ". $xsdmf_details['xsdmf_smarty_variable'].";");
								$xsd_display_fields[$dis_key]['field_options'] = array();
								foreach ($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]] as $parent_smarty_option) {
									if (array_key_exists($details[$dis_field['xsdmf_id']], $temp_parent_options)) {
										$details[$dis_field['xsdmf_id']] = $temp_parent_options[$details[$dis_field['xsdmf_id']]];
									}
								}
							}
						}
					}
				}
			}	
			
		}
		foreach ($details as $dkey => $dvalue) { // turn any array values into a comma seperated string value
			if (is_array($dvalue)) {
				$details[$dkey] = implode("<br /> ", $dvalue);
			}
		}
        // Setup the Najax Image Preview object.
        $tpl->assign('najax_header', NAJAX_Utilities::header(APP_RELATIVE_URL.'include/najax'));
        $tpl->assign('najax_register', NAJAX_Client::register('NajaxImagePreview', APP_RELATIVE_URL.'najax_services/image_preview.php'));
	} else {
		$tpl->assign("show_not_allowed_msg", true);
	} 
	if (empty($details)) {
		$tpl->assign('details', '');
	} else {

		$datastreams = Fedora_API::callGetDatastreams($pid);
		$datastreams = Misc::cleanDatastreamList($datastreams);
		$securityfields = Auth::getAllRoles();
		$datastream_workflows = WorkflowTrigger::getListByTrigger('-1', 5);
		
		foreach ($datastreams as $ds_key => $ds) {
			if ($datastreams[$ds_key]['controlGroup'] == 'M') {
				$FezACML_DS = array();
				$FezACML_DS = Record::getIndexDatastream($pid, $ds['ID'], 'FezACML');
			
				$return = array();
				foreach ($FezACML_DS as $result) {
					if (in_array($result['xsdsel_title'], $securityfields)  && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) )  {
						if (!is_array($return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
							$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']] = array();
						}
						if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
							array_push($return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
						}
					}
					if ($result['xsdmf_element'] == '!inherit_security') {
						if (!is_array($return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'])) {
							$return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'] = array();
						}
						if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'])) {
							array_push($return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'], $result['rmf_'.$result['xsdmf_data_type']]);
						}
					}
				}
			
				$datastreams[$ds_key]['FezACML'] = @$return[$pid]['FezACML'];
				$datastreams[$ds_key]['workflows'] = $datastream_workflows;
				$parentsACMLs = array();
				if (count($FezACML_DS) == 0 || $datastreams[$ds_key]['FezACML'][0]['!inherit_security'][0] == "on") {
					// if there is no FezACML set for this row yet, then is it will inherit from above, so show this for the form
					if ($datastreams[$ds_key]['FezACML'][0]['!inherit_security'][0] == "on") {
						$datastreams[$ds_key]['security'] = "include";
						$parentsACMLs = $datastreams[$ds_key]['FezACML'];
					} else {
						$datastreams[$ds_key]['security'] = "inherit";
						$parentsACMLs = array();
					} 
					$parents = Record::getParents($pid);
					Auth::getIndexParentACMLMemberList(&$parentsACMLs, $pid, $parents);
					$datastreams[$ds_key]['FezACML'] = $parentsACMLs;			
				} else {
					$datastreams[$ds_key]['security'] = "exclude";			
				}
			}
            if ($datastreams[$ds_key]['controlGroup'] == 'R' && $datastreams[$ds_key]['ID'] == 'DOI') {
                $datastreams[$ds_key]['location'] = trim($datastreams[$ds_key]['location']);
                $tpl->assign('doi', $datastreams[$ds_key]);
            }
		} 
		$datastreams = Auth::getIndexAuthorisationGroups($datastreams);
		$tpl->assign("datastreams", $datastreams);	
		$tpl->assign("ds_get_path", APP_FEDORA_GET_URL."/".$pid."/");		
		$parents = Record::getParents($pid);
		$tpl->assign("parents", $parents);		
		$tpl->assign("details", $details);
		$tpl->assign("controlled_vocabs", $controlled_vocabs);				

	}
} else {
	$tpl->assign("show_not_allowed_msg", true);
}
//print_r($GLOBALS['bench']->getProfiling());
?>
