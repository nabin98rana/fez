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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+
//
//
include_once("../config.inc.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.ad_hoc_sql.php");
include_once(APP_INC_PATH . "class.search_key.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "ad_hoc_sql");
$tpl->assign("active_nav", "admin");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

switch ($_REQUEST['cat'])
{
    /*
     * Cases for Ad Hoc SQL
     */
    case 'new':
        $result = Ad_Hoc_SQL::insert();
        header('Location: ' . $_SERVER['PHP_SELF'] . '?result=' . $result );
        break;
        
    case 'update':
        $result = Ad_Hoc_SQL::update($_POST["id"]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?result=' . $result );
        break;
        
    case 'edit':        
        $tpl->assign("info", Ad_Hoc_SQL::getDetails($_GET["id"]));        
        break;
        
    case 'delete':
        
        $result = 3;
        if( !Ad_Hoc_SQL::remove() ) {
            $result = 4;
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?result=' . $result );
        break;
               
    /*
     * List All Ad Hoc SQL Queries
     */
    default:        
        if($_GET['result']) {
            $tpl->assign("result", $_GET['result']);
        }
        break;
}

$list = Ad_Hoc_SQL::getList();
$tpl->assign("list", $list);
$tpl->assign("list_count", count($list));

if($_GET['result']) {
    $tpl->assign("result", $_GET['result']);
}


$tpl->displayTemplate();
?>