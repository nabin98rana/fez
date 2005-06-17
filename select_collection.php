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
// @(#) $Id: s.select_collection.php 1.15 04/01/19 15:22:29-00:00 jpradomaia $
//

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("select_collection.tpl.html");

// check if cookies are enabled, first of all
if (!Auth::hasCookieSupport(APP_SESSION)) {
//    echo "No cookie support in select collection";
    Auth::redirect(APP_RELATIVE_URL . "index.php?err=11");
}

if ((@$HTTP_GET_VARS["err"] == '') && (Auth::hasValidCookie(APP_SESSION))) {
    $cookie = Auth::getCookieInfo(APP_COLLECTION_COOKIE);
    if ($cookie["remember"]) {
        if (!empty($HTTP_GET_VARS["url"])) {
            Auth::redirect($HTTP_GET_VARS["url"]);
        } else {
           // Auth::redirect(APP_RELATIVE_URL . "main.php"); @@@ CK - changed default to listing
            Auth::redirect(APP_RELATIVE_URL . "list.php");
        }
    }
    // check if the list of active collections consists of just 
    // one collection, and redirect the user to the main page of the 
    // application on that case
    $assigned_collections = Collection::getAssocList(Auth::getUserID());
    if (count($assigned_collections) == 1) {
        list($col_id,) = each($assigned_collections);
        Auth::setCurrentCollection($col_id, 0);
        if (!empty($HTTP_GET_VARS["url"])) {
            Auth::redirect($HTTP_GET_VARS["url"]);
        } else {
            //Auth::redirect(APP_RELATIVE_URL . "main.php");  @@@ CK - changed default to listing
            Auth::redirect(APP_RELATIVE_URL . "list.php");
        }
    }

    // check if the user has a primary collection, 
    // and redirect the user to the main page of the 
    // application on that case
//    $assigned_collections = Collection::getAssocList(Auth::getUserID());

	$user_details = User::getDetails(Auth::getUserID());
	$primary_collection = $user_details['usr_primary_col_id'];
    if ($primary_collection > 0) {
        Auth::setCurrentCollection($primary_collection, 0);
        if (!empty($HTTP_GET_VARS["url"])) {
            Auth::redirect($HTTP_GET_VARS["url"]);
        } else {
//	            Auth::redirect(APP_RELATIVE_URL . "list.php");
/*            //Auth::redirect(APP_RELATIVE_URL . "main.php");  @@@ CK - changed default to listing
			if ($primary_collection == 2) { //askit
	            Auth::redirect(APP_RELATIVE_URL . "newquick.php");
			} elseif ($primary_collection == 4) { // server team
	            Auth::redirect(APP_RELATIVE_URL . "list.php");		
			} else { //wss and others goto normal create issue
	            //Auth::redirect(APP_RELATIVE_URL . "new.php");
	            Auth::redirect(APP_RELATIVE_URL . "list.php");
			}
*/
        }
    }

}

if (@$HTTP_GET_VARS["err"] != '') {
    Auth::removeCookie(APP_COLLECTION_COOKIE);
    $tpl->assign("err", $HTTP_GET_VARS["err"]);
}

if (@$HTTP_POST_VARS["cat"] == "select") {
    $usr_id = Auth::getUserID();
    $collections = Collection::getAssocList($usr_id);
	print_r($collections);
    if (!in_array($HTTP_POST_VARS["collection"], array_keys($collections))) {
		echo "WHAHAHAHHAHA";
        // show error message
        $tpl->assign("err", 1);
    } else {
        // create cookie and redirect
        if (empty($HTTP_POST_VARS["remember"])) {
            $HTTP_POST_VARS["remember"] = 0;
        }
        Auth::setCurrentCollection($HTTP_POST_VARS["collection"], $HTTP_POST_VARS["remember"]);
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
