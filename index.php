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

	if (!Auth::userExists($HTTP_POST_VARS["username"])) { // If the user isn't a registered eSpace user, get their details elsewhere
		$userDetails = User::GetUserLDAPDetails($HTTP_POST_VARS["username"], $HTTP_POST_VARS["passwd"]);
		$fullname = $userDetails['displayname'];
		$email = $userDetails['email'];
		$username = $HTTP_POST_VARS["username"];
		Auth::GetUsersLDAPGroups($userDetails['usr_username'], $HTTP_POST_VARS["passwd"]);
	} else { // if it is a registered eSpace user then get their details from the espace user table
		$username = $HTTP_POST_VARS["username"];
		$userDetails = User::getDetails($username);
		$fullname = $userDetails['usr_full_name'];
		$email = $userDetails['usr_email'];
		User::updateLoginDetails(User::getUserIDByUsername($HTTP_POST_VARS["username"])); //incremement login count and last login date
		if ($userDetails['usr_ldap_authentication'] == 1) {
			Auth::GetUsersLDAPGroups($userDetails['usr_username'], $HTTP_POST_VARS["passwd"]);
		} else { 
			// get internal espace groups - yet to be programmed
		}
	}

	Auth::createLoginSession($HTTP_POST_VARS["username"], $fullname, $email, $HTTP_POST_VARS["remember_login"]);
	
	if (!empty($HTTP_POST_VARS["url"])) {
	//    $extra = '?url=' . $HTTP_POST_VARS["url"];
		Auth::redirect(urldecode($HTTP_POST_VARS["url"])); // @@@ CK - 2/6/2005 - Added this in so url redirects could work again
	} else {
		$extra = '';
	}
}


Auth::redirect(APP_RELATIVE_URL . "list.php" . $extra);
//Auth::redirect(APP_RELATIVE_URL . "select_collection.php" . $extra);
?>
