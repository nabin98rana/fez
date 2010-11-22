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
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.group.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "users");
$tpl->assign("active_nav", "admin");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);

$pagerRow = Pager::getParam('pagerRow',$params);
if (empty($pagerRow)) {
	$pagerRow = 0;
}

$rows = Pager::getParam('rows',$params);
if (empty($rows)) {
	$rows = APP_DEFAULT_PAGER_SIZE;
}
$options = Pager::saveSearchParams($params);

$tpl->assign("options", $options);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if ($isAdministrator) {

    if (@$_POST["cat"] == "new") {
        $tpl->assign("result", User::insert());
        header('Location: ' . $_SERVER['PHP_SELF']);
    } elseif (@$_POST["cat"] == "update") {
        $tpl->assign("result", User::update($isSuperAdministrator));
        header('Location: ' . $_SERVER['PHP_SELF']);
    } elseif (@$_POST["cat"] == "change_status" && empty($_POST["delete"])) {
        User::changeStatus();
        header('Location: ' . $_SERVER['PHP_SELF']);
    } elseif (!empty($_POST["delete"])) {
        User::remove();
        header('Location: ' . $_SERVER['PHP_SELF']);
    }

    if (@$_GET["cat"] == "edit") {
		$user = User::getDetailsByID($_GET["id"]);
		$groupsList = array();
		foreach ($user['usr_groups'] as $groupId)
		{
			$groupDetails = Group::getDetails($groupId);
			$groupsList[$groupId] = $groupDetails['grp_title'];
		}
		$user['usr_groups'] = $groupsList;
		
        if($user['usr_super_administrator'] == 1 && !$isSuperAdministrator) {
            // User doesn't have permission to edit this record
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
				$shibAttribs = array();

				if (SHIB_CACHE_ATTRIBS == "ON") {
					$counter = 0;
					$attribs = User::getShibAttribsAssoc($_GET["id"]);
					foreach ($attribs as $name => $value) {
						if (is_numeric(strpos($name, "Shib-EP")) || is_numeric(strpos($name, "Shib-Person"))) {
							$shibAttribs[$counter]['name'] = $name;
							$shibAttribs[$counter]['value'] = $value;
							$counter++;
						}
					}
				}        
				$tpl->assign("shibAttribs", $shibAttribs);

        $tpl->assign("info", $user);
    }

	if (@$_GET["cat"] == "search") {
		$filter = Pager::getParam('search_filter',$params);
		$tpl->assign("search_filter", $filter);
		$user_list = User::getList($pagerRow, $rows, 'usr_full_name', $filter, $isSuperAdministrator);		
	} else {
		$user_list = User::getList($pagerRow, $rows, '','',$isSuperAdministrator);    
	}
    
    $tpl->assign("list", $user_list['list']);
    $tpl->assign("list_info", $user_list['list_info']);
	
    $tpl->assign("group_options", Group::getActiveAssocList());
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>
