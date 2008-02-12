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
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "search_keys");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if (!$isSuperAdministrator) {
    $tpl->assign("show_not_allowed_msg", true);
}

if (@$_POST["cat"] == "new") {
    $tpl->assign("result", Search_Key::insert());
} elseif (@$_POST["cat"] == "update") {
    $tpl->assign("result", Search_Key::update($_POST["id"]));
} elseif (@$_POST["cat"] == "delete") {
    Search_Key::remove();
}

if (@$_GET["cat"] == "edit") {
    $tpl->assign("info", Search_Key::getDetails($_GET["id"]));
} elseif (@$_GET["cat"] == "view_sql") {
    if(is_numeric(($_GET["id"]))) {
        echo '<pre>'.Search_Key::createSQL($_GET["id"]) . '</pre>';
        exit();
    }
}

$sek_data_type_list = array(
    "varchar" => "Varchar(255)", 
    "text" => "Text", 
    "int" => "Integer",
    "date" => "Date"
);
$sek_relationship_list = array(
    0 => "Core 1->1", 
    1 => "Related"
);

$list = Search_Key::getList();

$tpl->assign("list", $list);
$tpl->assign("list_count", count($list));
$tpl->assign("sek_relationship_list", $sek_relationship_list);
$tpl->assign("sek_data_type_list", $sek_data_type_list);
$tpl->assign("controlled_vocab_list", Controlled_Vocab::getAssocList());
$tpl->displayTemplate();

?>
