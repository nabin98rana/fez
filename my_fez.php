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

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");

include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.background_process_list.php");

$tpl = new Template_API();
$tpl->setTemplate("my_fez.tpl.html");

Auth::checkAuthentication(APP_SESSION);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
	$prefs = Prefs::get(Auth::getUserID());
} else {
	Auth::redirect(APP_RELATIVE_URL . "register.php?err=5&username=" . $username);	
}
$tpl->assign("isAdministrator", $isAdministrator);

$collection_list = Collection::getEditList();
//print_r($collection_list);
foreach ($collection_list as &$item) {
   $item['community'] = implode(',',Misc::keyPairs(Collection::getParents2($item['pid']),'pid','title'));
   $item['count'] = count(Collection::getEditListing($item['pid']));
}

$tpl->assign('my_collections_list', $collection_list);

$assigned_items= Record::getAssigned(Auth::getUsername());

$bgp_list = new BackgroundProcessList;
$tpl->assign('bgp_list', $bgp_list->getList(Auth::getUserID()));

$tpl->assign("eserv_url", APP_BASE_URL."eserv.php");
$tpl->assign('my_assigned_items_list', $assigned_items);
$tpl->assign("roles_list", Auth::getDefaultRoles());

$tpl->displayTemplate();
?>
