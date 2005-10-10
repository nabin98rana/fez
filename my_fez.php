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


$collection_list = Collection::getList();
$my_collections = array();
foreach ($collection_list as $col) {
    if (@$col['isEditor']) {
        // get parent community name
        $parents = Collection::getParents($col['pid']);
        $p2 = array();
        foreach ($parents as $p) {
            $p2[$p['pid']] = $p['title'];
        }
        $parents = implode(', ',array_values($p2));
        $col['community'] = $parents;
        // get the roles
        // get the number of records
        $col['items'] = Collection::getCount($col['pid']);
        $my_collections[] = $col;
    }
}
$tpl->assign('my_collections_list', $my_collections);

$assigned_items= Record::getAssigned(Auth::getUsername());
$tpl->assign('my_assigned_items_list', $assigned_items);
$tpl->assign("roles_list", Auth::getDefaultRoles());

$tpl->displayTemplate();


?>
