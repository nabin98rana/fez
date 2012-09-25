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
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.xsd_display_attach.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_relationship.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.select_xsd_display.php");

NAJAX_Server::allowClasses('SelectXSDDisplay');
if (NAJAX_Server::runServer()) {
	exit;
}

$tpl = new Template_API();
$tpl->setTemplate("manage/xsd_tree_match_form.tpl.html");
$tpl->assign("filter_class", XSD_Display::getFilterClasses());

Auth::checkAuthentication(APP_SESSION);
$anchor = "";
$tpl->assign("type", "custom_fields");
$tpl->assign("active_nav", "admin");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if ($isSuperAdministrator) {
$xsdmf_id = @$_POST["xsdmf_id"] ? $_POST["xsdmf_id"] : @$_GET["xsdmf_id"];
$xdis_id = @$_POST["xdis_id"] ? $_POST["xdis_id"] : @$_GET["xdis_id"];
$xsdsel_id = @$_POST["xsdsel_id"] ? $_POST["xsdsel_id"] : @$_GET["xsdsel_id"];
$xsdsel_id_edit = @$_POST["xsdsel_id_edit"] ? $_POST["xsdsel_id_edit"] : @$_GET["xsdsel_id_edit"];
$att_id_edit = @$_POST["att_id_edit"] ? $_POST["att_id_edit"] : @$_GET["att_id_edit"];
$xml_element = @$_POST["xml_element"] ? $_POST["xml_element"] : @$_GET["xml_element"];
$xml_element_clean = str_replace("!", " -> ", $xml_element);
$xml_element_clean = str_replace("^", " ", $xml_element_clean);
$xml_element_clean = substr($xml_element_clean, 4);
$filterAdmin = new Fez_Filter_Admin_XSDMF();

$tpl->assign("controlled_vocab_list", Controlled_Vocab::getAssocList());

	if (is_numeric(strpos(@$_POST["form_name"], "xsdmf"))) {
		if (is_numeric(strpos(@$_POST["submit"], "Delete"))) {
			$form_cat = "delete";
			$tpl->assign("cat", $form_cat);
			$filterAdmin->delete($xsdmf_id);
		} else { 
			$form_cat = @$_POST["form_cat"];
		}
		
		if ($form_cat == "new") {
			$filterAdmin->save(array(@$_POST['filter_class']), $xsdmf_id);
			$tpl->assign("result", XSD_HTML_Match::insert($xdis_id, $xml_element));
		} elseif ($form_cat == "update") {
			$filterAdmin->save(array(@$_POST['filter_class']), $xsdmf_id);
//			$tpl->assign("result", XSD_HTML_Match::update($xdis_id, $xml_element));
            if (isset($_POST['update_children'])) {
                $tpl->assign("result", XSD_HTML_Match::update($xsdmf_id, true));
            } else {
                $tpl->assign("result", XSD_HTML_Match::update($xsdmf_id));
            }

		} elseif ($form_cat == "delete") { // is this actually used? no I don't think so - CK - yes it is 3/8/06 CK
//			$tpl->assign("result", XSD_HTML_Match::remove($xdis_id, $xml_element));
			$tpl->assign("result", XSD_HTML_Match::remove($xsdmf_id));
		}

	} elseif (is_numeric(strpos(@$_POST["form_name"], "att_main"))) {
		$form_cat = @$_POST["form_cat"];
		$anchor = "#att_main";
		if ($form_cat == "new") {
			$tpl->assign("result", XSD_Display_Attach::insert());
		}

	} elseif (is_numeric(strpos(@$_POST["form_name"], "xsdrel_main"))) {
		$form_cat = @$_POST["form_cat"];
		$anchor = "#xsdrel_main";
		if ($form_cat == "new") {
			$tpl->assign("result", XSD_Relationship::insert());
		}
	} elseif (is_numeric(strpos(@$_POST["form_name"], "xsdsel_main"))) {
		$form_cat = @$_POST["form_cat"];
		$anchor = "#xsd_loop_subelement_form";
		if ($form_cat == "new") {
			$tpl->assign("result", XSD_Loop_Subelement::insert());
		} elseif ($form_cat == "update") {
			$tpl->assign("result", XSD_Loop_Subelement::update());		
		}
	} elseif (is_numeric(strpos(@$_POST["form_name"], "att_delete"))) {
		$anchor = "#att_main";
		$form_cat = "delete";
		$tpl->assign("result", XSD_Display_Attach::remove());
	} elseif (is_numeric(strpos(@$_POST["form_name"], "xsdrel_delete"))) {
		$anchor = "#xsdrel_main";
		$form_cat = "delete";
		$tpl->assign("result", XSD_Relationship::remove());
	} elseif (is_numeric(strpos(@$_POST["form_name"], "xsdsel_delete"))) {
		$anchor = "#xsd_loop_subelement_form";
		$form_cat = "delete";
		$tpl->assign("result", XSD_Loop_Subelement::remove());
	}

    $tpl->assign("xdis_id", $xdis_id);
    $tpl->assign("xml_element", $xml_element);
    $tpl->assign("xml_element_clean", $xml_element_clean);

	if (is_numeric($xsdsel_id)) {
		$xsdsel_details = XSD_Loop_Subelement::getDetails($xsdsel_id);
		$tpl->assign("xsdsel_title", $xsdsel_details['xsdsel_title']);
	} else {
		$tpl->assign("xsdsel_title", "N/A");
	}	

	$parent_subelement_loops = XSD_Loop_Subelement::getTopParentLoopList($xml_element, $xdis_id);

	if (count($parent_subelement_loops) > 0) {
		if (empty($xsdsel_id)) { 
			$tpl->assign("xsdsel_loop_list", $parent_subelement_loops);
			$show_subelement_parents = true;
		} else {
			$show_subelement_parents = false;
		}
	} else {
		$show_subelement_parents = false;
	}
	$tpl->assign("show_subelement_parents", $show_subelement_parents);
	$tpl->assign("xsdsel_id", $xsdsel_id);
	$tpl->assign("xsdsel_id_edit", $xsdsel_id_edit);

	if ((count($parent_subelement_loops) > 0) && is_numeric($xsdsel_id)) {	
	// It does have parents so 
		$info_array = XSD_HTML_Match::getDetailsSubelement($xdis_id, $xml_element, $xsdsel_id);
	} else {
		$info_array = XSD_HTML_Match::getDetails($xdis_id, $xml_element);
	}
	// Make sure the ID Ref XSDMF ID row has a XDIS ID, if not look it up.
	if (is_numeric($info_array['xsdmf_id_ref']) && !is_numeric($info_array['xsdmf_xdis_id_ref'])) {
		$id_ref_details = XSD_HTML_Match::getDetailsByXSDMF_ID($info_array['xsdmf_id_ref']);
		$info_array['xsdmf_xdis_id_ref'] = $id_ref_details['xsdmf_xdis_id'];
	}
	$xsd_display_list = XSD_Display::getAssocList();
	$tpl->assign("xsd_displays", $xsd_display_list);

	$xsd_reference_display_list = XSD_Display::getAssocListByObjectType(4); // get all the reference type xsd displays
	$tpl->assign("xsd_reference_displays", $xsd_reference_display_list);

	$search_key_list = Search_Key::getAssocList();
	$tpl->assign("search_key_list", $search_key_list);

	$checkbox_options_list = array("" => "not checked", "checked" => "checked");
	$tpl->assign("checkbox_options_list", $checkbox_options_list);

	if (is_array($info_array)) {
		$currentFilter = $filterAdmin->inputExists($info_array['xsdmf_id']);
		$tpl->assign('current_filter', $currentFilter[0]);
		
	    $tpl->assign("form_cat", "edit");
		$tpl->assign("xsdmf_id", $info_array['xsdmf_id']);
		
		$xsd_display_ref_list = XSD_Relationship::getListByXSDMF($info_array['xsdmf_id']);
		$xsd_display_att_list = XSD_Display_Attach::getListByXSDMF($info_array['xsdmf_id']);
		$xsd_loop_subelement_list = XSD_Loop_Subelement::getListByXSDMF($info_array['xsdmf_id']);
		$org_levels = Org_Structure::getAssocListLevels(); 
		if ((is_numeric($att_id_edit)) && ($_GET['att_cat'] == "edit")) {
			$xsd_attach_details = XSD_Display_Attach::getDetails($att_id_edit); // changed to xsdsel_id_edit for loops on loops - CK
			$tpl->assign("xsd_attach_details", $xsd_attach_details);
		}
		if ((is_numeric($xsdsel_id_edit)) && ($_GET['xsdsel_cat'] == "edit")) {
			$anchor = "#xsd_loop_subelement_form";
			$xsd_loop_subelement_details = XSD_Loop_Subelement::getDetails($xsdsel_id_edit); // changed to xsdsel_id_edit for loops on loops - CK
            $other_xsdmf_details = XSD_HTML_Match::getOneDetailsBySEL_XSDMF_ID($info_array['xsdmf_id']);
            $guess_xsdsel_indicator_xsdmf_id = 0;
            $guess_xsdsel_indicator_value = '';
            if ($other_xsdmf_details !== false) {
                $guess_xsdsel_indicator_xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title($other_xsdmf_details['xsdmf_element'], $xsd_loop_subelement_details['xsdsel_title'], $xdis_id);
                $guess_details = XSD_HTML_Match::getDetailsByXSDMF_ID($guess_xsdsel_indicator_xsdmf_id);
                if (array_key_exists('xsdmf_static_text', $guess_details) && $guess_details['xsdmf_static_text'] != '') {
                    $guess_xsdsel_indicator_value = $guess_details['xsdmf_static_text'];
                } else {
                    $guess_xsdsel_indicator_value = $xsd_loop_subelement_details['xsdsel_title'];
                }
            }
            $tpl->assign("guess_xsdsel_indicator_value", $guess_xsdsel_indicator_value);
            $tpl->assign("guess_xsdsel_indicator_xsdmf_id", $guess_xsdsel_indicator_xsdmf_id);
			$tpl->assign("xsd_loop_subelement_details", $xsd_loop_subelement_details);
		}
		$xsdmf_id_ref_list = XSD_HTML_Match::getListAssoc();
		if ($info_array['xsdmf_html_input'] == 'xsd_loop_subelement') {
			$tpl->assign("is_sublooping_base_element", true);
		} else {
			$tpl->assign("is_sublooping_base_element", false);
		}
		$tpl->assign("xsdmf_id_ref_list", $xsdmf_id_ref_list);
		$tpl->assign("org_levels", $org_levels);

		$tpl->assign("xsd_display_att_list", $xsd_display_att_list);
		$tpl->assign("xsd_display_ref_list", $xsd_display_ref_list);
		$tpl->assign("xsd_loop_subelement_list", $xsd_loop_subelement_list);
		$tpl->assign("xsd_display_count", count($xsd_display_ref_list));
		$tpl->assign("xsd_subelement_count", count($xsd_loop_subelement_list));
	} else {
	    $tpl->assign("form_cat", "new");
		$tpl->assign("xsd_display_count", 0);
	}
    $tpl->assign("anchor", $anchor);
    $tpl->assign("info", $info_array);
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->assign('najax_header', NAJAX_Utilities::header(APP_RELATIVE_URL.'include/najax'));
$tpl->registerNajax(NAJAX_Client::register('SelectXSDDisplay', 'xsd_tree_match_form.php'));
$tpl->displayTemplate();
?>
