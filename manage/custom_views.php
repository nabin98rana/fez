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
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.custom_view.php");
include_once(APP_INC_PATH . "class.search_key.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "custom_view");
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
     * Cases for Custom View
     */
    case 'new':
        $result = Custom_View::insert();
        header('Location: ' . $_SERVER['PHP_SELF'] . '?result=' . $result );
        break;
        
    case 'update':
        $result = Custom_View::update($_POST["id"]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?result=' . $result );
        break;
        
    case 'edit':
        $list = Custom_View::getList();
        
        $tpl->assign("info", Custom_View::getDetails($_GET["id"]));
        $tpl->assign("list", $list);
        $tpl->assign("list_count", count($list));
        break;
        
    case 'delete':
        
        $result = 3;
        if( !Custom_View::remove() ) {
            $result = 4;
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?result=' . $result );
        break;
        
    /*
     * Cases for Custom View Search Key
     */
    case 'new_skey':
        $result = Custom_View::insertCviewSek();
        
        header('Location: ' . $_SERVER['PHP_SELF'] . '?cat=skeys&id=' . $_POST["cview_id"] . '&result=' .$result);
        break;
    
    case 'edit_skey':
        $cview_sek_details = Custom_View::getCviewSekDetails($_GET["cvsk_id"]);
        $tpl->assign("info", $cview_sek_details);
        
        $cview_details = Custom_View::getDetails($cview_sek_details['cvsk_cview_id']);        
        $tpl->assign("cview_name", $cview_details['cview_name']);
    
        $cview_sek_list = Custom_View::getSekList($cview_sek_details['cvsk_cview_id']);
        
        $tpl->assign("sek_list", $cview_sek_list);
        $tpl->assign("sek_list_count", count($cview_sek_list));
        
        $tpl->assign("type", "custom_view_skeys");
        break;
        
    /*
     * List Search Keys for a particular Custom View
     */
    case 'skeys':
        $cview_details = Custom_View::getDetails($_GET["id"]);        
        $tpl->assign("cview_name", $cview_details['cview_name']);
        $tpl->assign("cview_id", $_GET["id"]);
    
        $cview_sek_list = Custom_View::getSekList($_GET["id"]);
        $tpl->assign("sek_list", $cview_sek_list);
        $tpl->assign("sek_list_count", count($cview_sek_list));
        
        $tpl->assign("type", "custom_view_skeys");
        
        if($_GET['result']) {
            $tpl->assign("result", $_GET['result']);
        }
        
        break;
    
    case 'update_skey':
        $tpl->assign("result", Custom_View::updateCviewSekDetails($_POST["cvsk_id"]));
        $tpl->assign("type", "custom_view_skeys");
        
        // Redirect to list custom view search keys page
        // This avoids re-posting on browser refresh
        header('Location: ' . $_SERVER['PHP_SELF'] . '?cat=skeys&id=' . $_POST["cview_id"]);
        break;
        
    case 'delete_skey':
        
        $result = 3;
        if( !Custom_View::removeCviewSekKey() ) {
            $result = 4;
        }
        
        header('Location: ' . $_SERVER['PHP_SELF'] . '?cat=skeys&id=' . $_POST["cview_id"] . '&result=' . $result);
        break;
        
    case 'list_sek_ids':
        
        $sek_ids = Search_Key::getList();
        
        $tpl->assign("sek_ids", $sek_ids);
        $tpl->setTemplate("manage/custom_view_sek_list.tpl.html");
        
        break;
        
    /*
     * List All Custom Views
     */
    default:
        $list = Custom_View::getList();

        $tpl->assign("list", $list);
        $tpl->assign("list_count", count($list));
        
        if($_GET['result']) {
            $tpl->assign("result", $_GET['result']);
        }
        break;
}

$list = Custom_View::getList();

$tpl->assign("list", $list);
$tpl->assign("list_count", count($list));

if($_GET['result']) {
    $tpl->assign("result", $_GET['result']);
}


$tpl->displayTemplate();
?>