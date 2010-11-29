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
include_once("config.inc.php");

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.background_process_list.php");
include_once(APP_INC_PATH . 'najax/najax.php');
include_once(APP_INC_PATH . 'najax_objects/class.background_process_list.php');

Auth::checkAuthentication(APP_SESSION);

$tpl = new Template_API();
$tpl->assign("yui_autosuggest", '1');

$bgp_list = new BackgroundProcessList;
$bgp_list->autoDeleteOld(Auth::getUserID());
$bgp_list_auth = $bgp_list->getList(Auth::getUserID());

$tpl->setTemplate("my_fez.tpl.html");

$tpl->assign('myFezView',   "MBP");
$tpl->assign('extra_title', "My Recent Processes");

$tpl->assign('bgp_list',    $bgp_list_auth);
$tpl->assign('bgp_states',  $bgp_list->getStates());
$tpl->assign('isApprover',  $_SESSION['auth_is_approver']);

$tpl->assign('najax_header', NAJAX_Utilities::header(APP_RELATIVE_URL.'include/najax'));
$tpl->registerNajax( NAJAX_Client::register('NajaxBackgroundProcessList', APP_RELATIVE_URL.'najax_services/generic.php'));
$tpl->assign("active_nav", 	"my_fez");

$tpl->displayTemplate();

