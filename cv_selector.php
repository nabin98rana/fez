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
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");

$tpl = new Template_API();
$tpl->setTemplate("cv_selector.tpl.html");

$cvo_id = @$_GET["cvo_id"] ? @$_GET["cvo_id"] : @$_POST["cvo_id"];
$xsdmf_cvo_min_level = @$_GET["xsdmf_cvo_min_level"] ? @$_GET["xsdmf_cvo_min_level"] : @$_POST["xsdmf_cvo_min_level"];
$element = @$_GET["element"] ? @$_GET["element"] : @$_POST["element"];
$form = @$_GET["form"] ? @$_GET["form"] : @$_POST["form"];
// get one level of the selected cvo_id
if (!is_numeric($cvo_id)) {
	$cvo_id = $_GET['cv_fields'];
}

if(!empty($cvo_id)) {
    $cvo_details = Controlled_Vocab::getDetails($cvo_id);
}

	$breadcrumb = Controlled_Vocab::getParentAssocListFullDisplay($cvo_id);
	$breadcrumb = Misc::array_merge_preserve($breadcrumb, Controlled_Vocab::getAssocListByID($cvo_id));

	$newcrumb = array();
	foreach ($breadcrumb as $key => $data) {
		array_push($newcrumb, array("cvo_id" => $key, "cvo_title" => $data));
	}
	$max_breadcrumb = (count($newcrumb) -1);

	$tpl->assign("max_subject_breadcrumb", $max_breadcrumb);
	$tpl->assign("subject_breadcrumb", $newcrumb);

if (is_numeric($cvo_id)) {
	$cvo_list = Controlled_Vocab::getAssocListFullDisplay($cvo_id, "", 0, 1);
} else {
	$cvo_list = Controlled_Vocab::getAssocList();
}
$parent_list = Controlled_Vocab::getList();

$show_add = 1;
if ($xsdmf_cvo_min_level == 1) {
	foreach ($parent_list as $pdata) {
		if ($pdata['cvo_id'] == $cvo_id) {
			$show_add = 0;
		}
	}
} 
if (!is_numeric($cvo_id)) {
	$show_add = 0;
}

$tpl->assign("cvo_details", $cvo_details);
$tpl->assign("cvo_list", $cvo_list);
$tpl->assign("cvo_id", $cvo_id);
$tpl->assign("show_add", $show_add);
$tpl->assign("xsdmf_cvo_min_level", $xsdmf_cvo_min_level);
$tpl->assign("form", $form);
$tpl->assign("element", $element);
$tpl->assign("cv_tree", Controlled_Vocab::renderCVtree(Controlled_Vocab::buildCVtree()));

$tpl->displayTemplate();
?>
