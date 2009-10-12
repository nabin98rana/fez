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
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "orgstructure");
$tpl->assign("active_nav", "admin");
$parent_id = @$_POST["parent_id"] ? $_POST["parent_id"] : @$_GET["parent_id"];	
//$parents = Org_Structure::getParentAssocListFullDisplay($parent_id);
	$org_id = $parent_id;
	$max_breadcrumb = "";
	$newcrum = "";
	if (!empty($org_id)) {
		$breadcrumb = Org_Structure::getParentAssocListFullDisplay($org_id);
		$breadcrumb = Misc::array_merge_preserve($breadcrumb, Org_Structure::getAssocListByID($org_id));
	
		$newcrumb = array();
		foreach ($breadcrumb as $key => $data) {
			array_push($newcrumb, array("org_id" => $key, "org_title" => $data));
		}
		$max_breadcrumb = (count($newcrumb) -1);
	}
	$tpl->assign("max_subject_breadcrumb", $max_breadcrumb);
	$tpl->assign("subject_breadcrumb", $newcrumb);

$tpl->assign("parent_id", $parent_id);
$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if ($isAdministrator) {
  
    if (@$_POST["cat"] == "new") {
        $tpl->assign("result", Org_Structure::insert());
    } elseif (@$_POST["cat"] == "update") {
        $tpl->assign("result", Org_Structure::update($_POST["id"]));
    } elseif (@$_POST["cat"] == "delete") {
        Org_Structure::remove();
    }

    if (@$_GET["cat"] == "edit") {
        $tpl->assign("info", Org_Structure::getDetails($_GET["id"]));
    }
//    $tpl->assign("parents", $parents); // for the parents about the very first one
	if (is_numeric($parent_id)) {
	    $tpl->assign("parent_title", Org_Structure::getTitle($parent_id));
	} else {
		$tpl->assign("parent_title", "0");
	}
    $tpl->assign("list", Org_Structure::getList($parent_id));
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>