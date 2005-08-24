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
// @(#) $Id: s.index.php 1.21 03/10/08 17:06:06-00:00 jpradomaia $
//

include_once("config.inc.php");

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
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
	
	// check if user exists
/*	if (!Auth::userExists($HTTP_POST_VARS["username"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=3");
	}
*/
	// check if the password matches
	if (!Auth::isCorrectPassword($HTTP_POST_VARS["username"], $HTTP_POST_VARS["passwd"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=3&username=" . $HTTP_POST_VARS["username"]);
	}

	// check if this user did already confirm his account
/*	if (Auth::isPendingUser($HTTP_POST_VARS["username"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=9", $is_popup);
	}
*/	
	// check if this user is really an active one
/*	if (!Auth::isActiveUser($HTTP_POST_VARS["username"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=7", $is_popup);
	}
*/	

	// redirect to the initial page
//	Auth::createLoginSession(APP_SESSION, $HTTP_POST_VARS["username"], $HTTP_POST_VARS["remember_login"]);
    Auth::LoginAuthenticatedUser($HTTP_POST_VARS["username"], $HTTP_POST_VARS["passwd"]); 
	
	if (!empty($HTTP_POST_VARS["url"])) {
	//    $extra = '?url=' . $HTTP_POST_VARS["url"];
		Auth::redirect(urldecode($HTTP_POST_VARS["url"])); // @@@ CK - 2/6/2005 - Added this in so url redirects could work again
	} else {
		$extra = '';
	}
}
$tpl = new Template_API();
$tpl->setTemplate("front_page.tpl.html");
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);

$tpl->assign("news", News::getList());

$tpl->displayTemplate();
//Auth::redirect(APP_RELATIVE_URL . "list.php" . $extra);
//Auth::redirect(APP_RELATIVE_URL . "select_collection.php" . $extra);
?>
