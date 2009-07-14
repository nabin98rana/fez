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

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.auth.php");

Auth::checkAuthentication(APP_SESSION);

$tpl = new Template_API();
$tpl->setTemplate("my_fez.tpl.html");
$options = Pager::saveSearchParams();

$bulk_workflows = WorkflowTrigger::getAssocListByTrigger("-1", 7); //get the bulk change workflows
$bulk_search_workflows = WorkflowTrigger::getAssocListByTrigger("-1", WorkflowTrigger::getTriggerId('Bulk Change Search')); 

$options["searchKey".Search_Key::getID("Status")] = Status::getID("In Creation");
$options["searchKey".Search_Key::getID("Assigned User ID")] = Auth::getUserID();

$pager_row  = $_GET['pager_row'];
$rows       = $_GET['rows'];

if (empty($pager_row))  $pager_row = 0;
if (empty($rows))       $rows = APP_DEFAULT_PAGER_SIZE;

$items = Record::getListing($options, array("Editor", "Creator"), $pager_row, $rows);
Record::getParentsByPids($items['list']);

$tpl->assign('extra_title',             "My Work In Progress");

$tpl->assign("page_url_order",          $_SERVER['PHP_SELF'].'?');
$tpl->assign("page_url",                $_SERVER['PHP_SELF'].'?');

$tpl->assign("bulk_workflows",          $bulk_workflows);
$tpl->assign("bulk_search_workflows",   $bulk_search_workflows);
$tpl->assign("status_list",             Status::getAssocList());
$tpl->assign("options",                 $options);
$tpl->assign('my_assigned_items_list',  $items['list']);
$tpl->assign('items_info',              $items['info']);
$tpl->assign('myFezView',               "WIP");
$tpl->assign('isApprover',              $_SESSION['auth_is_approver']);

$tpl->displayTemplate();
