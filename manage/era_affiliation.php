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
// |          Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.author_era_affiliations.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.pager.php");

$tpl = new Template_API();
$tpl->assign("era_affiliation", 1);
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "era_affiliation");
$tpl->assign("active_nav", "admin");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if ($isAdministrator) {
    $hideComplete = Pager::getParam('hideComplete',$params)=='true';
    $tpl->assign("hideComplete", $hideComplete);

    $search['on'] = Pager::getParam('search_on');
    $search['value'] = Pager::getParam('search_value');
    $tpl->assign("search_on", $search['on']);
    $tpl->assign("search_value", $search['value']);

    $pagerRow = Pager::getParam('pagerRow');
    if (empty($pagerRow)) {
        $pagerRow = 0;
    }
    $rows = Pager::getParam('rows',$params);
    if (empty($rows)) {
        $rows = APP_DEFAULT_PAGER_SIZE;
    }
    $tpl->assign("rows", $rows);
    $options = Pager::saveSearchParams($params);
 	$sort = Pager::getParam('sort_by',$params);
    $tpl->assign("sort_by", Pager::getParam('sort_by',$params));
    $sort.= Pager::getParam('sort_order',$params) ? " ".Pager::getParam('sort_order',$params) : "";
 	$tpl->assign("sort_order", Pager::getParam('sort_order',$params));
    if (!empty($sort)) {
        $affiliationsList = author_era_affiliations::getList($pagerRow, $rows, $sort, $hideComplete, $search);

 	} else {
          $affiliationsList = author_era_affiliations::getList($pagerRow, $rows);
 	}
    $tpl->assign("list",$affiliationsList['list']);
    $tpl->assign("list_info", $affiliationsList['list_info']);



    $sort_by_list = array(
        "pid" => 'PID',
        "request_priority" => 'Request Priority',
        "creator_priority" => 'Creator Priority',
        "staff_id" => 'Staff Id',
        "uq_assoc_status_name" => 'Status',
        "aut_display_name" => 'Author Name',
        "aae_is_pid_request_complete, pid" => 'Is complete'
    );
    $tpl->assign("sort_by_list", $sort_by_list);

} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();

?>
