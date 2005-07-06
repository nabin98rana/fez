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
// @(#) $Id: s.popup.php 1.25 04/01/23 03:42:02-00:00 jpradomaia $
//
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("popup.tpl.html");

Auth::checkAuthentication(APP_SESSION, 'index.php?err=5', true);
$usr_id = Auth::getUserID();

if (@$HTTP_GET_VARS["cat"] == "purge_datastream") {
	if (!in_array($HTTP_GET_VARS["ds_id"], Misc::const_array(APP_FEDORA_PROTECTED_DATASTREAMS))) {
	    $res = Fedora_API::callPurgeDatastream($HTTP_GET_VARS["pid"], $HTTP_GET_VARS["ds_id"]);
		if (count($res) == 1) { $res = 1; } else { $res = -1; }
	} else {
		$res = -1;
	}
    $tpl->assign("purge_datastream_result", $res);
} elseif (@$HTTP_POST_VARS["cat"] == "update_form") {
    $res = Record::update($HTTP_POST_VARS["pid"]);
	$tpl->assign("update_form_result", $res);
} elseif (@$HTTP_GET_VARS["cat"] == "purge_object") {
	$res = Fedora_API::callPurgeObject($HTTP_GET_VARS["pid"]);
	$tpl->assign("purge_object_result", $res);
}



$tpl->assign("current_user_prefs", Prefs::get($usr_id));

$tpl->displayTemplate();
?>
