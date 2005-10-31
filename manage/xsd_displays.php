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
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);
$tpl->assign("type", "xsd_displays");

$xsd_id = @$HTTP_POST_VARS["xsd_id"] ? $HTTP_POST_VARS["xsd_id"] : $HTTP_GET_VARS["xsd_id"];
$xdis_id = @$HTTP_POST_VARS["id"] ? $HTTP_POST_VARS["id"] : $HTTP_GET_VARS["id"];
$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

if ($isAdministrator) {
    if (@$HTTP_POST_VARS["cat"] == "new") {
        $tpl->assign("result", XSD_Display::insert($xsd_id));
    } elseif (@$HTTP_POST_VARS["cat"] == "update") {
        $tpl->assign("result", XSD_Display::update($xdis_id)); 
    } elseif (@$HTTP_POST_VARS["cat"] == "delete") {
        XSD_Display::remove();
    }
    if (@$HTTP_GET_VARS["cat"] == "edit") {
        $tpl->assign("info", XSD_Display::getDetails($xdis_id));
    }
    if (@$HTTP_GET_VARS["cat"] == "clone") {
        $tpl->assign("info", XSD_Display::cloneDisplay($xdis_id));
    }
	$tpl->assign("xsd_id", ($xsd_id));
	$tpl->assign("xdis_id", ($xdis_id));	
	$xsd_title = Doc_Type_XSD::getTitle($xsd_id);
	$tpl->assign("xsd_title", ($xsd_title));
    $tpl->assign("list", XSD_Display::getList($xsd_id));
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>
