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
include_once("config.inc.php");

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.news.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.validation.php");

if (count($HTTP_POST_VARS) > 0) {
	if (Validation::isWhitespace($HTTP_POST_VARS["username"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=1");
	}
	if (Validation::isWhitespace($HTTP_POST_VARS["passwd"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=2&username=" . $HTTP_POST_VARS["username"]);
	}



	// check if the password matches
	if (!Auth::isCorrectPassword($HTTP_POST_VARS["username"], $HTTP_POST_VARS["passwd"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=3&username=" . $HTTP_POST_VARS["username"]);
	}
    Auth::LoginAuthenticatedUser($HTTP_POST_VARS["username"], $HTTP_POST_VARS["passwd"]); 
	
	if (!empty($HTTP_POST_VARS["url"])) {
		Auth::redirect(urldecode($HTTP_POST_VARS["url"])); 
	} else {
		Auth::redirect(APP_RELATIVE_URL); // even though its the same page redirect so if they refresh it doesnt have the post vars
		$extra = '';
	}
}
$tpl = new Template_API();
$tpl->setTemplate("front_page.tpl.html");

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
}
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);
// get the 3 most recently added items this week
$tpl->assign("today", date("Y-m-d"));
$tpl->assign("today_day_name", date("l"));
$tpl->assign("yesterday", date("Y-m-d", time()-86400));
$tpl->assign("last", "Last ");

$list = Collection::browseListing(0, 3, "Created Date", NULL, 0);
$list = $list["list"];

$tpl->assign("list", $list);
$tpl->assign("eserv_url", APP_RELATIVE_URL."eserv.php");
$news = News::getList();
$news_count = count($news);
$tpl->assign("news", $news);
$tpl->assign("news_count", $news_count);
$tpl->displayTemplate();
?>
