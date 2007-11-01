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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.author_org.php");
include_once(APP_INC_PATH . "class.author_classification.php");
include_once(APP_INC_PATH . "class.author_function.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "orgstructure_author");

$author_id = @$HTTP_POST_VARS["auth"] ? $HTTP_POST_VARS["auth"] : @$HTTP_GET_VARS["auth"];
$aouid = @$HTTP_POST_VARS["id"] ? $HTTP_POST_VARS["id"] : @$HTTP_GET_VARS["id"];

$breadcrumb = "";

if (!empty($author_id)) {
    $breadcrumb = Author::getFullname($author_id);
}
$tpl->assign("breadcrumb_detail", $breadcrumb);
$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if ($isAdministrator) {

    if (@$HTTP_POST_VARS["cat"] == "new") {
        $tpl->assign("result", Author_Org::insert());
    } elseif (@$HTTP_POST_VARS["cat"] == "update") {
        $tpl->assign("result", Author_Org::update($HTTP_POST_VARS["id"]));
    } elseif (@$HTTP_POST_VARS["cat"] == "delete") {
        $tpl->assign("result", Author_Org::remove());
    }

    $org_list = Org_Structure::getListAll();
    $tpl->assign("org_list", $org_list);
    $classifications_list = Author_Classif::getList();
    $tpl->assign("cla_list", $classifications_list);
    $functions_list = Author_Funct::getList();
    $tpl->assign("fun_list", $functions_list);

    if (@$HTTP_GET_VARS["cat"] == "edit") {
        $org_details = Author_Org::getDetails($aouid);
        $tpl->assign("info", $org_details);
        $author_id = $org_details['auo_aut_id'];
    }

    $tpl->assign("author_id", $author_id);

    if (!empty($author_id)) {
        $tpl->assign("list", Author_Org::getList($author_id));
    }
}

$tpl->displayTemplate();
?>