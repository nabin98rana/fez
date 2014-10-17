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
//include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");

if ((($_SERVER["SERVER_PORT"] != 443) && (APP_HTTPS == "ON"))) { //should be ssl when using basic auth
	header ("Location: https://".APP_HOSTNAME.APP_RELATIVE_URL."basiceserv.php"."?".$_SERVER['QUERY_STRING']);
	exit;        		
}
$pid = $_GET['pid'];
if (!Auth::isValidSession($session)) { // if user not already logged in
  $acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");
  $status = Record::getSearchKeyIndexValue($pid, "Status", false);
  if ($status != Status::getID("Published")) {
    $acceptable_roles = array("Community_Admin", "Editor", "Creator");
  }
  // Check if you even need authorisation for this - if not then just redirect to the eserv url without doing basic auth login
  if (Auth::checkAuthorisation($_GET['pid'], $_GET['dsid'], $acceptable_roles, $_SERVER['REQUEST_URI'], null, $ALLOW_SECURITY_REDIRECT) == true) {
    header ("Location: https://".APP_HOSTNAME.APP_RELATIVE_URL."view/".$_GET['pid']."/".$_GET['dsid']);
    exit;
  }
}
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="'.APP_HOSTNAME.'"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You must login to access this service';
    exit;
} else {
	// Check for basic authentication (over ssl) to bypass authorisation and login the user coming directly to eserv.php (and bypass usual login)
	if (!Auth::isValidSession($session)) { // if user not already logged in
		//print_r($_SERVER); exit;
		if (isset($_SERVER['PHP_AUTH_USER'])) { // check to see if there is some basic auth login..
			$username = $_SERVER['PHP_AUTH_USER'];
			$pw = $_SERVER['PHP_AUTH_PW'];
			if (Auth::isCorrectPassword($username, $pw)) {
				Auth::LoginAuthenticatedUser($username, $pw, false);
				header ("Location: https://".APP_HOSTNAME.APP_RELATIVE_URL."view/".$_GET['pid']."/".$_GET['dsid']);
				exit;        		        			
			} else {
				header('WWW-Authenticate: Basic realm="'.APP_HOSTNAME.'"');
				header('HTTP/1.0 401 Unauthorized');
				exit;
			}
		}
	}
}
