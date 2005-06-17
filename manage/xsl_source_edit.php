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
// @(#) $Id: s.doc_type_xsds.php 1.2 03/07/14 04:55:26-00:00 jpm $
//
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_xsl_transform.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "xsl_source_edit");

// @@@ CK - changed to check role by primary project
$role_id = User::getRoleByUser(Auth::getUserID());
$col_id = Auth::getCurrentCollection();
//echo $_POST['xsd_source'];
//$role_id = User::getRoleByUserCollection(Auth::getUserID(), $col_id);
$xsl_id = @$HTTP_POST_VARS["xsl_id"] ? $HTTP_POST_VARS["xsl_id"] : $HTTP_GET_VARS["xsl_id"];
if ($role_id == User::getRoleID('administrator')) {
    $tpl->assign("show_setup_links", true);

    if (@$HTTP_POST_VARS["cat"] == "update") {
        $tpl->assign("result", XSD_XSL_Transform::update($xsl_id));
    }
	$list = XSD_XSL_Transform::getXSLSource($xsl_id);
    $tpl->assign("list", $list[0]);
    $tpl->assign("xsl_id", $xsl_id);
    $tpl->assign("collection_list", Collection::getAll());
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>