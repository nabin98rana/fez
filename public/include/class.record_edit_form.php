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
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

 include_once(APP_INC_PATH . "class.community.php");
 include_once(APP_INC_PATH . "class.search_key.php");
 require_once(APP_INC_PATH . "najax_classes.php");
 require_once(APP_INC_PATH . "class.wok.php");
 require_once(APP_INC_PATH . "class.scopus.php");
 require_once(APP_INC_PATH . "class.publisher.php");
 include_once(APP_INC_PATH . "class.sherpa_romeo.php");

class RecordEditForm
{
    var $details;
    var $xsd_display_fields;
    var $default_depositor_org_id;

     function setTemplateVars($tpl, $record)
     {

        $pid = $record->pid;
        $sta_id = $record->getPublishedStatus();
        if (!$sta_id) {
            $sta_id = Record::status_unpublished;
        }
        $tpl->assign('sta_id', $sta_id);

        $parents = $record->getParents();

        $tpl->assign("parents", $parents);
        $tpl->assign("pid", $pid);
        $this->default_depositor_org_id = 0;

        $xsd_display_fields = $record->display->getMatchFieldsList(array("FezACML"), array());  // XSD_DisplayObject

        $xsdmf_to_use = array();
        $xsdmf_state = array();

        foreach ($xsd_display_fields as $xsdmf) {

            // Get the xsdmf fields we're going to display
            if(($xsdmf['xsdmf_html_input'] != '' && $xsdmf['xsdmf_enabled'] == 1)) {

                if($xsdmf['xsdmf_html_input'] != 'static' ) {

                    $xsdmf_to_use[] = $xsdmf;

                } elseif($xsdmf['xsdmf_html_input'] == 'static'
                           && $xsdmf['xsdmf_show_in_view'] == 1
                           && $xsdmf['xsdmf_static_text'] != '') {

                    $xsdmf_to_use[] = $xsdmf;
                }

            } elseif($xsdmf['xsdmf_title'] == 'state') {
                $xsdmf_state[] = $xsdmf;
            }

        }

        $this->fixDisplayFields($xsdmf_to_use, $record);
        $this->xsd_display_fields = $xsdmf_to_use;

        $tpl->assign("xdis_id", $record->getXmlDisplayId());

        $details = $record->getDetails();   // RecordGeneral -> getDetails();
        $this->fixDetails($details);
        $this->details = $details;

        foreach ($xsdmf_to_use as &$xsdmf) {
            if( $xsdmf['xsdmf_multiple'] == 1 ) {
                if (empty($details[$xsdmf['xsdmf_id']])) {
                    $xsdmf['fields_num_display'] = 1;
                } else {
                    $xsdmf['fields_num_display'] = count(array_filter($details[$xsdmf['xsdmf_id']])) + 1;
                }
            }
        }

        $tpl->assign("xsd_display_fields",  $xsdmf_to_use);
        $tpl->assign("xsdmf_states",        $xsdmf_state);
        $tpl->assign("parents",             $parents);
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

        $tpl->assign("eserv_url", APP_BASE_URL."eserv/".$pid."/");
        $tpl->assign("local_eserv_url", APP_RELATIVE_URL."eserv/".$pid."/");
        $tpl->assign("ds_get_path", APP_FEDORA_GET_URL."/".$pid."/");
        $tpl->assign("isEditor", 1);
        $tpl->assign("default_depositor_org_id", $this->default_depositor_org_id); // a flag to set to 1 later if the edit forms default depositor org id combo box was set due to it being empty - so need to show a "not saved message next to the control"
        $tpl->assign("details", $details);
        $tpl->registerNajax( NAJAX_Client::register('SelectOrgStructure', 'edit_metadata.php')."\n"
                        .NAJAX_Client::register('Suggestor', 'edit_metadata.php'));

        // Get the fields to be displayed on Spyglass hover. @see view_inverse_metadata.tpl.html
        // Get the spyglass values from RecordView->getDetails(), which returns already formatted values for complex fields such as combo, controllvocab, etc.
        $record_view = new RecordView($record);
        $viewDetails = $record_view->getDetails();
        $spyglassFields = RecordGeneral::getSpyglassHoverFields($xsdmf_to_use, $viewDetails);
        $tpl->assign('spyglassFields', $spyglassFields);

        $isAdministrator = User::isUserAdministrator(Auth::getUsername());

        $show_delete = false;
        $show_purge = false;
        if( $isAdministrator ) {
            $show_purge = true;
            if( APP_VERSION_UPLOADS_AND_LINKS == "ON")
                $show_delete = true;
        } else {
            if( APP_VERSION_UPLOADS_AND_LINKS != "ON")
                $show_purge = true;
            else
                $show_delete = true;
        }
        $tpl->assign("showPurge", $show_purge);
        $tpl->assign("showDelete", $show_delete);
        $tpl->assign("APP_FEDORA_BYPASS", APP_FEDORA_BYPASS);

     }

