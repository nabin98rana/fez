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
// @(#) $Id: s.collections.php 1.9 03/08/12 20:02:58-00:00 jpm $
//
set_time_limit(0);
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "collections");

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

//$role_id = User::getRoleByUser(Auth::getUserID());
if ($isAdministrator) {
	if (@$HTTP_POST_VARS["cat"] == "report") {
    	$res = Record::insert(); // a Record insert works, but if it needs to differ it can be changed to Collection::insert and customised or wrapped around Record::Insert
    } elseif (@$HTTP_POST_VARS["cat"] == "delete") {
        Collection::remove();
    }

/*    if (@$HTTP_GET_VARS["cat"] == "edit") {
        $tpl->assign("info", Collection::getDetails($HTTP_GET_VARS["id"]));
    }
*/

	$xdis_id = 9; // 9 is hardcoded at the moment as it is the fedora collection xdis_id but this ID will move to a conf file or db entry later
	$xsd_display_fields = (XSD_HTML_Match::getListByDisplay($xdis_id));
//	print_r($xsd_display_fields);
// preset the variable
	$community_list = Community::getAssocList();
	//@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups - should probably move this into a function somewhere later
	foreach ($xsd_display_fields  as $dis_key => $dis_field) {
		if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple') {
			if (!empty($dis_field["xsdmf_smarty_variable"]) && $dis_field["xsdmf_smarty_variable"] != "none") {
                $evalstr = "\$xsd_display_fields[\$dis_key]['field_options'] = " . $dis_field["xsdmf_smarty_variable"] . ";";
				eval($evalstr);
			}
		}
	}

	$tpl->assign("xsd_display_fields", $xsd_display_fields);
	$tpl->assign("xdis_id", $xdis_id);
	$tpl->assign("form_title", "Create New Collection");
	$tpl->assign("form_submit_button", "Create Collection");
//	print_r(Community::getAssocList());
    $tpl->assign("community_list", Community::getAssocList());
    $tpl->assign("list", Collection::getList());
    $tpl->assign("user_options", User::getActiveAssocList());
    $tpl->assign("status_options", Status::getAssocList());
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>
