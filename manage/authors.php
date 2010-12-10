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
set_time_limit(0);
//include_once("../config.inc.php");
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.pager.php");

$tpl = new Template_API();
$tpl->assign("authors_form", 1);
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "authors");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);
$tpl->assign("active_nav", "admin");

$pagerRow = Pager::getParam('pagerRow',$params);
if (empty($pagerRow)) {
	$pagerRow = 0;
}
$rows = Pager::getParam('rows',$params);
if (empty($rows)) {
	$rows = APP_DEFAULT_PAGER_SIZE;
}
$options = Pager::saveSearchParams($params);
$tpl->assign("options", $options);


if ($isAdministrator) {
    if (@$_POST["cat"] == "new") {
        $tpl->assign("result", Author::insert());
    } elseif (@$_POST["cat"] == "update") {
        $tpl->assign("result", Author::update());
    } elseif (@$_POST["cat"] == "delete") {
        Author::remove();
    }
    if (@$_GET["cat"] == "edit") {
        $tpl->assign("info", Author::getDetails($_GET["id"]));
    }
	if (@$_GET["cat"] == "search") {
		$filter = Pager::getParam('search_filter',$params);
		$staff_id = Pager::getParam('staff_id',$params);
		
		$tpl->assign("search_filter", $filter);
		$tpl->assign("staff_id", $staff_id);
		
		$author_list = Author::getList($pagerRow, $rows, 'aut_lname', $filter,$staff_id);		
	} else {
		$author_list = Author::getList($pagerRow, $rows);    
	}

    $tpl->assign("list", $author_list['list']);
    $tpl->assign("list_info", $author_list['list_info']);
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>