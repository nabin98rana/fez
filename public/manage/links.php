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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.link_check.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.pager.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "links");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);
$tpl->assign("active_nav", "admin");

$pagerRow = Pager::getParam('pagerRow', $params);
if (empty($pagerRow)) {
	$pagerRow = 0;
}
$rows = Pager::getParam('rows',$params);
if (empty($rows)) {
	$rows = APP_DEFAULT_PAGER_SIZE;
}
$options = Pager::saveSearchParams($params);


if ($isAdministrator) {
    $filter_pid = Misc::GETorPOST('pid_filter');
    $filter_code = Misc::GETorPOST('code_filter');
	$report = LinkCheck::getLinkReport($pagerRow, $rows, $filter_pid, $filter_code);
	$codes = Misc::httpStatusCodes();
	$codeColours = array();
	foreach ($codes as $code => $title) {
		$codeColours[$code] = Misc::httpStatusCodeColourise($code);
	}
    $tpl->assign("report", $report['list']);
    $tpl->assign("list_info", $report['list_info']);
	$tpl->assign("options", $options);
	$tpl->assign("codes", $codes);
	$tpl->assign("code_colours", $codeColours);
	$tpl->assign("filter_pid", $filter_pid);
	$tpl->assign("filter_code", $filter_code);
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();

?>
