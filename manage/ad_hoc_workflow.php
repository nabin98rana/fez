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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
//include_once("../config.inc.php");
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.reindex.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.pager.php");
//include_once(APP_INC_PATH . "class.ad_hoc_workflow.php");
include_once(APP_INC_PATH . "class.ad_hoc_sql.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.bgp_index_object.php");
include_once(APP_INC_PATH . "najax_classes.php");
include_once(APP_INC_PATH . "class.fedora_direct_access.php");

set_time_limit(1800);      // 1800 MILLION MICROSECONDS!

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");
$tpl->assign("type", "ad_hoc_workflow");

Auth::checkAuthentication(APP_SESSION);
$isUser = Auth::getUsername();
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if (!$isSuperAdministrator) {
    $tpl->assign("show_not_allowed_msg", true);
} else {

//get the bulk change workflows
$bulk_workflows = WorkflowTrigger::getAssocListByTrigger("-1", WorkflowTrigger::getTriggerId('Bulk Change'));
$ad_hoc_queries = Ad_Hoc_SQL::getAssocList();

$tpl->assign("ad_hoc_queries", $ad_hoc_queries);
$tpl->assign("bulk_workflows", $bulk_workflows);
$tpl->assign("active_nav", "admin");

$rows = Pager::getParam('rows');
if (empty($rows)) {
    $rows = APP_DEFAULT_PAGER_SIZE;
}
$pagerRow = Pager::getParam('pagerRow');
if (empty($pagerRow)) {
    $pagerRow = 0;
}
$options = Pager::saveSearchParams();
$tpl->assign("options", $options);

$ahs_id = Pager::getParam('ahs_id');
if (is_numeric($ahs_id)) {
	$ahs_temp = array_keys($ad_hoc_queries);
	if (!in_array($ahs_id, $ahs_temp)) {
		$ahs_id = $ahs_temp[0];
	}
} else {
	if (count($ad_hoc_queries) > 0) {
		$ahs_temp = array_keys($ad_hoc_queries);
		$ahs_id = $ahs_temp[0];
	}
}

$list = Ad_Hoc_SQL::getResultSet($ahs_id, $pagerRow, $rows);

$tpl->assign("ahs_id", $ahs_id);    
$tpl->assign("list", $list['list']);
$tpl->assign("list_info", $list['info']);
}


$tpl->displayTemplate();

?>
