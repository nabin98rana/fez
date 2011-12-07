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

include_once('config.inc.php');
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . 'class.background_process.php');
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.object_type.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.scopus.php");
include_once(APP_INC_PATH . "class.wok.php");

$tpl = new Template_API();
$tpl->assign("yui_autosuggest", '1');
$tpl->assign("jquery", true);
$tpl->assign("jqueryUI", true);
$tpl->setTemplate("adv_search.tpl.html");

$list = Search_Key::getAdvSearchList();
$sta_list = Status::getAssocList();
$ret_list = Object_Type::getAssocList();
$xdis_list = XSD_Display::getAssocListDocTypes();
$scopus_doctypes = Scopus::getAssocDocTypes();
$wok_doctypes = Wok::getAssocDocTypes();

    
$options = Pager::saveSearchParams(array(), 'adv_search');

foreach ($list as $list_key => $list_field) {
	if ($list_field["sek_html_input"] == 'combo' || $list_field["sek_html_input"] == 'multiple' || $list_field["sek_html_input"] == 'dual_multiple') {
		if (!empty($list_field["sek_smarty_variable"]) && $list_field["sek_smarty_variable"] != "none") {
			eval("\$list[\$list_key]['field_options'] = " . $list_field["sek_smarty_variable"] . ";");
			if ($list_field["sek_html_input"] == 'combo') {
				$list[$list_key]['field_options'] = array("" => "any") + $list[$list_key]['field_options'];
			}
			$list[$list_key]['field_options_count'] = count($list[$list_key]['field_options']);
		}
	}
	if ($list_field["sek_html_input"] == 'contvocab') {
		$temp_value = "";
		if (is_array($options["searchKey".$list_field['sek_id']])) {		
			foreach ($options["searchKey".$list_field['sek_id']] as $option) {
				eval("\$temp_value = ".$list_field["sek_lookup_function"]."(".$option.");");		
				$list[$list_key]["field_options"][$option] = $temp_value;
			}
			$list[$list_key]['field_options_count'] = count($list[$list_key]["field_options"]);
		}
		$list[$list_key]["parent_id"] = $list_field['sek_cvo_id'];
	}
	if ($list_field["sek_html_input"] == 'allcontvocab') {
		$temp_value = "";
		if (is_array($options["searchKey".$list_field['sek_id']])) {
			foreach ($options["searchKey".$list_field['sek_id']] as $option) {
				eval("\$temp_value = ".$list_field["sek_lookup_function"]."(".$option.");");		
				$list[$list_key]["field_options"][$option] = $temp_value;
			}
			$list[$list_key]['field_options_count'] = count($list[$list_key]["field_options"]);
		}
	} 
}
//    echo "<pre>". print_r($list,1) ."</pre>";
//    exit;

$tpl->assign("options", $options);
$tpl->assign("search_keys", $list);
$tpl->assign("active_nav", "search");
$tpl->displayTemplate();