    function setDynamicVar($name)
    {
        // setup smarty variables used in dynamic XSD value lookups
        switch ($name)
        {
            case '$community_list':
                global $community_list;
                if (empty($community_list)) {
                    $community_list = Community::getAssocList();
                }
                break;
            case '$collection_list':
                global $collection_list;
                if (empty($collection_list)) {
                    $collection_list = Collection::getCreatorListAssoc();
                    //$collection_list = Collection::getEditListAssoc();
                }
                break;
            case '$xdis_collection_list':
                global $xdis_collection_list;
                if (empty($xdis_collection_list)) {
                    $xdis_collection_list = XSD_Display::getAssocListCollectionDocTypes();
                }
                break;
            case '$community_and_collection_list':
                global $community_list;
                global $community_and_collection_list;
                if (empty($community_list)) {
                    $community_list = Community::getAssocList();
                }
                global $collection_list;
                if (empty($collection_list)) {
                    $collection_list = Collection::getCreatorListAssoc();
                    //$collection_list = Collection::getEditListAssoc();
                }
                $community_and_collection_list = $community_list + $collection_list;
            break;
            case '$xdis_list':
            global $xdis_list;
            // @@@ CK - 24/8/05 added for collections to be able to select their child document types/xdisplays
            if (empty($xdis_list)) {
                $xdis_list = XSD_Display::getAssocListDocTypes();
            }
            case '$xdis_collection_and_object_list':
                global $xdis_list;
                global $xdis_collection_list;
                global $xdis_collection_and_object_list;
                if (empty($xdis_collection_and_object_list)) {
                    if (empty($xdis_collection_list)) {
                        $xdis_collection_list = XSD_Display::getAssocListCollectionDocTypes();
                    }
                    if (empty($xdis_list)) {
                        $xdis_list = XSD_Display::getAssocListDocTypes();
                    }
                    $xdis_collection_and_object_list = $xdis_list + $xdis_collection_list;
                }

            break;
        }
    }

