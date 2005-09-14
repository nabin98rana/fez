<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | eSpace                                                               |
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
// @(#) $Id: s.list.php 1.16 03/10/14 15:38:03-00:00 jpradomaia $
//


include_once("config.inc.php");

include_once(APP_INC_PATH . "db_access.php");

include_once(APP_INC_PATH . "class.template.php");

include_once(APP_INC_PATH . "class.auth.php");

include_once(APP_INC_PATH . "class.misc.php");

include_once(APP_INC_PATH . "class.record.php");

include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");

include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.user.php");
//include_once(APP_INC_PATH . "class.news.php");

$tpl = new Template_API();
$tpl->setTemplate("stats.tpl.html");

// CK turned authentication off for now for eSpace, now back on for testing
// Auth::checkAuthentication(APP_SESSION);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);



$pagerRow = Record::getParam('pagerRow');
if (empty($pagerRow)) {
    $pagerRow = 0;
}
$rows = Record::getParam('rows');
if (empty($rows)) {
    $rows = APP_DEFAULT_PAGER_SIZE;
}
//$usr_id = Auth::getUserID();

$options = Record::saveSearchParams();

$tpl->assign("options", $options);

$browse = @$_REQUEST['browse'];


if ($browse == "top50authors") {
	$rows = 50;
	$list = Collection::statsByAttribute($pagerRow, $rows, "Author");
	$list_info = $list["info"];
	$list = $list["list"];
	$tpl->assign("browse_heading", "Top 50 Authors");
	$tpl->assign("browse_type", "browse_top50authors");
}  elseif ($browse == "top50papers") {
	$rows = 50;
	$list = Collection::statsByAttribute($pagerRow, $rows, "Title");
	$list_info = $list["info"];
	$list = $list["list"];
	$tpl->assign("browse_heading", "Top 50 Papers");
	$tpl->assign("browse_type", "browse_top50papers");
}

$tpl->assign("eserv_url", APP_BASE_URL."eserv.php");
$tpl->assign("list", $list);
$tpl->assign("list_info", $list_info);

if (Auth::userExists($username)) {
	$prefs = Prefs::get(Auth::getUserID());
}

$tpl->displayTemplate();
?>
