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
// @(#) $Id: s.popup.php 1.25 04/01/23 03:42:02-00:00 jpradomaia $
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

Auth::checkAuthentication(APP_SESSION, 'index.php?err=5', true);
$usr_id = Auth::getUserID();
$cvo_id = @$HTTP_GET_VARS["cvo_id"] ? @$HTTP_GET_VARS["cvo_id"] : @$HTTP_POST_VARS["cvo_id"];
$element = @$HTTP_GET_VARS["element"] ? @$HTTP_GET_VARS["element"] : @$HTTP_POST_VARS["element"];
$form = @$HTTP_GET_VARS["form"] ? @$HTTP_GET_VARS["form"] : @$HTTP_POST_VARS["form"];
// get one level of the selected cvo_id
if (!is_numeric($cvo_id)) {
	$cvo_id = $_GET['cv_fields'];
}
$cvo_details = Controlled_Vocab::getDetails($cvo_id);

	$breadcrumb = Controlled_Vocab::getParentAssocListFullDisplay($cvo_id);
	$breadcrumb = Misc::array_merge_preserve($breadcrumb, Controlled_Vocab::getAssocListByID($cvo_id));

//	print_r(array_values($breadcrumb));
	$newcrumb = array();
	foreach ($breadcrumb as $key => $data) {
		array_push($newcrumb, array("cvo_id" => $key, "cvo_title" => $data));
	}
//	print_r($newcrumb);
//	if (count($newcrumb) > 0) {
		$max_breadcrumb = (count($newcrumb) -1);
//	} else {
//		$max_breadcrumb = -1;
//	}

	$tpl->assign("max_subject_breadcrumb", $max_breadcrumb);
	$tpl->assign("subject_breadcrumb", $newcrumb);

$cvo_list = Controlled_Vocab::getAssocListFullDisplay($cvo_id, "", 0, 1);
//print_r($cvo_list);
$tpl->assign("cvo_details", $cvo_details);
$tpl->assign("cvo_list", $cvo_list);
$tpl->assign("form", $form);
$tpl->assign("element", $element);
$tpl->assign("current_user_prefs", Prefs::get($usr_id));

$tpl->displayTemplate();
?>