    function fixDisplayFields(&$xsd_display_fields, $record)
    {
        $parents = $record->getParents();
        $parent_relationships = array();
        foreach ($parents as $parent) {
            $parent_record = new RecordObject($parent);
            $parent_xdis_id = $parent_record->getXmlDisplayIdUseIndex();
            $parent_relationship = XSD_Relationship::getColListByXDIS($parent_xdis_id);
            array_push($parent_relationship, $parent_xdis_id);
            $parent_relationships = Misc::array_merge_values($parent_relationships, $parent_relationship);
        }

        //@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups
        // - should probably move this into a function somewhere later
        foreach ($xsd_display_fields  as $dis_key => $dis_field) {
               if ($dis_field["xsdmf_html_input"] == 'depositor_org') {
                    $xsd_display_fields[$dis_key]['field_options'] = Org_Structure::getAssocListHR();
               }
                 if ($dis_field["xsdmf_html_input"] == 'org_selector') {
                    if ($dis_field["xsdmf_org_level"] != "") {
                        $xsd_display_fields[$dis_key]['field_options'] = Org_Structure::getAssocListByLevel($dis_field["xsdmf_org_level"]);
                    }
                }
                if ($dis_field["xsdmf_html_input"] == 'author_selector') {
                    if ($dis_field["xsdmf_use_parent_option_list"] == 1) {
                        // Loop through the parents - there is only one parent for entering metadata
                        if (in_array($dis_field["xsdmf_parent_option_xdis_id"], $parent_relationships)) {
                            foreach ($parents as $parent) {
                                $parent_record = new RecordObject($parent);
                                $parent_details = $parent_record->getDetails();
                                if (is_numeric(@$parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]])) {
                                    $authors_sub_list = Org_Structure::getAuthorsByOrgID($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]]);
                                    $xsd_display_fields[$dis_key]['field_options'] = $authors_sub_list;
                                }
                            }
                        }
                    }
                }
                                if ($dis_field["xsdmf_html_input"] == "contvocab_selector" && $dis_field["xsdmf_cvo_min_level"] == 3) {
                                    $xsd_display_fields[$dis_key]['field_options'] = Controlled_Vocab::getAssocListFullDisplay($dis_field["xsdmf_cvo_id"], '',  1,2);
                                }

                if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'dual_multiple') {
                    if (!empty($dis_field["xsdmf_smarty_variable"]) && $dis_field["xsdmf_smarty_variable"] != "none") {
                                                if (is_numeric(strpos($dis_field["xsdmf_smarty_variable"], "::"))) {
                                                    eval("\$temp = ".$dis_field['xsdmf_smarty_variable']
                                 ."; \$xsd_display_fields[\$dis_key]['field_options'] = \$temp;");
                                                } else {
                            $this->setDynamicVar($dis_field["xsdmf_smarty_variable"]);
                            eval("global ".$dis_field['xsdmf_smarty_variable']
                                ."; \$xsd_display_fields[\$dis_key]['field_options'] = "
                                . $dis_field["xsdmf_smarty_variable"] . ";");
                                                }
                    }
                    if (!empty($dis_field["xsdmf_dynamic_selected_option"]) && $dis_field["xsdmf_dynamic_selected_option"] != "none") {
                                                if (is_numeric(strpos($dis_field["xsdmf_dynamic_selected_option"], "::"))) {
                                                    eval("\$temp = ".$dis_field['xsdmf_dynamic_selected_option']
                                 ."; \$xsd_display_fields[\$dis_key]['selected_option'] = \$temp;");
                                                } else {
                            $this->setDynamicVar($dis_field["xsdmf_dynamic_selected_option"]);
                            if (isset($dis_field["xsdmf_dynamic_selected_option"])) {
                                if ($dis_field['xsdmf_dynamic_selected_option'][0] == "$") {
                                    eval("global ".$dis_field['xsdmf_dynamic_selected_option']
                                        ."; \$xsd_display_fields[\$dis_key]['selected_option'] = "
                                        . $dis_field["xsdmf_dynamic_selected_option"] . ";");
                                } else {
                                    eval(" \$xsd_display_fields[\$dis_key]['selected_option'] = "
                                        . $dis_field["xsdmf_dynamic_selected_option"] . ";");
                                }

                            }
                                                }
                    }

                    // if the display field inherits this list from a
                    // parent then get those options
                    if ($dis_field["xsdmf_use_parent_option_list"] == 1) {
                        // Loop through the parents
                        if (in_array($dis_field["xsdmf_parent_option_xdis_id"], $parent_relationships)) {

                            // this only works for one parent for now..
                            // need to loop over them again
                            $parent_details = $parent_record->getDetails();
                            if (is_array($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]])) {
                                $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($dis_field["xsdmf_parent_option_child_xsdmf_id"]);
                                if ($xsdmf_details['xsdmf_smarty_variable'] != "" && ($xsdmf_details['xsdmf_html_input'] == "multiple" || $xsdmf_details['xsdmf_html_input'] == "dual_multiple")) {
                                    $temp_parent_options = array();
                                    $temp_parent_options_final = array();
                                    $this->setDynamicVar($xsdmf_details['xsdmf_smarty_variable']);
                                    eval("global ".$xsdmf_details['xsdmf_smarty_variable']
                                        . "; \$temp_parent_options = "
                                        . $xsdmf_details['xsdmf_smarty_variable'].";");
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
        }
    }

     function fixDetails(&$details)
     {
        $xsd_display_fields = $this->xsd_display_fields;
        foreach ($xsd_display_fields  as $dis_field) {
            if ($dis_field["xsdmf_enabled"] == 1) {

                if ($dis_field["xsdmf_html_input"] == 'text' || $dis_field["xsdmf_html_input"] == 'textarea' || $dis_field["xsdmf_html_input"] == 'hidden') {
                    if (array_key_exists($dis_field['xsdmf_id'], $details) && is_array($details[$dis_field['xsdmf_id']])) {
                        foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
                            $details[$dis_field['xsdmf_id']][$ckey] = preg_replace('/\s\s+/', ' ', trim($cdata));
                        }
                    } elseif (array_key_exists($dis_field['xsdmf_id'], $details)) {
                        $details[$dis_field['xsdmf_id']] = preg_replace('/\s\s+/', ' ', trim($details[$dis_field['xsdmf_id']]));
                    }
                }

                // for the depositor org affilation control, check if it is empty then suggest a default if possible
                if ($dis_field["xsdmf_html_input"] == 'depositor_org') {
                    $tempValue = "";
                    if (array_key_exists($dis_field["xsdmf_id"], $details)) {
                        $tempValue = $details[$dis_field["xsdmf_id"]];
                    }
                    if ($tempValue == "") {
                        $username = Auth::getUsername();
                        $details[$dis_field["xsdmf_id"]] = Org_Structure::getDefaultOrgIDByUsername($username);
                        $this->default_depositor_org_id = 1; // will show a message on the form warning this was set from default lookup and needs saving to take affect
                    }
                }

                if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'dual_multiple' || $dis_field["xsdmf_html_input"] == 'customvocab_suggest' || $dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'contvocab' || $dis_field["xsdmf_html_input"] == 'contvocab_selector') {
                    if (@$details[$dis_field["xsdmf_id"]]) { // if a record detail matches a display field xsdmf entry
                        if (($dis_field["xsdmf_html_input"] == 'contvocab_selector') && ($dis_field['xsdmf_cvo_save_type'] != 1) && ($dis_field['xsdmf_cvo_min_level'] != 3)) {
                            $tempArray = $details[$dis_field["xsdmf_id"]];
                            if (is_array($tempArray)) {
                                $details[$dis_field["xsdmf_id"]] = array();
                                foreach ($tempArray as $cv_key => $cv_value) {
                                    $details[$dis_field["xsdmf_id"]][$cv_value] = Controlled_Vocab::getTitle($cv_value);
                                }
                            } elseif  (trim($details[$dis_field["xsdmf_id"]]) != "") {
                                $tempValue = $details[$dis_field["xsdmf_id"]];
                                $details[$dis_field["xsdmf_id"]] = array();
                                $details[$dis_field["xsdmf_id"]][$tempValue] = Controlled_Vocab::getTitle($tempValue);
                            }

                        } elseif ($dis_field["xsdmf_html_input"] == 'dual_multiple') {
                            $tempArray = $details[$dis_field["xsdmf_id"]];
                            if (is_array($tempArray)) {
                                $details[$dis_field["xsdmf_id"]] = array();
                                foreach ($tempArray as $cv_key => $cv_value) {
                                    if (!empty($dis_field["xsdmf_smarty_variable"]) && ($dis_field["xsdmf_smarty_variable"] != '$collection_list') && (!empty($cv_value))) {
                                        $smartyFunction = $dis_field["xsdmf_smarty_variable"];
                                        $smartyFunction = str_replace("()", "('".$cv_value."')", $smartyFunction);
                                        eval("\$details[\$dis_field[\"xsdmf_id\"]] += " . $smartyFunction . ";");
                                    } else {
                                        $tempValue = Record::getTitleFromIndex($cv_value);
                                        $details[$dis_field["xsdmf_id"]][$cv_value] = $tempValue ?  $tempValue : $cv_value;
                                    }
                                }
                            }  elseif  (!empty($dis_field["xsdmf_smarty_variable"]) && ($dis_field["xsdmf_smarty_variable"] != '$collection_list')) {
                                $tempValue = $tempArray;
                                $xsd_display_fields[$dis_key]["selected_option"] = array();
                                if (!empty($dis_field["xsdmf_smarty_variable"]) && (!empty($tempValue))) {
                                    $smartyFunction = $dis_field["xsdmf_smarty_variable"];
                                    $smartyFunction = str_replace("()", "('".$tempValue."')", $smartyFunction);
                                    eval("\$result = " . $smartyFunction . ";");
                                    $details[$dis_field["xsdmf_id"]] = array();
                                    $details[$dis_field["xsdmf_id"]][$tempValue] = $result[$tempValue];
                                }
                            } elseif  (trim($details[$dis_field["xsdmf_id"]]) != "") {
                                $tempValue = $details[$dis_field["xsdmf_id"]];
                                $details[$dis_field["xsdmf_id"]] = array();
                                $details[$dis_field["xsdmf_id"]][$tempValue] = Record::getTitleFromIndex($tempValue);
                            }

                        } elseif ($dis_field["xsdmf_html_input"] == 'customvocab_suggest') {
                            $lookupFunction = $dis_field["sek_lookup_function"];
                            $tempArray = $details[$dis_field["xsdmf_id"]];
                            if (is_array($tempArray)) {
                                $details[$dis_field["xsdmf_id"]] = array();
                                foreach ($tempArray as $cv_key => $cv_value) {
                                    $temp = $cv_value;
                                    if ($lookupFunction != "") {
                                        eval("\$temp = ".$lookupFunction."(".$cv_value.");");
                                    }
                                    $tempArray['id'] = $cv_value;
                                    $tempArray['text'] = $temp;
                                    $details[$dis_field["xsdmf_id"]][] = $tempArray;
                                }
                            } elseif  (trim($details[$dis_field["xsdmf_id"]]) != "") {
                                $tempValue = $details[$dis_field["xsdmf_id"]];
                                $temp = $tempValue;
                                $details[$dis_field["xsdmf_id"]] = array();
                                if ($lookupFunction != "") {
                                    eval("\$temp = ".$lookupFunction."(".$tempValue.");");
                                }
                                $tempArray = array();
                                $tempArray['id'] = $tempValue;
                                $tempArray['text'] = $temp;
                                $details[$dis_field["xsdmf_id"]][] = $tempArray;
                            }
                        } elseif (is_array($dis_field["field_options"])) { // if the display field has a list of matching options
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

                } elseif ($dis_field["xsdmf_html_input"] == 'author_suggestor') { // fix author id drop down combo if attached
                    if (array_key_exists($dis_field['xsdmf_id'], $details) && is_array($details[$dis_field['xsdmf_id']])) {
                        $temp_author_id = $details[$dis_field['xsdmf_id']];
                        if (!array_key_exists($dis_field['xsdmf_id']."_author_details", $details)) {
                            $details[$dis_field['xsdmf_id']."_author_details"] = array();
                        }
                        foreach ($temp_author_id as $ckey => $cdata) {
                            if (!empty($cdata)) {
                                $details[$dis_field['xsdmf_id']."_author_details"][] = Author::getDisplayNameUsername($cdata);
                            } else {
                                $details[$dis_field['xsdmf_id']."_author_details"][] = array();
                            }
                        }
                    } elseif (array_key_exists($dis_field['xsdmf_id'], $details)) {
                        $temp_author_id = $details[$dis_field['xsdmf_id']];

                        if (array_key_exists($dis_field['xsdmf_id']."_author_details", $details) && !is_array($details[$dis_field['xsdmf_id']."_author_details"])) {
                            $details[$dis_field['xsdmf_id']."_author_details"] = array();
                        }
                        if (is_numeric($temp_author_id) && $temp_author_id != 0) {
                            $details[$dis_field['xsdmf_id']."_author_details"][] = Author::getDisplayNameUsername($temp_author_id);
                        } else {
                            $details[$dis_field['xsdmf_id']."_author_details"][] = array();
                        }

                    }
                } elseif ($dis_field["xsdmf_html_input"] == 'publisher_suggestor') { // fix publisher id drop down combo if attached
                                    if (is_array($details[$dis_field['xsdmf_id']])) {
                                        $temp_publisher_id = $details[$dis_field['xsdmf_id']];
                                        if (!is_array($details[$dis_field['xsdmf_id']."_publisher_details"])) {
                                            $details[$dis_field['xsdmf_id']."_publisher_details"] = array();
                                        }
                                        foreach ($temp_publisher_id as $ckey => $cdata) {
                                            if (!empty($cdata)) {
                                                $details[$dis_field['xsdmf_id']."_publisher_details"][] = Publisher::getDisplayName($cdata);
                                            } else {
                                                $details[$dis_field['xsdmf_id']."_publisher_details"][] = array();
                                            }
                                        }
                                    } else {
                                        $temp_publisher_id = $details[$dis_field['xsdmf_id']];

                                        if (!is_array($details[$dis_field['xsdmf_id']."_publisher_details"])) {
                                            $details[$dis_field['xsdmf_id']."_publisher_details"] = array();
                                        }
                                        if (is_numeric($temp_publisher_id) && $temp_publisher_id != 0) {
                                            $details[$dis_field['xsdmf_id']."_publisher_details"][] = Publisher::getDisplayName($temp_publisher_id);
                                        } else {
                                            $details[$dis_field['xsdmf_id']."_publisher_details"][] = array();
                                        }

                                    }
                } elseif ($dis_field["xsdmf_html_input"] == 'conference_suggestor') { // fix conference id drop down combo if attached
                                        if (is_array($details[$dis_field['xsdmf_id']])) {
                                            $temp_conference_id = $details[$dis_field['xsdmf_id']];
                                            if (!is_array($details[$dis_field['xsdmf_id']."_conference_details"])) {
                                                $details[$dis_field['xsdmf_id']."_conference_details"] = array();
                                            }
                                            foreach ($temp_conference_id as $ckey => $cdata) {
                                                if (!empty($cdata)) {
                                                    $details[$dis_field['xsdmf_id']."_conference_details"][] = ConferenceId::getDisplayName($cdata);
                                                } else {
                                                    $details[$dis_field['xsdmf_id']."_conference_details"][] = array();
                                                }
                                            }
                                        } else {
                                            $temp_conference_id = $details[$dis_field['xsdmf_id']];

                                            if (!is_array($details[$dis_field['xsdmf_id']."_conference_details"])) {
                                                $details[$dis_field['xsdmf_id']."_conference_details"] = array();
                                            }
                                            if (is_numeric($temp_conference_id) && $temp_conference_id != 0) {
                                                $details[$dis_field['xsdmf_id']."_conference_details"][] = ConferenceId::getDisplayName($temp_conference_id);
                                            } else {
                                                $details[$dis_field['xsdmf_id']."_conference_details"][] = array();
                                            }

                                        }
                                    }
                elseif ($dis_field['xsdmf_html_input'] == "xsdmf_id_ref") {
                    $xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($dis_field['xsdmf_id_ref']);
                    $xsdmf_id_ref = $xsdmf_details_ref['xsdmf_id'];
                    if (($xsdmf_details_ref['xsdmf_html_input'] == 'contvocab') || ($xsdmf_details_ref['xsdmf_html_input'] == 'contvocab_selector')) {
                        if (!empty($details[$dis_field['xsdmf_id_ref']])) {
                            $details[$xsdmf_id_ref] = array(); //clear the existing data
                            if (is_array($details[$dis_field['xsdmf_id']])) {
                                foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
                                    if (!empty($cdata)) {
                                                                                if ($xsdmf_details_ref['xsdmf_cvo_min_level'] == 3) {
                                            $details[$xsdmf_id_ref][$cdata] = $cdata;
                                                                                } else {
                                            $details[$xsdmf_id_ref][$cdata] = Controlled_Vocab::getTitle($cdata);
                                                                                }
                                    }
                                }
                            } elseif (!empty($details[$dis_field['xsdmf_id']])) {
                                                                if ($xsdmf_details_ref['xsdmf_cvo_min_level'] == 3) {
                                    $details[$xsdmf_id_ref][$details[$dis_field['xsdmf_id']]] = $details[$dis_field['xsdmf_id']];
                                                                } else {
                                                                    $details[$xsdmf_id_ref][$details[$dis_field['xsdmf_id']]] = Controlled_Vocab::getTitle($details[$dis_field['xsdmf_id']]);
                                                                }
                            }
                        }
                    }


                } elseif (($dis_field["xsdmf_multiple"] == 1) && (!array_key_exists($dis_field["xsdmf_id"], $details) || !is_array($details[$dis_field["xsdmf_id"]])) ){ // makes the 'is_multiple' tagged display fields into arrays if they are not already so smarty renders them correctly
                    if (!array_key_exists($dis_field["xsdmf_id"], $details)) {
                        $details[$dis_field["xsdmf_id"]] = array();
                    }   else {
                        $details[$dis_field["xsdmf_id"]] = array($details[$dis_field["xsdmf_id"]]);
                    }

                } elseif ( ($dis_field["xsdmf_multiple"] != 1) && is_array($details[$dis_field["xsdmf_id"]]) && count($details[$dis_field["xsdmf_id"]]) == 1 ) {
                    $details[$dis_field["xsdmf_id"]] = $details[$dis_field["xsdmf_id"]][0];
                } elseif ( ($dis_field["xsdmf_multiple"] != 1) && is_array($details[$dis_field["xsdmf_id"]]) && count($details[$dis_field["xsdmf_id"]]) > 1 ) {
                    $details[$dis_field["xsdmf_id"]] = '';
                }
            }

            // handle attached fields on multiple things
            if (is_numeric($dis_field["xsdmf_attached_xsdmf_id"]) && $dis_field["xsdmf_multiple"] == 1) {
                if (!empty($details[$dis_field["xsdmf_attached_xsdmf_id"]])
                            && !is_array($details[$dis_field["xsdmf_attached_xsdmf_id"]])) {
                    $details[$dis_field["xsdmf_attached_xsdmf_id"]]
                            = array($details[$dis_field["xsdmf_attached_xsdmf_id"]]);
                }
            }
        }
     }

     function setDatastreamEditingTemplateVars($tpl, $record)
     {
        $pid = $record->pid;

        if(APP_FEDORA_BYPASS == 'ON')
        {
            $dob = new DigitalObject();
            $datastreams = $dob->getDatastreams(array('pid' => $pid));

        }
        else
        {
            $datastreams = Fedora_API::callGetDatastreams($pid);
        }

        $datastreams = Misc::cleanDatastreamListLite($datastreams, $pid);

        $datastream_workflows = WorkflowTrigger::getListByTrigger('-1', 5); //5 is for datastreams
        $linkCount = 0;
        $fileCount = 0;

        $datastream_isMemberOf = array(0 => $pid);
        $parents = $record->getParents();
        foreach ($parents as $parent) {
            array_push($datastream_isMemberOf, $parent);
        }

        if($datastreams)
        {
            foreach ($datastreams as $ds_key => $ds) {
                if ($datastreams[$ds_key]['controlGroup'] == 'R') {
                    $linkCount++;
                }
                if ($datastreams[$ds_key]['controlGroup'] == 'R' && $datastreams[$ds_key]['ID'] != 'DOI') {
                    $datastreams[$ds_key]['location'] = trim($datastreams[$ds_key]['location']);
                    // Check for APP_LINK_PREFIX and add if not already there
                    if (APP_LINK_PREFIX != "") {
                        if (!is_numeric(strpos($datastreams[$ds_key]['location'], APP_LINK_PREFIX))) {
                            $datastreams[$ds_key]['location'] = APP_LINK_PREFIX.$datastreams[$ds_key]['location'];
                        }
                    }
                } elseif ($datastreams[$ds_key]['controlGroup'] == 'M') {
                    $datastreams[$ds_key]['workflows'] = $datastream_workflows;
                    $datastreams[$ds_key]['FezACML'] = Auth::getAuthorisationGroups($pid, $ds['ID']);
                    $datastreams[$ds_key] = Auth::getAuthorisation($datastreams[$ds_key]);
                    $fileCount++;
                }

            }
        }
        $tpl->assign("linkCount", $linkCount);
        $tpl->assign("datastreams", $datastreams);
        $tpl->assign("fileCount", $fileCount);

     }

     function getRecordDetails()
     {
         return $this->details;
     }


    /**
     * makes sure that an array of params conforms to the expected
     * formats as if it was submitted by a form.
     */
    function fixParams(&$params, $record)
    {
        $record->getDisplay();
        $xsd_display_fields = $record->display->getMatchFieldsList(array("FezACML"), array());
        $this->fixDisplayFields($xsd_display_fields, $record);
        $this->xsd_display_fields = $xsd_display_fields;
        $this->fixDetails($params['xsd_display_fields']);

        foreach ($xsd_display_fields  as $dis_field) {
            if ($dis_field["xsdmf_enabled"] != 1) {
                continue; // skip non-enabled items
            }
            // make sure multiple items are arrays even if they only have one item
            if ( ($dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'dual_multiple' || $dis_field["xsdmf_html_input"] == 'customvocab_suggest'
                        || $dis_field["xsdmf_html_input"] == 'contvocab_selector')
                    && (!@is_array($params['xsd_display_fields'][$dis_field["xsdmf_id"]])) ){
                $params['xsd_display_fields'][$dis_field["xsdmf_id"]]
                    = array($params['xsd_display_fields'][$dis_field["xsdmf_id"]]);
            }
            // the contvocab selector uses key value pairs but we only want the keys
            if ($dis_field["xsdmf_html_input"] == 'contvocab_selector' || $dis_field["xsdmf_html_input"] == 'dual_multiple' || $dis_field["xsdmf_html_input"] == 'customvocab_suggest') {
                $params['xsd_display_fields'][$dis_field["xsdmf_id"]]
                    = array_keys($params['xsd_display_fields'][$dis_field["xsdmf_id"]]);
            }
            // handle attached fields
            if (isset($params['xsd_display_fields'][$dis_field["xsdmf_id"]])) {
                if (is_numeric($dis_field["xsdmf_attached_xsdmf_id"])) {
                    if (is_array($params['xsd_display_fields'][$dis_field["xsdmf_attached_xsdmf_id"]])) {
                        $ctr = 0;
                        foreach ($params['xsd_display_fields'][$dis_field["xsdmf_attached_xsdmf_id"]] as $item) {
                            $att_key = 'xsd_display_fields_'.$dis_field["xsdmf_attached_xsdmf_id"].'_'.$ctr;
                            $params[$att_key] = $item;
                            $ctr++;
                        }
                    } else {
                        $att_key = 'xsd_display_fields_'.$dis_field["xsdmf_attached_xsdmf_id"].'_0';
                        $params[$att_key]
                            = $params['xsd_display_fields'][$dis_field["xsdmf_attached_xsdmf_id"]];
                    }
                }
            }
            // convert dates
            if ($dis_field["xsdmf_html_input"] == 'date'
                && !empty($params['xsd_display_fields'][$dis_field["xsdmf_id"]])) {
                // need to break this into array of Year / Month / Day
                // We are expecting a YYYY-MM-DD format
                if (preg_match('/(\d{4})(-(\d{1,2})(-(\d{1,2}))?)?/',
                         trim($params['xsd_display_fields'][$dis_field["xsdmf_id"]]), $matches)) {
                    if (isset($matches[1])) {
                        $params['xsd_display_fields'][$dis_field["xsdmf_id"]] = array('Year' => $matches[1]);
                    }
                    if (isset($matches[3])) {
                        $params['xsd_display_fields'][$dis_field["xsdmf_id"]]['Month'] = $matches[3];
                    }
                    if (isset($matches[5])) {
                        $params['xsd_display_fields'][$dis_field["xsdmf_id"]]['Day'] = $matches[5];
                    }
                }
            }
            if ($dis_field["xsdmf_html_input"] == 'checkbox') {
                $value = $params['xsd_display_fields'][$dis_field["xsdmf_id"]];
                if ($value == 1 || $value == 'on' || $value == 'yes') {
                    $params['xsd_display_fields'][$dis_field["xsdmf_id"]] = 'on';
                } elseif (empty($value) || $value == 0 || $value == 'off' || $value == 'no') {
                    $params['xsd_display_fields'][$dis_field["xsdmf_id"]] = '';
                }
            }
        }
        // as a last pass, strip out any non enabled items. We do this in a separate loop because the attached fields
        // need to be read in the first loop and they are not usually enabled.
        foreach ($xsd_display_fields  as $dis_field) {
            if ($dis_field["xsdmf_enabled"] != 1 && isset($params['xsd_display_fields'][$dis_field["xsdmf_id"]])) {
                unset($params['xsd_display_fields'][$dis_field["xsdmf_id"]]);
            }
        }
        // strip out FezACML
        $fezacml_list = $record->display->getMatchFieldsList(array(), array("FezACML"));
        foreach ($fezacml_list as $item) {
            if (isset($params['xsd_display_fields'][$item['xsdmf_id']])) {
                unset($params['xsd_display_fields'][$item['xsdmf_id']]);
            }
        }
    }
}

