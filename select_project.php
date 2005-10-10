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
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.project.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("select_project.tpl.html");

// check if cookies are enabled, first of all
if (!Auth::hasCookieSupport(APP_SESSION)) {
    Auth::redirect(APP_RELATIVE_URL . "index.php?err=11");
}

if ((@$HTTP_GET_VARS["err"] == '') && (Auth::hasValidCookie(APP_SESSION))) {
    $cookie = Auth::getCookieInfo(APP_PROJECT_COOKIE);
    if ($cookie["remember"]) {
        if (!empty($HTTP_GET_VARS["url"])) {
            Auth::redirect($HTTP_GET_VARS["url"]);
        } else {
           // Auth::redirect(APP_RELATIVE_URL . "main.php"); @@@ CK - changed default to listing
            Auth::redirect(APP_RELATIVE_URL . "list.php");
        }
    }
    // check if the list of active projects consists of just 
    // one project, and redirect the user to the main page of the 
    // application on that case
    $assigned_projects = Project::getAssocList(Auth::getUserID());
    if (count($assigned_projects) == 1) {
        list($prj_id,) = each($assigned_projects);
        Auth::setCurrentProject($prj_id, 0);
        if (!empty($HTTP_GET_VARS["url"])) {
            Auth::redirect($HTTP_GET_VARS["url"]);
        } else {
            //Auth::redirect(APP_RELATIVE_URL . "main.php");  @@@ CK - changed default to listing
            Auth::redirect(APP_RELATIVE_URL . "list.php");
        }
    }

    // check if the user has a primary project, 
    // and redirect the user to the main page of the 
    // application on that case
//    $assigned_projects = Project::getAssocList(Auth::getUserID());

	$user_details = User::getDetails(Auth::getUserID());
	$primary_project = $user_details['usr_primary_prj_id'];
    if ($primary_project > 0) {
        Auth::setCurrentProject($primary_project, 0);
        if (!empty($HTTP_GET_VARS["url"])) {
            Auth::redirect($HTTP_GET_VARS["url"]);
        } else {
            //Auth::redirect(APP_RELATIVE_URL . "main.php");  @@@ CK - changed default to listing
			if ($primary_project == 2) { //askit
	            Auth::redirect(APP_RELATIVE_URL . "newquick.php");
			} elseif ($primary_project == 4) { // server team
	            Auth::redirect(APP_RELATIVE_URL . "list.php");		
			} else { //wss and others goto normal create issue
	            Auth::redirect(APP_RELATIVE_URL . "new.php");
			}
        }
    }

}

if (@$HTTP_GET_VARS["err"] != '') {
    Auth::removeCookie(APP_PROJECT_COOKIE);
    $tpl->assign("err", $HTTP_GET_VARS["err"]);
}

if (@$HTTP_POST_VARS["cat"] == "select") {
    $usr_id = Auth::getUserID();
    $projects = Project::getAssocList($usr_id);
    if (!in_array($HTTP_POST_VARS["project"], array_keys($projects))) {
        // show error message
        $tpl->assign("err", 1);
    } else {
        // create cookie and redirect
        if (empty($HTTP_POST_VARS["remember"])) {
            $HTTP_POST_VARS["remember"] = 0;
        }
        Auth::setCurrentProject($HTTP_POST_VARS["project"], $HTTP_POST_VARS["remember"]);
        if (!empty($HTTP_POST_VARS["url"])) {
            Auth::redirect($HTTP_POST_VARS["url"]);
        } else {
            //Auth::redirect(APP_RELATIVE_URL . "main.php");  @@@ CK - changed default to listing
            Auth::redirect(APP_RELATIVE_URL . "list.php");
        }
    }
}

$tpl->displayTemplate();
?>
