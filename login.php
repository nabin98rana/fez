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
// @(#) $Id: s.index.php 1.11 03/09/06 00:54:04-00:00 jpradomaia $
//


include_once("config.inc.php");

// @@@ Added by Christiaan 29/6/2004 - if not on secure https, redirect to it.
/*if ($_SERVER["SERVER_PORT"] != 443) {
   header ("HTTP 302 Redirect");
   header ("Location: https://".$_SERVER['HTTP_HOST'].APP_RELATIVE_URL."login.php");
}*/

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "db_access.php");


if (($_SERVER["SERVER_PORT"] != 443) && (APP_HTTPS == "ON")) {
   header ("HTTP 302 Redirect");
   header ("Location: https://".$_SERVER['HTTP_HOST'].APP_RELATIVE_URL."login.php"."?".$HTTP_SERVER_VARS['QUERY_STRING']);
}

$tpl = new Template_API();
$tpl->setTemplate("index.tpl.html");

if (Auth::hasValidSession(APP_SESSION)) {
    if ($_SESSION["autologin"]) {
        if (!empty($HTTP_GET_VARS["url"])) {
            $extra = '?url=' . $HTTP_GET_VARS["url"];
        } else {
            $extra = '';
        }
//        Auth::redirect(APP_RELATIVE_URL . "select_project.php" . $extra);
        Auth::redirect(APP_RELATIVE_URL . "list.php" . $extra);
    } else {
        $tpl->assign("username", $session["username"]);
    }
} else {
//	Auth::redirect(APP_RELATIVE_URL . "list.php" . $extra);
}
/*$projects = Project::getAnonymousList();
if (empty($projects)) {
    $tpl->assign("anonymous_post", 0);
} else {
    $tpl->assign("anonymous_post", 1);
}
*/
$tpl->displayTemplate();
?>
