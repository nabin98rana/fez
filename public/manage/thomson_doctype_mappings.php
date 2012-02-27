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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.thomson_doctype_mappings.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "thomson_doctype_mappings");
$tpl->assign("active_nav", "admin");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

switch ($_REQUEST['cat'])
{
  case 'new':
    $result = 1;
    if ( !Thomson_Doctype_Mappings::insert(
        $_POST['tdm_xdis_id'], $_POST['tdm_doctype'], $_POST['tdm_service'], $_POST['tdm_subtype'])
    ) {
      $result = -1;
    }
    Misc::redirect($_SERVER['PHP_SELF'] . '?result=' . $result);
      break;
      
  case 'delete':
    $result = 3;
    if ( !Thomson_Doctype_Mappings::delete($_POST['items']) ) {
      $result = 4;
    }
    Misc::redirect($_SERVER['PHP_SELF'] . '?result=' . $result);
      break;
  
  case 'update':
    $result = Thomson_Doctype_Mappings::update(
        $_POST['tdm_id'], $_POST['tdm_xdis_id'], $_POST['tdm_doctype'], $_POST['tdm_service'], $_POST['tdm_subtype']
    );
    Misc::redirect($_SERVER['PHP_SELF'] . '?result=' . $result);
      break;

  case 'edit':
    $list = Thomson_Doctype_Mappings::getList();    
    $tpl->assign("info", Thomson_Doctype_Mappings::get($_GET["tdm_id"]));
    $tpl->assign("list", $list);
    $tpl->assign("list_count", count($list));
      
  default:
    $list = Thomson_Doctype_Mappings::getList();
    $tpl->assign("list", $list);
    $tpl->assign("list_count", count($list));
      
    if ($_GET['result']) {
      $tpl->assign("result", $_GET['result']);
    }      
      break;
}

$xsd_disp_list = Thomson_Doctype_Mappings::getXsdDispList(
    Doc_Type_XSD::getFoxmlXsdId()
);
$tpl->assign('xsd_disp_list', $xsd_disp_list);

$tpl->displayTemplate();

