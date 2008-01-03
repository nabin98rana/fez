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

include_once("config.inc.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.group.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");

include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");

if (($_SERVER["SERVER_PORT"] != 443) && (APP_HTTPS == "ON")) {
   header ("HTTP 302 Redirect");
   header ("Location: https://".$_SERVER['HTTP_HOST'].APP_RELATIVE_URL."register.php"."?".$_SERVER['QUERY_STRING']);
}
$tpl = new Template_API();
$tpl->setTemplate("register.tpl.html");
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
}
$tpl->assign("isAdministrator", $isAdministrator);
$cat = @$_POST["cat"] ? $_POST["cat"] : $_GET["cat"];

// if user is already a registered user then redirect to their my fez page with an "error" message
if (Auth::userExists($username)) {
	Auth::redirect("my_fez.php?from=register");
} elseif ($cat == "ldap_user") {
	if (count($_POST) > 0) {
		if (Validation::isWhitespace($_POST["ldap_username"])) {
			Auth::redirect(APP_RELATIVE_URL . "register.php?err=1&ldap_username=" . $_POST["ldap_username"]);
		}
		if (Validation::isWhitespace($_POST["ldap_passwd"])) {
			Auth::redirect(APP_RELATIVE_URL . "register.php?err=2&ldap_username=" . $_POST["ldap_username"]);
		}
		
		// check if user exists - if it does then we need to tell them
		if (Auth::userExists($_POST["ldap_username"])) {
			Auth::redirect(APP_RELATIVE_URL . "register.php?err=3&ldap_username=" . $_POST["ldap_username"]);
		}		
		// check if the password matches
		if (!Auth::isCorrectPassword($_POST["ldap_username"], $_POST["ldap_passwd"])) {
			Auth::redirect(APP_RELATIVE_URL . "register.php?err=4&ldap_username=" . $_POST["ldap_username"]);
		}
		User::insertFromLDAPLogin(); // create the user account and get details from the 

        $loginres = Auth::loginAuthenticatedUser($_POST["ldap_username"], $_POST["ldap_passwd"]); 
        if ($loginres > 0) {
            Auth::redirect(APP_RELATIVE_URL . "login.php?err={$loginres}&username=" . $_POST["ldap_username"]); 
        }   
		
		Auth::redirect(APP_RELATIVE_URL."preferences.php?from=ldap_registration"); // redirect to the preferences page so the user can check the ldap details are ok
	}
} elseif ($cat == "new_user") {
	if (count($_POST) > 0) {
		if (Validation::isWhitespace($_POST["username"])) {
			Auth::redirect(APP_RELATIVE_URL . "register.php?err=1&username=" . $_POST["username"]);
		}
		if (Validation::isWhitespace($_POST["passwd"])) {
			Auth::redirect(APP_RELATIVE_URL . "register.php?err=2&username=" . $_POST["username"]);
		}
		
		// check if user exists - if it does then we need to tell them
		if (Auth::userExists($_POST["username"])) {
			Auth::redirect(APP_RELATIVE_URL . "register.php?err=3&username=" . $_POST["username"]);
		}		

		User::insertFromLogin(); // create the user account and get details from the 

        $loginres = Auth::loginAuthenticatedUser($_POST["username"], $_POST["passwd"]);  
        if ($loginres > 0) {
            Auth::redirect(APP_RELATIVE_URL . "register.php?err={$loginres}&username=" . $_POST["username"]); 
        }   
		
		Auth::redirect(APP_RELATIVE_URL."preferences.php?from=new_registration"); // redirect to the preferences page so the user can check the ldap details are ok
	}
} else {
	$get_username = $_GET['username'];
	if ($get_username != "") {
		$tpl->assign("get_username", $get_username);
	}
}

$tpl->displayTemplate();


?>
